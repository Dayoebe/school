<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\StudentRecord;
use App\Models\Result;
use App\Models\TermReport;
use Illuminate\Support\Facades\DB;

class CleanupDeletedStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:deleted-students {--force : Force the deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently deletes soft-deleted student users and their associated records (student records, results, term reports).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting cleanup of soft-deleted student users...');

        $deletedStudentUsers = User::onlyTrashed()->role('student')->get();

        if ($deletedStudentUsers->isEmpty()) {
            $this->info('No soft-deleted student users found to clean up.');
            return Command::SUCCESS;
        }

        $this->warn("Found {$deletedStudentUsers->count()} soft-deleted student users.");

        if ($this->option('force') || $this->confirm('Do you really wish to permanently delete these student users and all their associated data? This action cannot be undone.')) {
            DB::transaction(function () use ($deletedStudentUsers) {
                foreach ($deletedStudentUsers as $user) {
                    $this->line("Processing user: {$user->name} (ID: {$user->id})");

                    // Force delete ALL associated StudentRecords for this deleted user.
                    // We use withoutTrashed() on the relationship to ensure we fetch both active
                    // and soft-deleted student records if StudentRecord also uses SoftDeletes.
                    foreach ($user->studentRecords()->withTrashed()->get() as $studentRecord) {
                         $this->line("  - Force deleting StudentRecord ID: {$studentRecord->id}");
                         // When a StudentRecord is force deleted, its related Results and TermReports
                         // should ideally be cascade deleted at the database level, or handled via
                         // model events (e.g., in StudentRecord's 'deleting' event).
                         // Explicitly detaching from pivot table before forceDelete
                         $studentRecord->studentSubjects()->detach();
                         $this->line("  - Detached student record {$studentRecord->id} from subjects.");
                         $studentRecord->forceDelete();
                    }

                    // Force delete the user
                    $user->forceDelete();
                    $this->info("  - Permanently deleted user: {$user->name} (ID: {$user->id})");
                }
            });

            $this->info('Cleanup complete.');
        } else {
            $this->info('Cleanup cancelled.');
        }

        return Command::SUCCESS;
    }
}
