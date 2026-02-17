<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Result;
use App\Models\StudentRecord;
use App\Models\Subject;

class CleanupInvalidResults extends Command
{
    protected $signature = 'results:cleanup';
    protected $description = 'Clean up invalid results from database';

    public function handle()
    {
        $this->info('Starting results cleanup...');
        
        $totalDeleted = 0;

        // 1. Delete results where the subject doesn't exist
        $invalidSubjectResults = Result::whereDoesntHave('subject')->count();
        if ($invalidSubjectResults > 0) {
            $this->info("Deleting $invalidSubjectResults results with non-existent subjects...");
            Result::whereDoesntHave('subject')->delete();
            $totalDeleted += $invalidSubjectResults;
        }

        // 2. Delete results where the student doesn't exist
        $invalidStudentResults = Result::whereDoesntHave('student')->count();
        if ($invalidStudentResults > 0) {
            $this->info("Deleting $invalidStudentResults results with non-existent students...");
            Result::whereDoesntHave('student')->delete();
            $totalDeleted += $invalidStudentResults;
        }

        // 3. Delete results where subject isn't assigned to student's class
        $invalidClassResults = Result::whereHas('subject', function($query) {
            $query->join('student_records', 'results.student_record_id', '=', 'student_records.id')
                  ->whereColumn('student_records.my_class_id', '!=', 'subjects.my_class_id');
        })->count();
        
        if ($invalidClassResults > 0) {
            $this->info("Deleting $invalidClassResults results where subject isn't in student's class...");
            Result::whereHas('subject', function($query) {
                $query->join('student_records', 'results.student_record_id', '=', 'student_records.id')
                      ->whereColumn('student_records.my_class_id', '!=', 'subjects.my_class_id');
            })->delete();
            $totalDeleted += $invalidClassResults;
        }

        // 4. Delete results where student isn't enrolled in the subject
        $unenrolledResults = Result::whereDoesntHave('student.studentSubjects', function($query) {
            $query->whereColumn('subject_id', 'results.subject_id');
        })->count();
        
        if ($unenrolledResults > 0) {
            $this->info("Deleting $unenrolledResults results where student isn't enrolled in subject...");
            Result::whereDoesntHave('student.studentSubjects', function($query) {
                $query->whereColumn('subject_id', 'results.subject_id');
            })->delete();
            $totalDeleted += $unenrolledResults;
        }

        $this->info("Cleanup complete! Total deleted: $totalDeleted invalid results.");
        
        return 0;
    }
}