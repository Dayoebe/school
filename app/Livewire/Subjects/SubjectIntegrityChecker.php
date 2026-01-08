<?php

namespace App\Livewire\Subjects;

use App\Models\Subject;
use App\Models\MyClass;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class SubjectIntegrityChecker extends Component
{
    public $showModal = false;
    public $isChecking = false;
    public $isFixing = false;
    public $checkResults = [];
    public $fixResults = [];
    
    public function openModal()
    {
        $this->showModal = true;
        $this->checkResults = [];
        $this->fixResults = [];
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->isChecking = false;
        $this->isFixing = false;
    }
    
    public function runIntegrityCheck()
    {
        $this->isChecking = true;
        $this->checkResults = [];
        
        try {
            $schoolId = auth()->user()->school_id;
            
            // 1. Check for duplicate subjects (same name in same school)
            $duplicateNames = Subject::select('name', 'school_id', DB::raw('COUNT(*) as count'))
                ->where('school_id', $schoolId)
                ->where('is_legacy', false)
                ->groupBy('name', 'school_id')
                ->having('count', '>', 1)
                ->pluck('name');
            
            $duplicateSubjects = collect();
            foreach ($duplicateNames as $name) {
                $subjects = Subject::where('name', $name)
                    ->where('school_id', $schoolId)
                    ->where('is_legacy', false)
                    ->with(['classes', 'teachers'])
                    ->get();
                
                $duplicateSubjects->push([
                    'name' => $name,
                    'count' => $subjects->count(),
                    'subjects' => $subjects->map(fn($s) => [
                        'id' => $s->id,
                        'short_name' => $s->short_name,
                        'classes_count' => $s->classes->count(),
                        'teachers_count' => $s->teachers->count(),
                        'created_at' => $s->created_at->format('Y-m-d H:i'),
                    ])
                ]);
            }
            
            $this->checkResults['duplicates'] = [
                'count' => $duplicateSubjects->sum('count') - $duplicateSubjects->count(),
                'items' => $duplicateSubjects
            ];
            
            // 2. Check for subjects with no classes assigned
            $subjectsWithoutClasses = Subject::where('school_id', $schoolId)
                ->where('is_legacy', false)
                ->whereDoesntHave('classes')
                ->get(['id', 'name', 'short_name']);
            
            $this->checkResults['no_classes'] = [
                'count' => $subjectsWithoutClasses->count(),
                'items' => $subjectsWithoutClasses
            ];
            
            // 3. Check for subjects assigned to non-existent classes
            $orphanedClassAssignments = DB::table('class_subject as cs')
                ->leftJoin('my_classes', 'cs.my_class_id', '=', 'my_classes.id')
                ->join('subjects', 'cs.subject_id', '=', 'subjects.id')
                ->whereNull('my_classes.id')
                ->where('subjects.school_id', $schoolId)
                ->select(
                    'cs.*', 
                    'subjects.id as subject_id',
                    'subjects.name as subject_name',
                    'subjects.short_name'
                )
                ->get();
            
            $this->checkResults['orphaned_classes'] = [
                'count' => $orphanedClassAssignments->count(),
                'items' => $orphanedClassAssignments
            ];
            
            // 4. Check for teacher assignments to non-existent users
            $orphanedTeachers = DB::table('subject_teacher as st')
                ->leftJoin('users', 'st.user_id', '=', 'users.id')
                ->join('subjects', 'st.subject_id', '=', 'subjects.id')
                ->whereNull('users.id')
                ->where('subjects.school_id', $schoolId)
                ->select(
                    'st.*', 
                    'subjects.id as subject_id',
                    'subjects.name as subject_name',
                    'subjects.short_name'
                )
                ->get();
            
            $this->checkResults['orphaned_teachers'] = [
                'count' => $orphanedTeachers->count(),
                'items' => $orphanedTeachers
            ];
            
            // 5. Check for student-subject assignments with missing students
            $orphanedStudentSubjects = DB::table('student_subject as ss')
                ->leftJoin('student_records', 'ss.student_record_id', '=', 'student_records.id')
                ->join('subjects', 'ss.subject_id', '=', 'subjects.id')
                ->whereNull('student_records.id')
                ->where('subjects.school_id', $schoolId)
                ->select(
                    'ss.*', 
                    'subjects.id as subject_id',
                    'subjects.name as subject_name',
                    'subjects.short_name'
                )
                ->get();
            
            $this->checkResults['orphaned_students'] = [
                'count' => $orphanedStudentSubjects->count(),
                'items' => $orphanedStudentSubjects
            ];
            
            // 6. Check for legacy subjects that should be cleaned up
            $legacySubjects = Subject::where('school_id', $schoolId)
                ->where('is_legacy', true)
                ->whereDoesntHave('results')
                ->get(['id', 'name', 'short_name']);
            
            $this->checkResults['legacy_cleanup'] = [
                'count' => $legacySubjects->count(),
                'items' => $legacySubjects
            ];
            
            // 7. Check for subjects with invalid school_id
            $invalidSchool = Subject::where('school_id', $schoolId)
                ->where('is_legacy', false)
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('schools')
                        ->whereColumn('schools.id', 'subjects.school_id');
                })
                ->get(['id', 'name', 'short_name']);
            
            $this->checkResults['invalid_school'] = [
                'count' => $invalidSchool->count(),
                'items' => $invalidSchool
            ];
            
            // Calculate total issues
            $totalIssues = array_sum(array_column($this->checkResults, 'count'));
            $this->checkResults['total_issues'] = $totalIssues;
            
            session()->flash('check_complete', "Integrity check complete. Found {$totalIssues} issue(s).");
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error during integrity check: ' . $e->getMessage());
        }
        
        $this->isChecking = false;
    }
    
    public function fixIssues()
    {
        $this->isFixing = true;
        $this->fixResults = [];
        
        try {
            DB::beginTransaction();
            
            $schoolId = auth()->user()->school_id;
            
            // 1. Fix duplicates by merging them
            $duplicates = Subject::select('name', 'school_id')
                ->where('school_id', $schoolId)
                ->where('is_legacy', false)
                ->groupBy('name', 'school_id')
                ->having(DB::raw('COUNT(*)'), '>', 1)
                ->get();
            
            $mergedCount = 0;
            foreach ($duplicates as $dup) {
                $subjects = Subject::where('name', $dup->name)
                    ->where('school_id', $dup->school_id)
                    ->where('is_legacy', false)
                    ->orderBy('created_at')
                    ->get();
                
                if ($subjects->count() > 1) {
                    $primary = $subjects->first();
                    $duplicatesToMerge = $subjects->slice(1);
                    
                    foreach ($duplicatesToMerge as $duplicate) {
                        // Merge classes
                        $classes = $duplicate->classes()->pluck('my_classes.id');
                        foreach ($classes as $classId) {
                            $primary->assignToClass($classId);
                        }
                        
                        // Merge teachers
                        $teachers = DB::table('subject_teacher')
                            ->where('subject_id', $duplicate->id)
                            ->get();
                        
                        foreach ($teachers as $teacher) {
                            $primary->assignTeacher(
                                $teacher->user_id,
                                $teacher->my_class_id,
                                $teacher->is_general
                            );
                        }
                        
                        // Update student assignments
                        DB::table('student_subject')
                            ->where('subject_id', $duplicate->id)
                            ->update(['subject_id' => $primary->id]);
                        
                        // Update results
                        DB::table('results')
                            ->where('subject_id', $duplicate->id)
                            ->update(['subject_id' => $primary->id]);
                        
                        // Mark as legacy and point to primary
                        $duplicate->update([
                            'is_legacy' => true,
                            'merged_into_subject_id' => $primary->id
                        ]);
                        
                        $mergedCount++;
                    }
                }
            }
            $this->fixResults['duplicates_merged'] = $mergedCount;
            
            // 2. Remove orphaned class assignments
            $orphanedClassesRemoved = DB::table('class_subject')
                ->leftJoin('my_classes', 'class_subject.my_class_id', '=', 'my_classes.id')
                ->whereNull('my_classes.id')
                ->delete();
            
            $this->fixResults['orphaned_classes_removed'] = $orphanedClassesRemoved;
            
            // 3. Remove orphaned teacher assignments
            $orphanedTeachersRemoved = DB::table('subject_teacher')
                ->leftJoin('users', 'subject_teacher.user_id', '=', 'users.id')
                ->whereNull('users.id')
                ->delete();
            
            $this->fixResults['orphaned_teachers_removed'] = $orphanedTeachersRemoved;
            
            // 4. Remove orphaned student-subject assignments
            $orphanedStudentsRemoved = DB::table('student_subject')
                ->leftJoin('student_records', 'student_subject.student_record_id', '=', 'student_records.id')
                ->whereNull('student_records.id')
                ->delete();
            
            $this->fixResults['orphaned_students_removed'] = $orphanedStudentsRemoved;
            
            // 5. Delete unused legacy subjects (with no results)
            $legacyDeleted = Subject::where('school_id', $schoolId)
                ->where('is_legacy', true)
                ->whereDoesntHave('results')
                ->forceDelete();
            
            $this->fixResults['legacy_deleted'] = $legacyDeleted;
            
            // 6. Re-assign subjects to students in classes
            $subjects = Subject::where('school_id', $schoolId)
                ->where('is_legacy', false)
                ->with('classes')
                ->get();
            
            $reassignedCount = 0;
            foreach ($subjects as $subject) {
                $subject->autoAssignToClassStudents();
                $reassignedCount++;
            }
            $this->fixResults['subjects_reassigned'] = $reassignedCount;
            
            DB::commit();
            
            $totalFixed = array_sum($this->fixResults);
            session()->flash('success', "Successfully fixed {$totalFixed} issue(s)!");
            
            // Re-run check to show updated status
            $this->runIntegrityCheck();
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error fixing issues: ' . $e->getMessage());
        }
        
        $this->isFixing = false;
    }
    
    public function render()
    {
        return view('livewire.subjects.subject-integrity-checker');
    }
}