<?php

namespace App\Console\Commands;

use App\Models\StudentRecord;
use App\Models\AcademicYear;
use App\Models\MyClass;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnoseStudentRecords extends Command
{
    protected $signature = 'students:diagnose {--fix : Automatically fix missing records}';
    protected $description = 'Diagnose and optionally fix student academic year records';

    public function handle()
    {
        $schoolAdmin = User::role('admin')->with('school')->first();
        $school = $schoolAdmin->school ?? null;
        
        if (!$school) {
            $this->error('No school found! Please ensure there is a school_admin user with an associated school.');
            return;
        }

        // $school = auth()->user()->school ?? User::role('school_admin')->first()->school;
        $currentYear = $school->academicYear;

        if (!$currentYear) {
            $this->error('No current academic year set for school!');
            return;
        }

        $this->info("School: {$school->name}");
        $this->info("Current Academic Year: {$currentYear->name}");
        $this->line('');

        // Get all active students
        $allStudents = StudentRecord::where('is_graduated', false)
            ->whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->with(['user', 'myClass', 'section'])
            ->get();

        $this->info("Total Active Students: {$allStudents->count()}");
        $this->line('');

        // Check who has academic year records
        $studentsWithRecords = [];
        $studentsWithoutRecords = [];

        foreach ($allStudents as $student) {
            $hasRecord = DB::table('academic_year_student_record')
                ->where('student_record_id', $student->id)
                ->where('academic_year_id', $currentYear->id)
                ->exists();

            if ($hasRecord) {
                $studentsWithRecords[] = $student;
            } else {
                $studentsWithoutRecords[] = $student;
            }
        }

        $this->info("Students WITH academic year records: " . count($studentsWithRecords));
        $this->info("Students WITHOUT academic year records: " . count($studentsWithoutRecords));
        $this->line('');

        if (!empty($studentsWithoutRecords)) {
            $this->warn("Students missing academic year records:");
            $this->table(
                ['Name', 'Admission No', 'Current Class', 'Current Section'],
                collect($studentsWithoutRecords)->map(fn($s) => [
                    $s->user->name,
                    $s->admission_number,
                    $s->myClass->name,
                    $s->section?->name ?? '—'
                ])
            );

            if ($this->option('fix') || $this->confirm('Create missing academic year records?')) {
                foreach ($studentsWithoutRecords as $student) {
                    DB::table('academic_year_student_record')->insert([
                        'student_record_id' => $student->id,
                        'academic_year_id' => $currentYear->id,
                        'my_class_id' => $student->my_class_id,
                        'section_id' => $student->section_id,
                    ]);
                    $this->info("✓ Created record for {$student->user->name}");
                }
                $this->info('All missing records created!');
            }
        } else {
            $this->info('✓ All students have proper academic year records!');
        }

        $this->line('');
        
        // Show breakdown by class
        $this->info('Students by Class (in current academic year):');
        $classCounts = [];
        
        foreach ($studentsWithRecords as $student) {
            $pivot = DB::table('academic_year_student_record')
                ->where('student_record_id', $student->id)
                ->where('academic_year_id', $currentYear->id)
                ->first();
            
            if ($pivot) {
                $class = MyClass::find($pivot->my_class_id);
                $className = $class ? $class->name : 'Unknown';
                $classCounts[$className] = ($classCounts[$className] ?? 0) + 1;
            }
        }

        foreach ($classCounts as $class => $count) {
            $this->line("  {$class}: {$count} students");
        }
    }
}