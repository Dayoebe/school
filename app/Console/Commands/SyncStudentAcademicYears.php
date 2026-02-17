<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\StudentRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncStudentAcademicYears extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:sync-academic-years 
                            {--academic-year-id= : Specific academic year ID to sync to}
                            {--school-id= : Specific school ID to sync}
                            {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all existing students to their academic year records. Creates missing pivot entries.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $academicYearId = $this->option('academic-year-id');
        $schoolId = $this->option('school-id');

        $this->info('🔄 Starting Student Academic Year Sync...');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        try {
            // Get target academic year
            if ($academicYearId) {
                $academicYear = AcademicYear::find($academicYearId);
                if (!$academicYear) {
                    $this->error("❌ Academic year with ID {$academicYearId} not found!");
                    return 1;
                }
            } else {
                // Use 2024-2025 as default
                $academicYear = AcademicYear::where('start_year', 2024)
                    ->where('stop_year', 2025)
                    ->when($schoolId, function($q) use ($schoolId) {
                        $q->where('school_id', $schoolId);
                    })
                    ->first();

                if (!$academicYear) {
                    $this->error('❌ 2024-2025 academic year not found!');
                    $this->info('💡 Creating 2024-2025 academic year...');
                    
                    if (!$isDryRun) {
                        $academicYear = AcademicYear::create([
                            'start_year' => 2024,
                            'stop_year' => 2025,
                            'school_id' => $schoolId ?? auth()->user()->school_id ?? 1,
                        ]);
                        $this->info('✅ Created 2024-2025 academic year');
                    }
                }
            }

            $this->info("📅 Target Academic Year: {$academicYear->name}");
            $this->newLine();

            // Get all student records
            $query = StudentRecord::with('user')
                ->where('is_graduated', false)
                ->when($schoolId, function($q) use ($schoolId) {
                    $q->whereHas('user', function($userQuery) use ($schoolId) {
                        $userQuery->where('school_id', $schoolId);
                    });
                });

            $studentRecords = $query->get();
            $this->info("👥 Found {$studentRecords->count()} active student records");
            $this->newLine();

            $created = 0;
            $existing = 0;
            $errors = 0;

            $progressBar = $this->output->createProgressBar($studentRecords->count());
            $progressBar->start();

            foreach ($studentRecords as $record) {
                try {
                    // Check if pivot record exists
                    $exists = DB::table('academic_year_student_record')
                        ->where('student_record_id', $record->id)
                        ->where('academic_year_id', $academicYear->id)
                        ->exists();

                    if ($exists) {
                        $existing++;
                    } else {
                        if (!$isDryRun) {
                            // Create pivot record
                            DB::table('academic_year_student_record')->insert([
                                'student_record_id' => $record->id,
                                'academic_year_id' => $academicYear->id,
                                'my_class_id' => $record->my_class_id,
                                'section_id' => $record->section_id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        $created++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $this->newLine();
                    $this->error("❌ Error for student {$record->user->name}: {$e->getMessage()}");
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            // Summary
            $this->info('📊 SYNC SUMMARY');
            $this->info('═══════════════════════════════════════');
            $this->table(
                ['Status', 'Count'],
                [
                    ['✅ Already Synced', $existing],
                    ['➕ New Records Created', $created],
                    ['❌ Errors', $errors],
                    ['📈 Total Processed', $studentRecords->count()],
                ]
            );

            if ($isDryRun) {
                $this->newLine();
                $this->warn('⚠️  DRY RUN: No changes were made to the database');
                $this->info('💡 Run without --dry-run to apply changes');
            } else {
                $this->newLine();
                $this->info('✅ Sync completed successfully!');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Fatal error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}