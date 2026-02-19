<?php

namespace App\Livewire\Result;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Exports\ClassResultsExport;
use App\Models\{MyClass, AcademicYear, Semester, Result, StudentRecord, Subject};
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ClassResultsSpreadsheet extends Component
{
    public $academicYearId;
    public $semesterId;
    public $selectedClassId;
    public $viewType = 'termly'; // 'termly' or 'annual'
    
    public $classes;
    public $academicYears;
    public $semesters;
    
    public $spreadsheetData = [];
    public $subjects = [];
    public $students = [];
    public $statistics = [];
    
    public function mount()
    {
        $this->classes = MyClass::whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->orderBy('name')
            ->get();
        $this->academicYears = AcademicYear::query()
            ->orderBy('start_year', 'desc')
            ->get();
        
        $this->academicYearId = session('result_academic_year_id') ?? auth()->user()->school?->academic_year_id;
        $this->semesterId = session('result_semester_id') ?? auth()->user()->school?->semester_id;
        
        $this->loadSemesters();
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];
        $this->loadSemesters();
        
        if ($this->selectedClassId) {
            $this->loadSpreadsheet();
        }
    }

    public function updatedAcademicYearId()
    {
        $this->loadSemesters();
        if ($this->selectedClassId) {
            $this->loadSpreadsheet();
        }
    }

    public function updatedSemesterId()
    {
        if ($this->selectedClassId) {
            $this->loadSpreadsheet();
        }
    }

    public function updatedSelectedClassId()
    {
        if ($this->selectedClassId) {
            $this->loadSpreadsheet();
        }
    }

    public function updatedViewType()
    {
        if ($this->selectedClassId) {
            $this->loadSpreadsheet();
        }
    }

    protected function loadSemesters()
    {
        $this->semesters = $this->academicYearId 
            ? Semester::where('academic_year_id', $this->academicYearId)
                ->where('school_id', auth()->user()->school_id)
                ->orderBy('name')
                ->get() 
            : collect();
    }

    public function loadSpreadsheet()
    {
        if (!$this->selectedClassId || !$this->academicYearId) {
            return;
        }

        $classExists = MyClass::where('id', $this->selectedClassId)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->exists();
        $yearExists = AcademicYear::where('id', $this->academicYearId)
            ->where('school_id', auth()->user()->school_id)
            ->exists();

        if (!$classExists || !$yearExists) {
            $this->spreadsheetData = [];
            return;
        }

        if ($this->viewType === 'termly') {
            $this->loadTermlyResults();
        } else {
            $this->loadAnnualResults();
        }
    }

    protected function loadTermlyResults()
    {
        if (!$this->semesterId) {
            return;
        }

        // Get students in this class for this academic year
        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId)
            ->where('my_class_id', $this->selectedClassId)
            ->pluck('student_record_id');

        $this->students = StudentRecord::with(['user' => function($q) {
                $q->where('school_id', auth()->user()->school_id)
                    ->whereNull('deleted_at');
            }])
            ->whereIn('student_records.id', $studentRecordIds)
            ->whereHas('user', function($q) {
                $q->where('school_id', auth()->user()->school_id)
                    ->whereNull('deleted_at');
            })
            ->join('users', 'student_records.user_id', '=', 'users.id')
            ->where('users.school_id', auth()->user()->school_id)
            ->whereNull('users.deleted_at')
            ->orderBy('users.name')
            ->select('student_records.*')
            ->get();

        // Get subjects for this class
        $this->subjects = Subject::where('my_class_id', $this->selectedClassId)
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();

        // Get all results
        $allResults = Result::whereIn('student_record_id', $studentRecordIds)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->with('subject')
            ->get()
            ->groupBy('student_record_id');

        $this->spreadsheetData = [];
        
        foreach ($this->students as $student) {
            if (!$student->user) continue;

            $studentResults = $allResults->get($student->id, collect());
            $resultsBySubject = $studentResults->keyBy('subject_id');
            
            $subjectScores = [];
            $totalScore = 0;
            $subjectCount = 0;

            foreach ($this->subjects as $subject) {
                $result = $resultsBySubject->get($subject->id);
                $score = $result ? $result->total_score : null;
                
                $subjectScores[$subject->id] = [
                    'score' => $score,
                    'grade' => $score !== null ? $this->calculateGrade($score) : '-',
                ];

                if ($score !== null) {
                    $totalScore += $score;
                    $subjectCount++;
                }
            }

            $average = $subjectCount > 0 ? round($totalScore / $subjectCount, 2) : 0;

            $this->spreadsheetData[] = [
                'student' => $student,
                'subject_scores' => $subjectScores,
                'total_score' => $totalScore,
                'average' => $average,
                'subject_count' => $subjectCount,
                'position' => 0, // Will be calculated after
            ];
        }

        // Calculate positions
        usort($this->spreadsheetData, fn($a, $b) => $b['total_score'] <=> $a['total_score']);
        
        $rank = 1;
        $prevScore = null;
        $sameRankCount = 0;

        foreach ($this->spreadsheetData as $index => &$data) {
            if ($prevScore !== null && $data['total_score'] < $prevScore) {
                $rank += $sameRankCount;
                $sameRankCount = 1;
            } else {
                $sameRankCount++;
            }
            $data['position'] = $rank;
            $prevScore = $data['total_score'];
        }

        // Calculate statistics
        $this->calculateStatistics();
    }

    protected function loadAnnualResults()
    {
        $allSemesters = Semester::where('academic_year_id', $this->academicYearId)
            ->where('school_id', auth()->user()->school_id)
            ->get();
        
        if ($allSemesters->isEmpty()) {
            return;
        }

        // Get students
        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId)
            ->where('my_class_id', $this->selectedClassId)
            ->pluck('student_record_id');

        $this->students = StudentRecord::with(['user' => function($q) {
                $q->where('school_id', auth()->user()->school_id)
                    ->whereNull('deleted_at');
            }])
            ->whereIn('student_records.id', $studentRecordIds)
            ->whereHas('user', function($q) {
                $q->where('school_id', auth()->user()->school_id)
                    ->whereNull('deleted_at');
            })
            ->join('users', 'student_records.user_id', '=', 'users.id')
            ->where('users.school_id', auth()->user()->school_id)
            ->whereNull('users.deleted_at')
            ->orderBy('users.name')
            ->select('student_records.*')
            ->get();

        // Get subjects
        $this->subjects = Subject::where('my_class_id', $this->selectedClassId)
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();

        // Get all results for the year
        $allResults = Result::whereIn('student_record_id', $studentRecordIds)
            ->where('academic_year_id', $this->academicYearId)
            ->whereIn('semester_id', $allSemesters->pluck('id'))
            ->with(['subject', 'semester'])
            ->get()
            ->groupBy('student_record_id');

        $this->spreadsheetData = [];

        foreach ($this->students as $student) {
            if (!$student->user) continue;

            $studentResults = $allResults->get($student->id, collect());
            
            $termScores = [];
            $subjectAnnualScores = [];
            $grandTotal = 0;

            // Calculate per semester
            foreach ($allSemesters as $semester) {
                $semesterResults = $studentResults->where('semester_id', $semester->id);
                $termTotal = $semesterResults->sum('total_score');
                $termScores[$semester->id] = $termTotal;
                $grandTotal += $termTotal;
            }

            // Calculate per subject (sum across all terms)
            foreach ($this->subjects as $subject) {
                $subjectResults = $studentResults->where('subject_id', $subject->id);
                $subjectTotal = $subjectResults->sum('total_score');
                $subjectAverage = $allSemesters->count() > 0 
                    ? round($subjectTotal / $allSemesters->count(), 2) 
                    : 0;

                $subjectAnnualScores[$subject->id] = [
                    'total' => $subjectTotal,
                    'average' => $subjectAverage,
                    'grade' => $subjectAverage > 0 ? $this->calculateGrade($subjectAverage) : '-',
                ];
            }

            $maxPossible = $this->subjects->count() * 100 * $allSemesters->count();
            $annualAverage = $maxPossible > 0 ? round(($grandTotal / $maxPossible) * 100, 2) : 0;

            $this->spreadsheetData[] = [
                'student' => $student,
                'term_scores' => $termScores,
                'subject_scores' => $subjectAnnualScores,
                'grand_total' => $grandTotal,
                'annual_average' => $annualAverage,
                'position' => 0,
            ];
        }

        // Calculate positions
        usort($this->spreadsheetData, fn($a, $b) => $b['grand_total'] <=> $a['grand_total']);
        
        $rank = 1;
        $prevScore = null;
        $sameRankCount = 0;

        foreach ($this->spreadsheetData as $index => &$data) {
            if ($prevScore !== null && $data['grand_total'] < $prevScore) {
                $rank += $sameRankCount;
                $sameRankCount = 1;
            } else {
                $sameRankCount++;
            }
            $data['position'] = $rank;
            $prevScore = $data['grand_total'];
        }

        // Calculate statistics
        $this->calculateAnnualStatistics();
    }

    protected function calculateStatistics()
    {
        if (empty($this->spreadsheetData)) {
            $this->statistics = [];
            return;
        }

        $totalStudents = count($this->spreadsheetData);
        $scores = collect($this->spreadsheetData)->pluck('total_score');
        
        $this->statistics = [
            'total_students' => $totalStudents,
            'highest_score' => $scores->max(),
            'lowest_score' => $scores->min(),
            'average_score' => round($scores->average(), 2),
            'pass_rate' => round(
                (collect($this->spreadsheetData)->filter(fn($d) => $d['average'] >= 50)->count() / $totalStudents) * 100, 
                2
            ),
        ];

        // Subject statistics
        $subjectStats = [];
        foreach ($this->subjects as $subject) {
            $subjectScores = collect($this->spreadsheetData)
                ->pluck("subject_scores.{$subject->id}.score")
                ->filter(fn($s) => $s !== null);

            if ($subjectScores->isNotEmpty()) {
                $subjectStats[$subject->id] = [
                    'name' => $subject->name,
                    'highest' => $subjectScores->max(),
                    'lowest' => $subjectScores->min(),
                    'average' => round($subjectScores->average(), 2),
                ];
            }
        }

        $this->statistics['subject_stats'] = $subjectStats;
    }

    protected function calculateAnnualStatistics()
    {
        if (empty($this->spreadsheetData)) {
            $this->statistics = [];
            return;
        }

        $totalStudents = count($this->spreadsheetData);
        $scores = collect($this->spreadsheetData)->pluck('grand_total');
        
        $this->statistics = [
            'total_students' => $totalStudents,
            'highest_score' => $scores->max(),
            'lowest_score' => $scores->min(),
            'average_score' => round($scores->average(), 2),
            'pass_rate' => round(
                (collect($this->spreadsheetData)->filter(fn($d) => $d['annual_average'] >= 50)->count() / $totalStudents) * 100, 
                2
            ),
        ];
    }

    protected function calculateGrade($score)
    {
        return match(true) {
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

    public function exportToExcel()
    {
        if (!$this->selectedClassId || !$this->academicYearId) {
            session()->flash('error', 'Please select a class and academic year');
            return;
        }

        if ($this->viewType === 'termly' && !$this->semesterId) {
            session()->flash('error', 'Please select a term for termly results');
            return;
        }

        $this->loadSpreadsheet();

        $class = MyClass::where('id', $this->selectedClassId)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->first();
        $academicYear = AcademicYear::where('id', $this->academicYearId)
            ->where('school_id', auth()->user()->school_id)
            ->first();

        if (!$class || !$academicYear) {
            session()->flash('error', 'Selected class or academic year was not found');
            return;
        }

        if ($this->viewType === 'termly') {
            $semester = Semester::where('id', $this->semesterId)
                ->where('academic_year_id', $this->academicYearId)
                ->where('school_id', auth()->user()->school_id)
                ->first();
            if (!$semester) {
                session()->flash('error', 'Selected term was not found');
                return;
            }

            $export = ClassResultsExport::forTermly(
                $this->spreadsheetData,
                $this->subjects,
                $class->name,
                $semester->name,
                $academicYear->name
            );

            $filename = "{$class->name}_{$semester->name}_{$academicYear->name}.xlsx";
        } else {
            $export = ClassResultsExport::forAnnual(
                $this->spreadsheetData,
                $this->subjects,
                $this->semesters,
                $class->name,
                $academicYear->name
            );

            $filename = "{$class->name}_Annual_{$academicYear->name}.xlsx";
        }

        return Excel::download($export, str_replace(' ', '_', $filename));
    }

    public function exportToPdf()
    {
        if (!$this->selectedClassId || !$this->academicYearId) {
            session()->flash('error', 'Please select a class and academic year');
            return;
        }

        if ($this->viewType === 'termly' && !$this->semesterId) {
            session()->flash('error', 'Please select a term for termly results');
            return;
        }

        $this->loadSpreadsheet();

        $class = MyClass::where('id', $this->selectedClassId)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->first();
        $academicYear = AcademicYear::where('id', $this->academicYearId)
            ->where('school_id', auth()->user()->school_id)
            ->first();

        if (!$class || !$academicYear) {
            session()->flash('error', 'Selected class or academic year was not found');
            return;
        }

        $data = [
            'class' => $class,
            'academicYear' => $academicYear,
            'subjects' => $this->subjects,
            'semesters' => $this->semesters,
            'spreadsheetData' => $this->spreadsheetData,
            'viewType' => $this->viewType,
        ];

        if ($this->viewType === 'termly') {
            $semester = Semester::where('id', $this->semesterId)
                ->where('academic_year_id', $this->academicYearId)
                ->where('school_id', auth()->user()->school_id)
                ->first();
            if (!$semester) {
                session()->flash('error', 'Selected term was not found');
                return;
            }
            $data['semester'] = $semester;
        }

        $pdf = Pdf::loadView('livewire.result.pages.class-spreadsheet-pdf', $data)->setPaper('A4', 'landscape');

        $filename = $this->viewType === 'termly'
            ? "{$class->name}_{$data['semester']->name}_{$academicYear->name}.pdf"
            : "{$class->name}_Annual_{$academicYear->name}.pdf";

        return $pdf->download(str_replace(' ', '_', $filename));
    }

    public function render()
    {
        return view('livewire.result.class-results-spreadsheet');
    }
}
