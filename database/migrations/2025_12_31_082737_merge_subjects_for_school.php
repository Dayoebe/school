<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Subject;

return new class extends Migration
{
    public function up(): void
    {
        try {
            echo "\n🔄 Starting subject merge migration...\n\n";
            
            // Get all subjects grouped by school and name
            $schools = DB::table('subjects')
                ->select('school_id')
                ->distinct()
                ->pluck('school_id');
            
            foreach ($schools as $schoolId) {
                echo "Processing School ID: {$schoolId}\n";
                $this->mergeSubjectsForSchool($schoolId);
                echo "\n";
            }
            
            echo "✅ Migration completed successfully!\n";
        } catch (\Exception $e) {
            echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . "\n";
            echo "Line: " . $e->getLine() . "\n";
            throw $e;
        }
    }
    
    protected function mergeSubjectsForSchool($schoolId)
    {
        // Group subjects by name (case-insensitive) and short_name
        $subjectGroups = DB::table('subjects')
            ->where('school_id', $schoolId)
            ->whereNull('deleted_at')
            ->get()
            ->groupBy(function($subject) {
                return strtolower(trim($subject->name)) . '|' . strtolower(trim($subject->short_name));
            });
        
        foreach ($subjectGroups as $key => $subjects) {
            if ($subjects->count() <= 1) {
                continue; // No duplicates, skip
            }
            
            // Sort by ID to keep the oldest as primary
            $sorted = $subjects->sortBy('id');
            $primarySubject = $sorted->first();
            $duplicates = $sorted->slice(1);
            
            echo "  📚 Merging {$subjects->count()} instances of '{$primarySubject->name}' (Primary ID: {$primarySubject->id})\n";
            
            // Update primary subject to be general
            DB::table('subjects')
                ->where('id', $primarySubject->id)
                ->update([
                    'my_class_id' => null,
                    'is_general' => true,
                    'updated_at' => now()
                ]);
            
            // Create class associations for primary subject
            $classIds = $subjects->pluck('my_class_id')->filter()->unique();
            
            foreach ($classIds as $classId) {
                try {
                    DB::table('class_subject')->insertOrIgnore([
                        'subject_id' => $primarySubject->id,
                        'my_class_id' => $classId,
                        'school_id' => $schoolId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } catch (\Exception $e) {
                    echo "    ⚠️  Warning: Could not assign class {$classId}: {$e->getMessage()}\n";
                }
            }
            
            foreach ($duplicates as $duplicate) {
                echo "    ↳ Processing duplicate ID {$duplicate->id} (Class: {$duplicate->my_class_id})\n";
                
                try {
                    // Migrate in order to avoid foreign key issues
                    $this->migrateTeacherAssignments($duplicate->id, $primarySubject->id, $duplicate->my_class_id, $schoolId);
                    $this->migrateStudentSubjects($duplicate->id, $primarySubject->id);
                    $this->migrateResults($duplicate->id, $primarySubject->id);
                    $this->migrateTimetableRecords($duplicate->id, $primarySubject->id);
                    
                    // Mark as legacy
                    DB::table('subjects')
                        ->where('id', $duplicate->id)
                        ->update([
                            'is_legacy' => true,
                            'merged_into_subject_id' => $primarySubject->id,
                            'updated_at' => now()
                        ]);
                    
                    echo "      ✓ Successfully migrated\n";
                } catch (\Exception $e) {
                    echo "      ✗ Error migrating duplicate {$duplicate->id}: {$e->getMessage()}\n";
                    // Continue with other duplicates
                }
            }
        }
    }
    
    protected function migrateTeacherAssignments($oldSubjectId, $newSubjectId, $classId, $schoolId)
    {
        // Get old teacher assignments from subject_user
        $teachers = DB::table('subject_user')
            ->where('subject_id', $oldSubjectId)
            ->get();
        
        foreach ($teachers as $teacher) {
            // Check if this teacher-subject-class combination already exists
            $exists = DB::table('subject_teacher')
                ->where('subject_id', $newSubjectId)
                ->where('user_id', $teacher->user_id)
                ->where(function($q) use ($classId) {
                    $q->where('my_class_id', $classId)
                      ->orWhere(function($q2) {
                          $q2->whereNull('my_class_id')
                             ->where('is_general', true);
                      });
                })
                ->exists();
            
            if (!$exists) {
                try {
                    DB::table('subject_teacher')->insert([
                        'subject_id' => $newSubjectId,
                        'user_id' => $teacher->user_id,
                        'my_class_id' => $classId,
                        'school_id' => $schoolId,
                        'is_general' => false,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } catch (\Exception $e) {
                    // Teacher might already be assigned, skip
                    echo "        ⚠️  Teacher {$teacher->user_id} already assigned\n";
                }
            }
        }
    }
    
    protected function migrateStudentSubjects($oldSubjectId, $newSubjectId)
    {
        // Get all student enrollments for the old subject
        $enrollments = DB::table('student_subject')
            ->where('subject_id', $oldSubjectId)
            ->get();
        
        $migratedCount = 0;
        $skippedCount = 0;
        
        foreach ($enrollments as $enrollment) {
            // Check if student is already enrolled in the new subject
            $exists = DB::table('student_subject')
                ->where('student_record_id', $enrollment->student_record_id)
                ->where('subject_id', $newSubjectId)
                ->exists();
            
            if ($exists) {
                // Student already enrolled in primary subject, just delete the duplicate
                DB::table('student_subject')
                    ->where('id', $enrollment->id)
                    ->delete();
                $skippedCount++;
            } else {
                // Update to point to primary subject
                DB::table('student_subject')
                    ->where('id', $enrollment->id)
                    ->update([
                        'subject_id' => $newSubjectId,
                        'updated_at' => now()
                    ]);
                $migratedCount++;
            }
        }
        
        if ($migratedCount > 0 || $skippedCount > 0) {
            echo "        📝 Students: {$migratedCount} migrated, {$skippedCount} duplicates removed\n";
        }
    }
    
    protected function migrateResults($oldSubjectId, $newSubjectId)
    {
        // Count results to migrate
        $count = DB::table('results')
            ->where('subject_id', $oldSubjectId)
            ->count();
        
        if ($count > 0) {
            // Update results table - CRITICAL for preserving student grades
            DB::table('results')
                ->where('subject_id', $oldSubjectId)
                ->update([
                    'subject_id' => $newSubjectId,
                    'updated_at' => now()
                ]);
            
            echo "        📊 Results: {$count} records migrated\n";
        }
    }
    
    protected function migrateTimetableRecords($oldSubjectId, $newSubjectId)
    {
        // Check if timetable_records table exists
        if (!Schema::hasTable('timetable_records')) {
            return;
        }
        
        // Count records to migrate
        $count = DB::table('timetable_records')
            ->where('timetable_time_slot_weekdayable_type', 'App\\Models\\Subject')
            ->where('timetable_time_slot_weekdayable_id', $oldSubjectId)
            ->count();
        
        if ($count > 0) {
            DB::table('timetable_records')
                ->where('timetable_time_slot_weekdayable_type', 'App\\Models\\Subject')
                ->where('timetable_time_slot_weekdayable_id', $oldSubjectId)
                ->update([
                    'timetable_time_slot_weekdayable_id' => $newSubjectId,
                    'updated_at' => now()
                ]);
            
            echo "        🕐 Timetable: {$count} records migrated\n";
        }
    }

    public function down(): void
    {
        echo "⚠️  Warning: This migration cannot be safely rolled back.\n";
        echo "Please restore from backup if you need to undo these changes.\n";
    }
};