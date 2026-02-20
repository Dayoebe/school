<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use SplFileInfo;

class BaselineMigrations extends Command
{
    private const DEFAULT_BASELINE_CUTOFF = '2026_01_05_000010_update_polymorphic_types';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:baseline-migrations
                            {--up-to= : Highest migration filename to baseline (inclusive)}
                            {--dry-run : Show what would be inserted without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync migration history for databases where schema already exists';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!Schema::hasTable('migrations')) {
            $this->error('The migrations table does not exist. Run `php artisan migrate:install` first.');
            return self::FAILURE;
        }

        $allMigrationFiles = collect(File::files(database_path('migrations')))
            ->map(fn (SplFileInfo $file) => pathinfo($file->getFilename(), PATHINFO_FILENAME))
            ->sort()
            ->values();

        if ($allMigrationFiles->isEmpty()) {
            $this->warn('No migration files found in database/migrations.');
            return self::SUCCESS;
        }

        $upTo = trim((string) $this->option('up-to'));
        if ($upTo === '') {
            $upTo = (string) (DB::table('migrations')->max('migration') ?? '');
        }

        if ($upTo === '') {
            $upTo = self::DEFAULT_BASELINE_CUTOFF;
            $this->warn('No existing migration rows detected. Falling back to default cutoff: ' . $upTo);
        }

        $existing = DB::table('migrations')->pluck('migration')
            ->map(fn ($name) => (string) $name)
            ->values();

        $candidateFiles = $allMigrationFiles
            ->filter(fn (string $name) => $name <= $upTo)
            ->values();

        $missing = $candidateFiles
            ->reject(fn (string $name) => $existing->contains($name))
            ->values();

        if ($missing->isEmpty()) {
            $this->info('No missing migration records found up to: ' . $upTo);
            return self::SUCCESS;
        }

        $this->line('Cutoff: ' . $upTo);
        $this->line('Missing migrations to baseline: ' . $missing->count());
        foreach ($missing as $migrationName) {
            $this->line(' - ' . $migrationName);
        }

        if ((bool) $this->option('dry-run')) {
            $this->info('Dry run complete. No records inserted.');
            return self::SUCCESS;
        }

        $nextBatch = ((int) (DB::table('migrations')->max('batch') ?? 0)) + 1;

        DB::table('migrations')->insert(
            $missing->map(fn (string $migrationName) => [
                'migration' => $migrationName,
                'batch' => $nextBatch,
            ])->all()
        );

        $this->info('Inserted ' . $missing->count() . ' migration record(s) in batch #' . $nextBatch . '.');

        return self::SUCCESS;
    }
}
