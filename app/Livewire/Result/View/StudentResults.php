<?php

namespace App\Livewire\Result\View;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\{StudentRecord, MyClass, Section, Result, Subject};
use Illuminate\Support\Facades\DB;

class StudentResults extends Component
{
    use WithPagination;

    public $academicYearId;
    public $semesterId;
    public $selectedClass;
    public $selectedSection;
    public $searchTerm = '';
    public $perPage = 10;
    
    // View student details
    public $viewingStudent = false;
    public $studentRecord;
    public $subjects = [];
    public $results = [];
    public $studentPosition;
    public $totalStudents;

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->academicYearId = session('result_academic_year_id');
        $this->semesterId = session('result_semester_id');
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];
        $this->reset(['selectedClass', 'selectedSection', 'viewingStudent']);
    }

    public function viewStudent($studentId)
    {
        $this->studentRecord = StudentRecord::with(['user', 'myClass', 'section'])
            ->findOrFail($studentId);

        // Get subjects for this student
        $this->subjects = Subject::where('my_class_id', $this->studentRecord->my_class_id)
            ->orderBy('name')
            ->get();

        // Load results
        $resultsCollection = Result::where('student_record_id', $this->studentRecord->id)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->with('subject')
            ->get();

        $this->results = [];
        foreach ($resultsCollection as $result) {
            $this->results[$result->subject_id] = [
                'ca1_score' => $result->ca1_score,
                'ca2_score' => $result->ca2_score,
                'ca3_score' => $result->ca3_score,
                'ca4_score' => $result->ca4_score,
                'exam_score' => $result->exam_score,
                'total_score' => $result->total_score,
                'grade' => $this->calculateGrade($result->total_score),
                'comment' => $result->teacher_comment,
            ];
        }

        // Calculate position
        $this->calculatePosition();

        $this->viewingStudent = true;
    }

    protected function calculatePosition()
    {
        $classStudents = StudentRecord::with(['results' => function ($query) {
            $query->where('academic_year_id', $this->academicYearId)
                ->where('semester_id', $this->semesterId);
        }])
        ->where('my_class_id', $this->studentRecord->my_class_id)
        ->whereHas('user', fn($q) => $q->whereNull('deleted_at'))
        ->get();

        $this->totalStudents = $classStudents->count();

        $scores = $classStudents->map(function ($record) {
            return [
                'id' => $record->id,
                'total_score' => $record->results->sum('total_score'),
            ];
        })->sortByDesc('total_score')->values();

        $rank = 1;
        $prevScore = null;
        $studentsAtRank = 0;

        foreach ($scores as $data) {
            if ($prevScore !== null && $data['total_score'] < $prevScore) {
                $rank += $studentsAtRank;
                $studentsAtRank = 1;
            } else {
                $studentsAtRank++;
            }

            if ($data['id'] == $this->studentRecord->id) {
                $this->studentPosition = $rank;
                break;
            }
            $prevScore = $data['total_score'];
        }
    }

    protected function calculateGrade($score)
    {
        return match (true) {
            $score >= 75 => 'A1',
            $score >= 70 => 'B2',
            $score >= 65 => 'B3',
            $score >= 60 => 'C4',
            $score >= 55 => 'C5',
            $score >= 50 => 'C6',
            $score >= 45 => 'D7',
            $score >= 40 => 'E8',
            default => 'F9',
        };
    }

    public function backToList()
    {
        $this->viewingStudent = false;
        $this->reset(['studentRecord', 'subjects', 'results']);
    }

    public function render()
    {
        $classes = MyClass::orderBy('name')->get();
        $sections = Section::when($this->selectedClass, fn($q) => $q->where('my_class_id', $this->selectedClass))->get();
    
        $students = collect();
        
        if ($this->selectedClass && !$this->viewingStudent) {
            $studentRecordIds = DB::table('academic_year_student_record')
                ->where('academic_year_id', $this->academicYearId)
                ->where('my_class_id', $this->selectedClass)
                ->when($this->selectedSection, fn($q) => $q->where('section_id', $this->selectedSection))
                ->pluck('student_record_id');
    
            $students = StudentRecord::whereIn('student_records.id', $studentRecordIds) // Change here
                ->with(['user' => function($query) {
                    $query->whereNull('deleted_at');
                }, 'results' => function($q) {
                    $q->where('academic_year_id', $this->academicYearId)
                      ->where('semester_id', $this->semesterId);
                }])
                ->when($this->searchTerm, function($q) {
                    $q->whereHas('user', function($query) {
                        $query->where('name', 'like', '%' . $this->searchTerm . '%')
                              ->whereNull('deleted_at');
                    });
                })
                ->whereHas('user', function($query) {
                    $query->whereNull('deleted_at');
                })
                ->orderByName()
                ->paginate($this->perPage);
        }    
    
        return view('livewire.result.view.student-results', compact('classes', 'sections', 'students'))
            ->layout('layouts.new', [
                'title' => 'View Student Results',
                'page_heading' => 'View Student Results'
            ]);
    }
}