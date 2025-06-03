<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentRecord;

class AssignSubjectsToExistingStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:assign-subjects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign subjects automatically to all existing students based on their class and section';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting subject assignment for existing students...');

        $students = StudentRecord::all();

        $bar = $this->output->createProgressBar($students->count());
        $bar->start();

        foreach ($students as $studentRecord) {
            $studentRecord->assignSubjectsAutomatically();
            $bar->advance();
        }

        $bar->finish();

        $this->info("\nSubject assignment completed successfully!");

        return 0;
    }
}
