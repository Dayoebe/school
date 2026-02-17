<?php

namespace App\Livewire\Result\View;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{StudentRecord, MyClass, Section, Result, Subject};
use Illuminate\Support\Facades\DB;

class ClassResults extends Component
{
    public $academicYearId;
    public $semesterId;
    public $selectedClass;
    public $selectedSection;
    public $classResults; // REMOVE = []
    public $subjects; // REMOVE = []

    public function mount()
    {
        $this->academicYearId = session('result_academic_year_id');
        $this->semesterId = session('result_semester_id');
        $this->classResults = collect(); // ADD THIS
        $this->subjects = collect(); // ADD THIS
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];
        $this->reset(['selectedClass', 'selectedSection']);
        $this->classResults = collect(); // CHANGE from [] to collect()
    }

    public function loadResults()
    {
        if (!$this->selectedClass) {
            $this->dispatch('error', 'Please select a class');
            return;
        }
    
        // Get students for this academic year
        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId)
            ->where('my_class_id', $this->selectedClass)
            ->when($this->selectedSection, fn($q) => $q->where('section_id', $this->selectedSection))
            ->pluck('student_record_id');
    
        if ($studentRecordIds->isEmpty()) {
            $this->dispatch('error', 'No students found for this class in current academic year');
            $this->classResults = collect();
            return;
        }
    
        // Get subjects for this class
        $this->subjects = Subject::where('my_class_id', $this->selectedClass)
            ->orderBy('name')
            ->get();
    
        // Load students with their results (filter out soft-deleted users)
        $students = StudentRecord::whereIn('student_records.id', $studentRecordIds) // CHANGE HERE
            ->with([
                'user' => function ($query) {
                    $query->whereNull('deleted_at');
                },
                'results' => function ($q) {
                    $q->where('academic_year_id', $this->academicYearId)
                        ->where('semester_id', $this->semesterId)
                        ->with('subject');
                }
            ])
            ->whereHas('user', fn($q) => $q->whereNull('deleted_at'))
            ->orderByName()
            ->get();
    
        // Process results for each student
        foreach ($students as $student) {
            // Skip if user is null (soft-deleted)
            if (!$student->user) {
                continue;
            }
            
            $totalScore = 0;
            $subjectResults = [];
    
            foreach ($this->subjects as $subject) {
                $result = $student->results->firstWhere('subject_id', $subject->id);
                if ($result) {
                    $subjectResults[$subject->id] = [
                        'total_score' => $result->total_score,
                        'grade' => $this->calculateGrade($result->total_score),
                    ];
                    $totalScore += $result->total_score;
                }
            }
    
            $student->setAttribute('subject_results', $subjectResults);
            $student->setAttribute('total_score', $totalScore);
            $student->setAttribute('average_score', 
                count($subjectResults) > 0 ? round($totalScore / count($subjectResults), 2) : 0
            );
        }
    
        // Filter out any students without users
        $students = $students->filter(fn($student) => $student->user);
    
        // Calculate positions
        $rankedStudents = $students->sortByDesc('total_score')->values();
        $rank = 1;
        $prevScore = null;
        $studentsAtRank = 0;
    
        foreach ($rankedStudents as $student) {
            if ($prevScore !== null && $student->total_score < $prevScore) {
                $rank += $studentsAtRank;
                $studentsAtRank = 1;
            } else {
                $studentsAtRank++;
            }
            $student->setAttribute('position', $rank);
            $prevScore = $student->total_score;
        }
    
        $this->classResults = $students->sortBy('position');
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
        $classes = MyClass::orderBy('name')->get();
        $sections = Section::when($this->selectedClass, fn($q) => $q->where('my_class_id', $this->selectedClass))->get();

        return view('livewire.result.view.class-results', compact('classes', 'sections'))
            ->layout('layouts.new', [
                'title' => 'View Class Results',
                'page_heading' => 'View Class Results'
            ]);
    }
}