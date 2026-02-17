<?php

namespace App\Livewire\Result;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{StudentRecord, MyClass, Section, AcademicYear, Semester, Result};
use Illuminate\Support\Facades\DB;

class StudentHistory extends Component
{
    use WithPagination;

    public $selectedClass;
    public $selectedSection;
    public $searchTerm = '';
    public $selectedStudent;
    public $viewingHistory = false;
    
    // Student history data
    public $studentRecord;
    public $academicYears = [];
    public $historyData = [];
    public $overallStats = [];

    protected $paginationTheme = 'tailwind';

    public function viewHistory($studentId)
    {
        $this->studentRecord = StudentRecord::with(['user', 'myClass', 'section'])
            ->findOrFail($studentId);

        // Get all academic years this student has results for
        $this->academicYears = AcademicYear::whereHas('semesters.results', function($q) use ($studentId) {
            $q->where('student_record_id', $studentId);
        })
        ->with(['semesters' => function($q) use ($studentId) {
            $q->whereHas('results', function($query) use ($studentId) {
                $query->where('student_record_id', $studentId);
            });
        }])
        ->orderBy('start_year', 'desc')
        ->get();

        // Build history data
        $this->historyData = [];
        $allScores = [];

        foreach ($this->academicYears as $year) {
            $yearData = [
                'year' => $year,
                'semesters' => []
            ];

            foreach ($year->semesters as $semester) {
                $results = Result::where('student_record_id', $studentId)
                    ->where('academic_year_id', $year->id)
                    ->where('semester_id', $semester->id)
                    ->with('subject')
                    ->get();

                if ($results->isNotEmpty()) {
                    $totalScore = $results->sum('total_score');
                    $maxPossible = $results->count() * 100;
                    $percentage = $maxPossible > 0 ? round(($totalScore / $maxPossible) * 100, 2) : 0;

                    $yearData['semesters'][] = [
                        'semester' => $semester,
                        'results' => $results,
                        'total_score' => $totalScore,
                        'percentage' => $percentage,
                        'subjects_count' => $results->count(),
                    ];

                    $allScores[] = $percentage;
                }
            }

            if (!empty($yearData['semesters'])) {
                $this->historyData[] = $yearData;
            }
        }

        // Calculate overall statistics
        if (!empty($allScores)) {
            $this->overallStats = [
                'total_terms' => count($allScores),
                'average_score' => round(array_sum($allScores) / count($allScores), 2),
                'highest_score' => max($allScores),
                'lowest_score' => min($allScores),
            ];

            // Find best and worst subjects
            $subjectScores = [];
            foreach ($this->historyData as $yearData) {
                foreach ($yearData['semesters'] as $semesterData) {
                    foreach ($semesterData['results'] as $result) {
                        if (!isset($subjectScores[$result->subject->name])) {
                            $subjectScores[$result->subject->name] = [];
                        }
                        $subjectScores[$result->subject->name][] = $result->total_score;
                    }
                }
            }

            $subjectAverages = [];
            foreach ($subjectScores as $subject => $scores) {
                $subjectAverages[$subject] = round(array_sum($scores) / count($scores), 2);
            }

            arsort($subjectAverages);
            $this->overallStats['best_subject'] = array_key_first($subjectAverages) ?? 'N/A';
            $this->overallStats['best_subject_avg'] = reset($subjectAverages) ?: 0;
            
            asort($subjectAverages);
            $this->overallStats['worst_subject'] = array_key_first($subjectAverages) ?? 'N/A';
            $this->overallStats['worst_subject_avg'] = reset($subjectAverages) ?: 0;
        }

        $this->viewingHistory = true;
    }

    public function backToList()
    {
        $this->viewingHistory = false;
        $this->reset(['studentRecord', 'academicYears', 'historyData', 'overallStats']);
    }

    public function render()
    {
        $classes = MyClass::orderBy('name')->get();
        $sections = Section::when($this->selectedClass, fn($q) => $q->where('my_class_id', $this->selectedClass))->get();
    
        $students = collect();
        
        if ($this->selectedClass && !$this->viewingHistory) {
            $students = StudentRecord::with(['user' => function($query) {
                    $query->whereNull('deleted_at');
                }, 'myClass'])
                ->where('my_class_id', $this->selectedClass)
                ->when($this->selectedSection, fn($q) => $q->where('section_id', $this->selectedSection))
                ->whereHas('user', function($query) {
                    $query->whereNull('deleted_at')
                        ->when($this->searchTerm, function($q) {
                            $q->where('name', 'like', '%' . $this->searchTerm . '%');
                        });
                })
                ->whereHas('results')
                ->orderByName()
                ->paginate(10);
        }
    
        return view('livewire.result.student-history', compact('classes', 'sections', 'students'))
            ->layout('layouts.new', [
                'title' => 'Student Academic History',
                'page_heading' => 'Student Academic History'
            ]);
    }
}