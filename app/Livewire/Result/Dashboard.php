<?php

namespace App\Livewire\Result;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{Result, StudentRecord, Subject, MyClass};
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $academicYearId;
    public $semesterId;
    public $stats = [];

    public function mount()
    {
        $this->academicYearId = session('result_academic_year_id');
        $this->semesterId = session('result_semester_id');
        $this->loadStats();
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];
        $this->loadStats();
    }

    protected function loadStats()
    {
        if (!$this->academicYearId || !$this->semesterId) {
            $this->stats = [];
            return;
        }
    
        // Get student records for this academic year
        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId)
            ->pluck('student_record_id');
    
        $totalStudents = $studentRecordIds->count();
    
        $results = Result::where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->whereIn('student_record_id', $studentRecordIds)
            ->get();
    
        $studentsWithResults = $results->pluck('student_record_id')->unique()->count();
        $totalResults = $results->count();
        $avgScore = $results->avg('total_score') ?? 0;
    
        $recentlyUploaded = Result::where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->whereIn('student_record_id', $studentRecordIds)
            // Fix: Properly eager load the student with user relationship
            ->with([
                'studentRecord.user', // Use studentRecord instead of student, and load user
                'subject'
            ])
            ->latest()
            ->take(5)
            ->get();
    
        $this->stats = [
            'total_students' => $totalStudents,
            'students_with_results' => $studentsWithResults,
            'pending_students' => $totalStudents - $studentsWithResults,
            'total_results' => $totalResults,
            'average_score' => round($avgScore, 2),
            'completion_rate' => $totalStudents > 0 
                ? round(($studentsWithResults / $totalStudents) * 100, 2) 
                : 0,
            'recently_uploaded' => $recentlyUploaded,
        ];
    }
    public function render()
    {
        return view('livewire.result.dashboard')
            ->layout('layouts.new', [
                'title' => 'Results Dashboard',
                'page_heading' => 'Results Dashboard'
            ]);
    }
}