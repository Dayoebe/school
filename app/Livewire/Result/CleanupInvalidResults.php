<?php

namespace App\Livewire\Result;

use Livewire\Component;
use App\Models\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupInvalidResults extends Component
{
    public $showModal = false;
    public $isProcessing = false;
    public $cleanupResults = null;

    public function openModal()
    {
        $this->showModal = true;
        $this->analyzeInvalidResults();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->cleanupResults = null;
    }

    /**
     * Analyze invalid results without deleting
     */
    public function analyzeInvalidResults()
    {
        try {
            $this->cleanupResults = $this->findInvalidResults();
        } catch (\Exception $e) {
            Log::error('Analysis error: ' . $e->getMessage());
            $this->dispatch('error', 'Failed to analyze: ' . $e->getMessage());
        }
    }

    /**
     * Find all invalid results
     */
    protected function findInvalidResults()
    {
        // Type 1: Results where student is not enrolled in the subject
        $notEnrolled = Result::whereDoesntHave('student', function ($q) {
            $q->whereHas('studentSubjects', function ($q) {
                $q->whereColumn('subjects.id', 'results.subject_id');
            });
        })
        ->with(['student.user', 'subject', 'academicYear', 'semester'])
        ->get();

        // Type 2: Results with soft-deleted students
        $deletedStudents = Result::whereHas('student', function ($q) {
            $q->whereHas('user', function ($q) {
                $q->whereNotNull('deleted_at')->withTrashed();
            });
        })
        ->with(['student.user', 'subject', 'academicYear', 'semester'])
        ->get();

        // Type 3: Results with non-existent students
        $orphanedResults = Result::whereDoesntHave('student')
            ->with(['subject', 'academicYear', 'semester'])
            ->get();

        // Type 4: Results with non-existent subjects
        $orphanedSubjects = Result::whereDoesntHave('subject')
            ->with(['student.user', 'academicYear', 'semester'])
            ->get();

        // Type 5: Results where subject doesn't belong to student's class
        $wrongClassSubjects = Result::whereHas('student', function ($studentQuery) {
            $studentQuery->whereHas('myClass', function ($classQuery) {
                $classQuery->whereDoesntHave('subjects', function ($subjectQuery) {
                    $subjectQuery->whereColumn('subjects.id', 'results.subject_id');
                });
            });
        })
        ->with(['student.user', 'student.myClass', 'subject', 'academicYear', 'semester'])
        ->get();

        return [
            'not_enrolled' => $notEnrolled,
            'deleted_students' => $deletedStudents,
            'orphaned_results' => $orphanedResults,
            'orphaned_subjects' => $orphanedSubjects,
            'wrong_class_subjects' => $wrongClassSubjects,
            'total_invalid' => $notEnrolled->count() + 
                              $deletedStudents->count() + 
                              $orphanedResults->count() + 
                              $orphanedSubjects->count() +
                              $wrongClassSubjects->count(),
        ];
    }

    /**
     * Execute cleanup
     */
    public function executeCleanup()
    {
        $this->isProcessing = true;

        try {
            DB::beginTransaction();

            $deletedCount = 0;
            $details = [];

            // 1. Delete results where student not enrolled in subject
            $notEnrolledIds = Result::whereDoesntHave('student', function ($q) {
                $q->whereHas('studentSubjects', function ($q) {
                    $q->whereColumn('subjects.id', 'results.subject_id');
                });
            })->pluck('id');
            
            if ($notEnrolledIds->isNotEmpty()) {
                $count = Result::whereIn('id', $notEnrolledIds)->delete();
                $deletedCount += $count;
                $details[] = "Deleted {$count} results where students not enrolled in subjects";
            }

            // 2. Delete results with soft-deleted students
            $deletedStudentIds = Result::whereHas('student', function ($q) {
                $q->whereHas('user', function ($q) {
                    $q->whereNotNull('deleted_at')->withTrashed();
                });
            })->pluck('id');
            
            if ($deletedStudentIds->isNotEmpty()) {
                $count = Result::whereIn('id', $deletedStudentIds)->delete();
                $deletedCount += $count;
                $details[] = "Deleted {$count} results with soft-deleted students";
            }

            // 3. Delete orphaned results (no student record)
            $orphanedResultIds = Result::whereDoesntHave('student')->pluck('id');
            
            if ($orphanedResultIds->isNotEmpty()) {
                $count = Result::whereIn('id', $orphanedResultIds)->delete();
                $deletedCount += $count;
                $details[] = "Deleted {$count} orphaned results (no student)";
            }

            // 4. Delete results with non-existent subjects
            $orphanedSubjectIds = Result::whereDoesntHave('subject')->pluck('id');
            
            if ($orphanedSubjectIds->isNotEmpty()) {
                $count = Result::whereIn('id', $orphanedSubjectIds)->delete();
                $deletedCount += $count;
                $details[] = "Deleted {$count} results with non-existent subjects";
            }

            // 5. Delete results where subject doesn't belong to student's class
            $wrongClassIds = Result::whereHas('student', function ($studentQuery) {
                $studentQuery->whereHas('myClass', function ($classQuery) {
                    $classQuery->whereDoesntHave('subjects', function ($subjectQuery) {
                        $subjectQuery->whereColumn('subjects.id', 'results.subject_id');
                    });
                });
            })->pluck('id');
            
            if ($wrongClassIds->isNotEmpty()) {
                $count = Result::whereIn('id', $wrongClassIds)->delete();
                $deletedCount += $count;
                $details[] = "Deleted {$count} results with wrong class-subject assignments";
            }

            DB::commit();

            // Log the cleanup
            Log::info('Results cleanup completed', [
                'total_deleted' => $deletedCount,
                'details' => $details,
                'user_id' => auth()->id(),
            ]);

            $this->dispatch('success', "Cleanup successful! Deleted {$deletedCount} invalid results.");
            
            // Store details in session for display
            session()->flash('cleanup_details', $details);
            
            $this->closeModal();
            
            // Refresh the page to update any displayed data
            return redirect()->route('result');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cleanup error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('error', 'Cleanup failed: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function render()
    {
        return view('livewire.result.cleanup-invalid-results');
    }
}