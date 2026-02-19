<?php

namespace App\Livewire\Result\View;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{StudentRecord, MyClass, Subject, Result};
use Illuminate\Support\Facades\DB;

class SubjectResults extends Component
{
    public $academicYearId;
    public $semesterId;
    public $selectedClass;
    public $selectedSubject;
    
    public $subjects; // REMOVE = []
    public $subjectResults; // REMOVE = []
    public $subjectStats; // REMOVE = []

    public function mount()
    {
        $this->academicYearId = session('result_academic_year_id') ?? auth()->user()->school?->academic_year_id;
        $this->semesterId = session('result_semester_id') ?? auth()->user()->school?->semester_id;
        $this->subjects = collect(); // ADD THIS
        $this->subjectResults = collect(); // ADD THIS
        $this->subjectStats = []; // Keep as array for stats
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];
        $this->reset(['selectedClass', 'selectedSubject']);
        $this->subjectResults = collect(); // CHANGE from [] to collect()
    }

    public function updatedSelectedClass()
    {
        $classExists = MyClass::where('id', $this->selectedClass)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->exists();

        if (!$classExists) {
            $this->subjects = collect();
            $this->selectedClass = null;
            return;
        }

        $this->subjects = Subject::query()
            ->where('my_class_id', $this->selectedClass)
            ->orderBy('name')
            ->get();
        $this->reset(['selectedSubject']);
        $this->subjectResults = collect(); // CHANGE from [] to collect()
    }

    public function loadResults()
    {
        if (!$this->selectedClass || !$this->selectedSubject) {
            $this->dispatch('error', 'Please select both class and subject');
            return;
        }

        $classExists = MyClass::where('id', $this->selectedClass)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->exists();
        if (!$classExists) {
            $this->dispatch('error', 'Selected class is not in your current school.');
            return;
        }

        $subjectExists = Subject::where('id', $this->selectedSubject)
            ->where('school_id', auth()->user()->school_id)
            ->exists();
        if (!$subjectExists) {
            $this->dispatch('error', 'Selected subject is not in your current school.');
            return;
        }

        // Get students for this academic year
        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId)
            ->where('my_class_id', $this->selectedClass)
            ->pluck('student_record_id');

        if ($studentRecordIds->isEmpty()) {
            $this->dispatch('error', 'No students found for this class');
            $this->subjectResults = collect(); // CHANGE from [] to collect()
            return;
        }

        // Load results for this subject
        $results = Result::where('subject_id', $this->selectedSubject)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->whereIn('student_record_id', $studentRecordIds)
            ->whereHas('student.user', function ($query) {
                $query->where('school_id', auth()->user()->school_id)
                    ->whereNull('deleted_at');
            })
            ->with(['student.user' => function ($query) {
                $query->where('school_id', auth()->user()->school_id)
                    ->whereNull('deleted_at');
            }])
            ->get();

        // Calculate statistics
        if ($results->isNotEmpty()) {
            $scores = $results->pluck('total_score')->filter();
            
            $this->subjectStats = [
                'total_students' => $results->count(),
                'highest_score' => $scores->max() ?? 0,
                'lowest_score' => $scores->min() ?? 0,
                'average_score' => $scores->avg() ? round($scores->avg(), 2) : 0,
                'pass_rate' => $results->count() > 0 
                    ? round(($results->filter(fn($r) => $r->total_score >= 50)->count() / $results->count()) * 100, 2) 
                    : 0,
                'grade_distribution' => $this->calculateGradeDistribution($results),
            ];
        }

        // Sort by score descending
        $this->subjectResults = $results->sortByDesc('total_score')->values();

        // Add ranking
        $rank = 1;
        $prevScore = null;
        $studentsAtRank = 0;

        foreach ($this->subjectResults as $result) {
            if ($prevScore !== null && $result->total_score < $prevScore) {
                $rank += $studentsAtRank;
                $studentsAtRank = 1;
            } else {
                $studentsAtRank++;
            }
            $result->setAttribute('rank', $rank);
            $prevScore = $result->total_score;
        }
    }

    protected function calculateGradeDistribution($results)
    {
        $distribution = [
            'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0
        ];

        foreach ($results as $result) {
            $grade = $this->calculateGrade($result->total_score);
            $gradePrefix = substr($grade, 0, 1);
            $distribution[$gradePrefix]++;
        }

        return $distribution;
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

    public function render()
    {
        $classes = MyClass::whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->orderBy('name')
            ->get();
    
        return view('livewire.result.view.subject-results', compact('classes'))
            ->layout('layouts.result', [
                'title' => 'View Subject Results',
                'page_heading' => 'View Subject Results'
            ]);
    }
}
