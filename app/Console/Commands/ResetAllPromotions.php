<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Promotion;
use App\Models\StudentRecord;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class ResetAllPromotions extends Command
{
    protected $signature = 'promotions:reset-all {school_id? : Optional school ID}';
    protected $description = 'Completely reset all promotions and rebuild current academic year cleanly';

    public function handle()
    {
        $inputSchoolId = $this->argument('school_id');

        // Load all schools with their current academic year
        $schools = School::with('academicYear')->get();

        if ($schools->isEmpty()) {
            $this->error('No schools found in database.');
            return 1;
        }

        // Build choices: index => "Name (ID: x)"
        $choices = [];
        foreach ($schools as $school) {
            $choices[$school->id] = "{$school->name} (ID: {$school->id})";
        }

        // Determine which school to use
        if ($inputSchoolId) {
            $school = $schools->firstWhere('id', $inputSchoolId);
        } else {
            $defaultIndex = $schools->first()->id;
;
            $selectedId = $this->choice(
                'Which school do you want to reset promotions for?',
                $choices,
                $defaultIndex
            );
            // Extract actual ID from selected string like "Elite International College (ID: 1)"
            preg_match('/\(ID: (\d+)\)/', $selectedId, $matches);
            $school = $schools->find($matches[1] ?? null);
        }

        if (!$school) {
            $this->error('School not found or invalid selection!');
            return 1;
        }

        if (!$school->academicYear) {
            $this->error("School '{$school->name}' has no active academic year. Set it in School Settings first.");
            return 1;
        }

        $year = $school->academicYear;

        $this->newLine();
        $this->alert("RESETTING PROMOTIONS");
        $this->info("School: {$school->name}");
        $this->info("Current Academic Year: {$year->name}");
        $this->newLine();

        $this->warn("This will:");
        $this->info("   • Delete ALL promotion records");
        $this->info("   • Wipe all academic year class/section history except current year");
        $this->info("   • Rebuild current year from current student class assignments");
        $this->newLine();

        if (!$this->confirm('Are you ABSOLUTELY sure? This cannot be undone.', false)) {
            $this->info('Cancelled. Nothing was changed.');
            return 0;
        }

        $this->info('Starting full reset...');

        DB::transaction(function () use ($school, $year) {
            $this->line('<fg=yellow>Deleting all promotion records...</>');
            Promotion::where('school_id', $school->id)->delete();

            $this->line('<fg=yellow>Removing old academic year records...</>');
            DB::table('academic_year_student_record')
                ->where('academic_year_id', '!=', $year->id)
                ->delete();

            $this->line('<fg=yellow>Clearing current year records (prevent duplicates)...</>');
            DB::table('academic_year_student_record')
                ->where('academic_year_id', $year->id)
                ->delete();

            $this->line('<fg=yellow>Rebuilding current academic year from student_records...</>');
            $count = 0;

            StudentRecord::whereHas('user', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            })->chunkById(200, function ($records) use ($year, &$count) {
                foreach ($records as $record) {
                    $record->academicYears()->syncWithoutDetaching([
                        $year->id => [
                            'my_class_id' => $record->my_class_id,
                            'section_id'  => $record->section_id,
                        ]
                    ]);
                    $count++;
                }
            });

            $this->info("Successfully rebuilt $count students in {$year->name}");
        });

        $this->newLine();
        $this->components->info("ALL PROMOTIONS RESET SUCCESSFULLY FOR {$school->name}!");
        $this->components->warn("You can now promote students cleanly with correct section filtering and re-promotion support.");
        $this->newLine();

        return 0;
    }
}