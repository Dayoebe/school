<?php 
// app/Console/Commands/AssignSubjectsCommand.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subject;

class AssignSubjectsCommand extends Command
{
    protected $signature = 'subjects:assign';
    protected $description = 'Assign subjects to students';

    public function handle()
    {
        $subjects = Subject::all();
        
        foreach ($subjects as $subject) {
            $subject->assignToClassStudents(
                $subject->my_class_id,
                $subject->section_id
            );
            $this->info("Assigned {$subject->name} to students");
        }
        
        $this->info('All subjects assigned successfully!');
    }
}