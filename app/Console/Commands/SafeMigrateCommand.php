<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use SplFileInfo;

class SafeMigrateCommand extends Command
{
    private const BASELINE_CUTOFF = '2026_01_05_000010_update_polymorphic_types';

    protected $signature = 'app:migrate-safe
                            {--force : Force migrations in production}
                            {--seed : Re-run database seeders after migrate}';

    protected $description = 'Run migrations with baseline sync for legacy schemas';

    public function handle(): int
    {
        try {
            if (!Schema::hasTable('migrations')) {
                $this->components->task('Creating migrations table', function (): bool {
                    return $this->call('migrate:install') === self::SUCCESS;
                });
            }

            if ($this->shouldSyncBaseline()) {
                $baselineExit = $this->call('app:baseline-migrations', [
                    '--up-to' => self::BASELINE_CUTOFF,
                ]);

                if ($baselineExit !== self::SUCCESS) {
                    return $baselineExit;
                }
            } else {
                $this->line('Baseline sync skipped: migration history already matches schema state.');
            }

            $migrateOptions = [];
            if ((bool) $this->option('force')) {
                $migrateOptions['--force'] = true;
            }
            if ((bool) $this->option('seed')) {
                $migrateOptions['--seed'] = true;
            }

            return $this->call('migrate', $migrateOptions);
        } catch (\Throwable $exception) {
            $this->error('Safe migrate failed: ' . $exception->getMessage());
            return self::FAILURE;
        }
    }

    private function shouldSyncBaseline(): bool
    {
        if (!Schema::hasTable('migrations')) {
            return false;
        }

        if (!$this->schemaLooksLegacy()) {
            return false;
        }

        $baselineMigrations = $this->baselineMigrationNames();
        if ($baselineMigrations === []) {
            return false;
        }

        $applied = DB::table('migrations')
            ->whereIn('migration', $baselineMigrations)
            ->pluck('migration')
            ->map(fn ($migration) => (string) $migration)
            ->all();

        return count(array_diff($baselineMigrations, $applied)) > 0;
    }

    private function schemaLooksLegacy(): bool
    {
        $sentinelTables = ['users', 'schools', 'my_classes', 'subjects'];

        $existingCount = collect($sentinelTables)
            ->filter(fn (string $table) => Schema::hasTable($table))
            ->count();

        return $existingCount >= 2;
    }

    private function baselineMigrationNames(): array
    {
        return collect(File::files(database_path('migrations')))
            ->map(fn (SplFileInfo $file) => pathinfo($file->getFilename(), PATHINFO_FILENAME))
            ->filter(fn (string $name) => str_starts_with($name, '2026_01_05_0000'))
            ->filter(fn (string $name) => $name <= self::BASELINE_CUTOFF)
            ->sort()
            ->values()
            ->all();
    }
}
