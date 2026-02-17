<?php

namespace App\Livewire\Result;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{Result, MyClass, StudentRecord, Semester};
use Illuminate\Support\Facades\DB;

class AwardsManager extends Component
{
    public $academicYearId;
    public $semesterId;
    public $selectedClassId;
    public $viewType = 'termly'; // termly or annual
    
    public $classes;
    public $semesters = [];
    public $topPerformers = [];

    public function mount()
    {
        $this->classes = MyClass::orderBy('name')->get();
        $this->academicYearId = session('result_academic_year_id');
        $this->semesterId = session('result_semester_id');
        
        $this->loadSemesters();
        $this->loadTopPerformers();
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];
        $this->loadSemesters();
        $this->loadTopPerformers();
    }

    public function updatedSelectedClassId()
    {
        $this->loadTopPerformers();
    }

    public function updatedViewType()
    {
        $this->loadTopPerformers();
    }

    protected function loadSemesters()
    {
        if ($this->academicYearId) {
            $this->semesters = Semester::where('academic_year_id', $this->academicYearId)->get();
        }
    }

    public function loadTopPerformers()
    {
        if (!$this->academicYearId) {
            $this->topPerformers = [];
            return;
        }

        // For termly, we need a semester selected
        if ($this->viewType === 'termly' && !$this->semesterId) {
            $this->topPerformers = [];
            return;
        }

        // Get student record IDs
        $query = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId);

        if ($this->selectedClassId) {
            $query->where('my_class_id', $this->selectedClassId);
        }

        $studentRecordIds = $query->pluck('student_record_id');

        if ($studentRecordIds->isEmpty()) {
            $this->topPerformers = [];
            return;
        }

        // Get results based on view type
        $resultsQuery = Result::whereIn('student_record_id', $studentRecordIds)
            ->where('academic_year_id', $this->academicYearId);

        if ($this->viewType === 'termly') {
            $resultsQuery->where('semester_id', $this->semesterId);
        } else {
            // Annual - get all semesters
            $resultsQuery->whereIn('semester_id', $this->semesters->pluck('id'));
        }

        $results = $resultsQuery->with([
            'studentRecord' => function($query) {
                $query->with(['user', 'myClass']);
            }, 
            'subject'
        ])
        ->get();

        if ($results->isEmpty()) {
            $this->topPerformers = [];
            return;
        }

        // Calculate student performances with proper filtering
        $studentData = [];
        $totalSubjectsPerClass = $this->getTotalSubjectsPerClass();
        
        foreach ($results->groupBy('student_record_id') as $studentId => $studentResults) {
            // Filter out results with null or 0 scores
            $validResults = $studentResults->filter(function($result) {
                return $result->total_score !== null && $result->total_score > 0;
            });
        
            if ($validResults->isEmpty()) {
                continue; // Skip students with no valid scores
            }
        
            $totalScore = $validResults->sum('total_score');
            $average = $validResults->avg('total_score');
        
            // CRITICAL FIX: Check if the student has taken enough subjects
            // Use null-safe operators and check if relationships exist
            $firstResult = $validResults->first();
            
            // Check if studentRecord exists
            if (!$firstResult->studentRecord) {
                continue; // Skip if student record doesn't exist
            }
            
            $studentClass = $firstResult->studentRecord->myClass;
            
            // Check if myClass exists for this student
            if (!$studentClass) {
                continue; // Skip if student doesn't have a class assigned
            }
            
            $expectedSubjectCount = $totalSubjectsPerClass[$studentClass->id] ?? 0;
        
            // Skip if student has taken less than 50% of expected subjects
            $subjectCompletionRatio = $validResults->count() / max($expectedSubjectCount, 1);
            if ($subjectCompletionRatio < 0.5) {
                continue;
            }
            
            $aGrades = $validResults->filter(fn($r) => $r->total_score >= 75)->count();
            
            // Calculate consistency (lower is better) - only for students with at least 3 subjects
            $consistency = 0;
            if ($validResults->count() >= 3) {
                $scores = $validResults->pluck('total_score')->toArray();
                $consistency = $this->calculateStdDev($scores);
            }
        
            $studentData[$studentId] = [
                'student' => $firstResult->studentRecord,
                'total' => $totalScore,
                'average' => round($average, 2),
                'a_grades' => $aGrades,
                'consistency' => round($consistency, 2),
                'subject_count' => $validResults->count(),
                'expected_subjects' => $expectedSubjectCount,
                'completion_ratio' => $subjectCompletionRatio,
            ];
        }

        // If no valid students, return empty
        if (empty($studentData)) {
            $this->topPerformers = [];
            return;
        }

        // Filter students for eligibility:
        // 1. Must have at least 3 subjects
        // 2. Must have completed at least 50% of expected subjects
        // 3. Must have an average above 40%
        $eligibleStudents = collect($studentData)->filter(function($student) {
            return $student['subject_count'] >= 3 
                   && $student['completion_ratio'] >= 0.5
                   && $student['average'] >= 40;
        });

        if ($eligibleStudents->isEmpty()) {
            $this->topPerformers = [];
            return;
        }

        // Top 3 by Average (from eligible students only)
        $top3 = $eligibleStudents->sortByDesc('average')->take(3)->values();

        // Highest Total Score (from eligible students)
        $highestTotal = $eligibleStudents->sortByDesc('total')->first();

        // Most A Grades (from eligible students, at least 1 A grade)
        $mostAs = $eligibleStudents->where('a_grades', '>', 0)
            ->sortByDesc('a_grades')
            ->first();

        // Most Consistent (from eligible students with at least 3 subjects and average >= 50)
        $mostConsistent = $eligibleStudents
            ->filter(fn($s) => $s['subject_count'] >= 3 && $s['average'] >= 50)
            ->sortBy('consistency')
            ->first();

        // Best per subject (filter out 0 scores and require score > 40)
        $bestInSubjects = [];
        foreach ($results->groupBy('subject_id') as $subjectId => $subjectResults) {
            // Filter out 0 scores and require minimum passing score
            $validResults = $subjectResults->filter(fn($r) => 
                $r->total_score !== null && $r->total_score > 40
            );
            
            if ($validResults->isEmpty()) {
                continue;
            }

            $best = $validResults->sortByDesc('total_score')->first();
            
            // Verify this student is in the eligible list
            $studentId = $best->student_record_id;
            if (!isset($studentData[$studentId]) || $studentData[$studentId]['average'] < 40) {
                continue;
            }
            
            $bestInSubjects[] = [
                'subject' => $best->subject,
                'student' => $best->studentRecord,
                'score' => $best->total_score,
            ];
        }

        $this->topPerformers = [
            'top_3' => $top3,
            'highest_total' => $highestTotal,
            'most_as' => $mostAs,
            'most_consistent' => $mostConsistent,
            'best_in_subjects' => $bestInSubjects,
        ];
    }

   /**
 * Get total number of subjects expected per class
 */
protected function getTotalSubjectsPerClass()
{
    $classes = MyClass::withCount('subjects')->get();
    $subjectCounts = [];
    
    foreach ($classes as $class) {
        $subjectCounts[$class->id] = $class->subjects_count;
    }
    
    return $subjectCounts;
}

    protected function calculateStdDev($values)
    {
        $count = count($values);
        if ($count === 0) return 0;
        
        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / $count;
        
        return sqrt($variance);
    }

    public function render()
    {
        return view('livewire.result.awards-manager');
    }
}