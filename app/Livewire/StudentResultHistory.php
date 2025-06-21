<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    StudentRecord,
    AcademicYear,
    Result,
    TermReport,
    User
};

class StudentResultHistory extends Component
{
    public $studentRecord;
    public $academicYears = [];
    public $selectedAcademicYear = 'all';
    public $selectedSemester = 'all';
    public $performanceData = [];
    public $termReports = [];
    public $overallStats = [];
    public $subjectPerformance = [];
    public $loading = true;
    public $studentResults = [];
    public $studentUser;

    public function mount($student)
    {
        $this->studentRecord = StudentRecord::with('user', 'myClass')->findOrFail($student);
        
        // Set default to latest academic year and term
        $latestYear = AcademicYear::orderBy('start_year', 'desc')->first();
        $latestTerm = $latestYear->semesters()->orderBy('name', 'desc')->first();
        
        $this->selectedAcademicYear = $latestYear->id ?? 'all';
        $this->selectedSemester = $latestTerm->id ?? 'all';
        
        $this->loadStudentHistory();
    }

    public function loadStudentHistory()
{
    $this->loading = true;
    
    // Get all academic years the student has results for
    $this->academicYears = AcademicYear::whereHas('semesters', function ($query) {
        $query->whereHas('results', function ($q) {
            $q->where('student_record_id', $this->studentRecord->id);
        });
    })
    ->with('semesters')
    ->orderBy('start_year', 'desc')
    ->get();

    // Initialize performance data structure
    $this->performanceData = [];
    $totalGrandTotal = 0;
    $totalPossible = 0;
    $subjectTotals = [];

    // Get all results for the student
    $results = Result::with('subject', 'academicYear', 'semester')
        ->where('student_record_id', $this->studentRecord->id)
        ->get();

    // Organize results by academic year and semester
    foreach ($results as $result) {
        $yearId = $result->academic_year_id;
        $semesterId = $result->semester_id;
        
        if (!isset($this->performanceData[$yearId])) {
            $this->performanceData[$yearId] = [
                'year' => $result->academicYear,
                'semesters' => [],
                'year_total' => 0,
                'year_possible' => 0
            ];
        }
        
        if (!isset($this->performanceData[$yearId]['semesters'][$semesterId])) {
            $this->performanceData[$yearId]['semesters'][$semesterId] = [
                'semester' => $result->semester,
                'results' => [],
                'term_report' => null,
                'semester_total' => 0,
                'semester_possible' => 0
            ];
        }
        
        $totalScore = ($result->ca1_score ?? 0) + ($result->ca2_score ?? 0) + 
                      ($result->ca3_score ?? 0) + ($result->ca4_score ?? 0) + 
                      ($result->exam_score ?? 0);
        
        $this->performanceData[$yearId]['semesters'][$semesterId]['results'][] = [
            'subject' => $result->subject,
            'scores' => [
                'ca1' => $result->ca1_score,
                'ca2' => $result->ca2_score,
                'ca3' => $result->ca3_score,
                'ca4' => $result->ca4_score,
                'exam' => $result->exam_score
            ],
            'total' => $totalScore,
            'grade' => $this->calculateGrade($totalScore),
            'comment' => $result->teacher_comment
        ];
        
        $this->performanceData[$yearId]['semesters'][$semesterId]['semester_total'] += $totalScore;
        $this->performanceData[$yearId]['semesters'][$semesterId]['semester_possible'] += 100;
        
        $this->performanceData[$yearId]['year_total'] += $totalScore;
        $this->performanceData[$yearId]['year_possible'] += 100;
        
        // Track subject performance across all years
        $subjectId = $result->subject_id;
        if (!isset($subjectTotals[$subjectId])) {
            $subjectTotals[$subjectId] = [
                'subject' => $result->subject,
                'total' => 0,
                'count' => 0
            ];
        }
        $subjectTotals[$subjectId]['total'] += $totalScore;
        $subjectTotals[$subjectId]['count']++;
        
        $totalGrandTotal += $totalScore;
        $totalPossible += 100;
    }
    
    // Calculate subject averages
    foreach ($subjectTotals as $subjectId => $data) {
        $this->subjectPerformance[] = [
            'subject' => $data['subject'],
            'average' => $data['total'] / $data['count']
        ];
    }
    
    // Sort subjects by average
    usort($this->subjectPerformance, function($a, $b) {
        return $b['average'] <=> $a['average'];
    });
    
    // Load term reports
    $termReports = TermReport::where('student_record_id', $this->studentRecord->id)
        ->with('academicYear', 'semester')
        ->get();
        
    foreach ($termReports as $report) {
        $yearId = $report->academic_year_id;
        $semesterId = $report->semester_id;
        
        if (isset($this->performanceData[$yearId]['semesters'][$semesterId])) {
            $this->performanceData[$yearId]['semesters'][$semesterId]['term_report'] = $report;
        }
    }
    
// Sort subjects by average
usort($this->subjectPerformance, function($a, $b) {
    return $b['average'] <=> $a['average'];
});

// Calculate overall statistics
$worstSubject = count($this->subjectPerformance) > 0 ? end($this->subjectPerformance) : null;

$this->overallStats = [
    'total_terms' => $results->groupBy('semester_id')->count(),
    'average_score' => $totalPossible > 0 ? round($totalGrandTotal / $totalPossible * 100, 2) : 0,
    'best_subject' => count($this->subjectPerformance) > 0 ? $this->subjectPerformance[0]['subject']->name : 'N/A',
    'best_subject_avg' => count($this->subjectPerformance) > 0 ? round($this->subjectPerformance[0]['average'], 2) : 0,
    'worst_subject' => $worstSubject ? $worstSubject['subject']->name : 'N/A',
    'worst_subject_avg' => $worstSubject ? round($worstSubject['average'], 2) : 0,
    'overall_position' => 'N/A'
];

    // // Calculate overall statistics
    // $this->overallStats = [
    //     'total_terms' => $results->groupBy('semester_id')->count(),
    //     'average_score' => $totalPossible > 0 ? round($totalGrandTotal / $totalPossible * 100, 2) : 0,
    //     'best_subject' => count($this->subjectPerformance) > 0 ? $this->subjectPerformance[0]['subject']->name : 'N/A',
    //     'best_subject_avg' => count($this->subjectPerformance) > 0 ? round($this->subjectPerformance[0]['average'], 2) : 0,
    //     'overall_position' => 'N/A' // Would require additional logic
    // ];
    
    $this->loading = false;
}
    public function calculateGrade($score)
    {
        if ($score >= 75) return 'A1';
        if ($score >= 70) return 'B2';
        if ($score >= 65) return 'B3';
        if ($score >= 60) return 'C4';
        if ($score >= 55) return 'C5';
        if ($score >= 50) return 'C6';
        if ($score >= 45) return 'D7';
        if ($score >= 40) return 'E8';
        return 'F9';
    }
    
    public function updatedSelectedAcademicYear()
    {
        $this->selectedSemester = 'all';
    }



// StudentResultHistory.php
public function render()
{
    return view('livewire.student-result-history', [
        'filteredData' => $this->getFilteredData()
    ])->layout('layouts.app', [ // Add this line to specify the layout
        'breadcrumbs' => [
            ['href' => route('dashboard'), 'text' => 'Dashboard'],
            ['href' => route('result'), 'text' => 'Results', 'active' => true],
        ],
        'title' => 'Results',
        'page_heading' => 'Student Results History',
    ]);
}

// public function render()
// {
//     return view('livewire.student-result-history', [
//         'filteredData' => $this->getFilteredData()
//     ]);
// }    
    protected function getFilteredData()
    {
        if ($this->loading) return [];
        
        $filtered = [];
        
        foreach ($this->performanceData as $yearId => $yearData) {
            if ($this->selectedAcademicYear !== 'all' && $yearId != $this->selectedAcademicYear) {
                continue;
            }
            
            $yearEntry = [
                'year' => $yearData['year'],
                'semesters' => [],
                'year_total' => $yearData['year_total'],
                'year_possible' => $yearData['year_possible'],
                'year_avg' => $yearData['year_possible'] > 0 ? 
                    round($yearData['year_total'] / $yearData['year_possible'] * 100, 2) : 0
            ];
            
            foreach ($yearData['semesters'] as $semesterId => $semesterData) {
                if ($this->selectedSemester !== 'all' && $semesterId != $this->selectedSemester) {
                    continue;
                }
                
                $yearEntry['semesters'][$semesterId] = [
                    'semester' => $semesterData['semester'],
                    'results' => $semesterData['results'],
                    'term_report' => $semesterData['term_report'],
                    'semester_total' => $semesterData['semester_total'],
                    'semester_possible' => $semesterData['semester_possible'],
                    'semester_avg' => $semesterData['semester_possible'] > 0 ? 
                        round($semesterData['semester_total'] / $semesterData['semester_possible'] * 100, 2) : 0
                ];
            }
            
            $filtered[$yearId] = $yearEntry;
        }
        
        return $filtered;
    }

    public function resetFilters()
{
    $this->selectedAcademicYear = 'all';
    $this->selectedSemester = 'all';
    $this->loadStudentHistory();
}

// Update the mount method to set default to latest result

}