<?php

namespace App\Livewire\Result;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{StudentRecord, Result, MyClass, Subject, Semester};
use Illuminate\Support\Facades\DB;

class PerformanceAnalytics extends Component
{
    public $academicYearId;
    public $semesterId;
    public $selectedClassId;
    public $selectedStudentId;
    public $viewMode = 'class'; // class, student, subject
    
    public $classes;
    public $students = [];
    public $subjects = [];
    
    // Analytics Data
    public $trendData = [];
    public $radarData = [];
    public $comparisonData = [];
    public $atRiskStudents = [];
    public $performanceDistribution = [];
    public $subjectAnalysis = [];
    public $insights = [];

    public function mount()
    {
        $this->classes = MyClass::whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->orderBy('name')
            ->get();
        $this->academicYearId = session('result_academic_year_id') ?? auth()->user()->school?->academic_year_id;
        $this->semesterId = session('result_semester_id') ?? auth()->user()->school?->semester_id;
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];
        $this->loadAnalytics();
    }

    public function updatedSelectedClassId()
    {
        $this->loadStudents();
        $this->selectedStudentId = null;
        $this->loadAnalytics();
    }

    public function updatedSelectedStudentId()
    {
        $this->loadAnalytics();
    }

    public function updatedViewMode()
    {
        $this->loadAnalytics();
    }

    protected function loadStudents()
    {
        if (!$this->selectedClassId || !$this->academicYearId) {
            $this->students = [];
            return;
        }

        $classExists = MyClass::where('id', $this->selectedClassId)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->exists();
        if (!$classExists) {
            $this->students = [];
            return;
        }

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
    }

    public function loadAnalytics()
    {
        if (!$this->academicYearId) {
            return;
        }

        if ($this->viewMode === 'student' && $this->selectedStudentId) {
            $this->loadStudentAnalytics();
        } elseif ($this->viewMode === 'class' && $this->selectedClassId) {
            $this->loadClassAnalytics();
        } elseif ($this->viewMode === 'subject' && $this->selectedClassId) {
            $this->loadSubjectAnalytics();
        }
    }

    protected function loadStudentAnalytics()
    {
        $student = StudentRecord::whereHas('user', function ($query) {
            $query->where('school_id', auth()->user()->school_id)
                ->whereNull('deleted_at');
        })->find($this->selectedStudentId);
        if (!$student) return;

        // Load all semesters for trend analysis
        $semesters = Semester::where('academic_year_id', $this->academicYearId)
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();

        // Get all results for this student
        $allResults = Result::where('student_record_id', $this->selectedStudentId)
            ->where('academic_year_id', $this->academicYearId)
            ->with(['subject', 'semester'])
            ->get();

        // Trend Data (Performance over time)
        $this->trendData = $this->buildTrendData($allResults, $semesters);

        // Radar Data (Subject strengths/weaknesses)
        $this->radarData = $this->buildRadarData($allResults);

        // Comparison with Class Average
        $this->comparisonData = $this->buildComparisonData($student, $allResults);

        // Generate Insights
        $this->insights = $this->generateStudentInsights($allResults, $semesters);
    }

    protected function loadClassAnalytics()
    {
        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId)
            ->where('my_class_id', $this->selectedClassId)
            ->pluck('student_record_id');

        $this->subjects = Subject::where('my_class_id', $this->selectedClassId)
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();

        // Get all results for the class
        $results = Result::whereIn('student_record_id', $studentRecordIds)
            ->where('academic_year_id', $this->academicYearId)
            ->when($this->semesterId, fn($q) => $q->where('semester_id', $this->semesterId))
            ->with(['studentRecord.user', 'subject'])
            ->get();

        // Performance Distribution
        $this->performanceDistribution = $this->buildPerformanceDistribution($results);

        // At-Risk Students
        $this->atRiskStudents = $this->identifyAtRiskStudents($results, $studentRecordIds);

        // Subject Analysis
        $this->subjectAnalysis = $this->buildSubjectAnalysis($results);

        // Class Insights
        $this->insights = $this->generateClassInsights($results);
    }

    protected function loadSubjectAnalytics()
    {
        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId)
            ->where('my_class_id', $this->selectedClassId)
            ->pluck('student_record_id');

        $this->subjects = Subject::where('my_class_id', $this->selectedClassId)
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();

        $semesters = Semester::where('academic_year_id', $this->academicYearId)
            ->where('school_id', auth()->user()->school_id)
            ->get();

        $results = Result::whereIn('student_record_id', $studentRecordIds)
            ->where('academic_year_id', $this->academicYearId)
            ->with(['subject', 'semester', 'studentRecord.user'])
            ->get();

        // Subject Performance Trends
        $this->subjectAnalysis = $this->buildDetailedSubjectAnalysis($results, $semesters);
    }

    protected function buildTrendData($results, $semesters)
    {
        $trendData = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Average Score',
                    'data' => [],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                ]
            ]
        ];

        foreach ($semesters as $semester) {
            $semesterResults = $results->where('semester_id', $semester->id);
            
            if ($semesterResults->isNotEmpty()) {
                $average = $semesterResults->avg('total_score');
                $trendData['labels'][] = $semester->name;
                $trendData['datasets'][0]['data'][] = round($average, 2);
            }
        }

        return $trendData;
    }

    protected function buildRadarData($results)
    {
        $currentResults = $results->when($this->semesterId, 
            fn($q) => $q->where('semester_id', $this->semesterId),
            fn($q) => $q
        );

        $subjectScores = $currentResults->groupBy('subject_id')->map(function($subjectResults) {
            return [
                'subject' => $subjectResults->first()->subject->name,
                'score' => $subjectResults->avg('total_score'),
            ];
        })->values();

        return [
            'labels' => $subjectScores->pluck('subject')->toArray(),
            'datasets' => [
                [
                    'label' => 'Student Performance',
                    'data' => $subjectScores->pluck('score')->map(fn($s) => round($s, 2))->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                ]
            ]
        ];
    }

    protected function buildComparisonData($student, $studentResults)
    {
        // Get class average for comparison
        $classmates = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId)
            ->where('my_class_id', $student->my_class_id)
            ->pluck('student_record_id');

        $classResults = Result::whereIn('student_record_id', $classmates)
            ->where('academic_year_id', $this->academicYearId)
            ->when($this->semesterId, fn($q) => $q->where('semester_id', $this->semesterId))
            ->get();

        $studentAvg = $studentResults->avg('total_score');
        $classAvg = $classResults->avg('total_score');

        return [
            'labels' => ['Student', 'Class Average'],
            'datasets' => [
                [
                    'label' => 'Average Score',
                    'data' => [round($studentAvg, 2), round($classAvg, 2)],
                    'backgroundColor' => ['rgba(59, 130, 246, 0.8)', 'rgba(156, 163, 175, 0.8)'],
                ]
            ]
        ];
    }

    protected function buildPerformanceDistribution($results)
    {
        $grouped = $results->groupBy('student_record_id')->map(function($studentResults) {
            return $studentResults->avg('total_score');
        });

        $ranges = [
            '90-100' => $grouped->filter(fn($avg) => $avg >= 90)->count(),
            '80-89' => $grouped->filter(fn($avg) => $avg >= 80 && $avg < 90)->count(),
            '70-79' => $grouped->filter(fn($avg) => $avg >= 70 && $avg < 80)->count(),
            '60-69' => $grouped->filter(fn($avg) => $avg >= 60 && $avg < 70)->count(),
            '50-59' => $grouped->filter(fn($avg) => $avg >= 50 && $avg < 60)->count(),
            'Below 50' => $grouped->filter(fn($avg) => $avg < 50)->count(),
        ];

        return [
            'labels' => array_keys($ranges),
            'datasets' => [
                [
                    'label' => 'Number of Students',
                    'data' => array_values($ranges),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(127, 29, 29, 0.8)',
                    ],
                ]
            ]
        ];
    }

    protected function identifyAtRiskStudents($results, $studentRecordIds)
    {
        $atRisk = [];

        foreach ($studentRecordIds as $studentId) {
            $studentResults = $results->where('student_record_id', $studentId);
            
            if ($studentResults->isEmpty()) continue;

            $average = $studentResults->avg('total_score');
            $failingSubjects = $studentResults->where('total_score', '<', 40)->count();

            if ($average < 50 || $failingSubjects >= 3) {
                $student = StudentRecord::with('user')->find($studentId);
                if (!$student || !$student->user || $student->user->school_id !== auth()->user()->school_id) {
                    continue;
                }
                
                $atRisk[] = [
                    'student' => $student,
                    'average' => round($average, 2),
                    'failing_subjects' => $failingSubjects,
                    'total_subjects' => $studentResults->count(),
                    'risk_level' => $this->calculateRiskLevel($average, $failingSubjects),
                ];
            }
        }

        return collect($atRisk)->sortBy('average')->values()->toArray();
    }

    protected function calculateRiskLevel($average, $failingCount)
    {
        if ($average < 40 || $failingCount >= 5) return 'critical';
        if ($average < 50 || $failingCount >= 3) return 'high';
        return 'moderate';
    }

    protected function buildSubjectAnalysis($results)
    {
        return $results->groupBy('subject_id')->map(function($subjectResults) {
            $scores = $subjectResults->pluck('total_score');
            
            return [
                'subject' => $subjectResults->first()->subject,
                'average' => round($scores->avg(), 2),
                'highest' => $scores->max(),
                'lowest' => $scores->min(),
                'pass_rate' => round(($subjectResults->where('total_score', '>=', 50)->count() / $subjectResults->count()) * 100, 2),
                'total_students' => $subjectResults->count(),
            ];
        })->sortByDesc('average')->values()->toArray();
    }

    protected function buildDetailedSubjectAnalysis($results, $semesters)
    {
        $analysis = [];

        foreach ($this->subjects as $subject) {
            $subjectData = [
                'subject' => $subject,
                'trend' => [
                    'labels' => [],
                    'data' => [],
                ],
                'distribution' => [],
            ];

            foreach ($semesters as $semester) {
                $semesterResults = $results->where('subject_id', $subject->id)
                    ->where('semester_id', $semester->id);

                if ($semesterResults->isNotEmpty()) {
                    $subjectData['trend']['labels'][] = $semester->name;
                    $subjectData['trend']['data'][] = round($semesterResults->avg('total_score'), 2);
                }
            }

            $analysis[] = $subjectData;
        }

        return $analysis;
    }

    protected function generateStudentInsights($results, $semesters)
    {
        $insights = [];

        // Overall Performance
        $overallAvg = $results->avg('total_score');
        $insights[] = [
            'type' => 'info',
            'icon' => 'fa-chart-line',
            'title' => 'Overall Performance',
            'message' => "Average score: " . round($overallAvg, 2) . "% - " . $this->getPerformanceLabel($overallAvg),
        ];

        // Best Subject
        $bestSubject = $results->sortByDesc('total_score')->first();
        if ($bestSubject) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'fa-star',
                'title' => 'Strongest Subject',
                'message' => "{$bestSubject->subject->name} ({$bestSubject->total_score}%)",
            ];
        }

        // Weakest Subject
        $weakestSubject = $results->sortBy('total_score')->first();
        if ($weakestSubject && $weakestSubject->total_score < 50) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'fa-exclamation-triangle',
                'title' => 'Needs Improvement',
                'message' => "{$weakestSubject->subject->name} ({$weakestSubject->total_score}%) - Requires attention",
            ];
        }

        // Trend Analysis
        if ($semesters->count() > 1) {
            $firstTerm = $results->where('semester_id', $semesters->first()->id)->avg('total_score');
            $lastTerm = $results->where('semester_id', $semesters->last()->id)->avg('total_score');
            $improvement = $lastTerm - $firstTerm;

            if (abs($improvement) > 5) {
                $insights[] = [
                    'type' => $improvement > 0 ? 'success' : 'danger',
                    'icon' => $improvement > 0 ? 'fa-arrow-up' : 'fa-arrow-down',
                    'title' => 'Performance Trend',
                    'message' => ($improvement > 0 ? 'Improved' : 'Declined') . " by " . abs(round($improvement, 2)) . "% points",
                ];
            }
        }

        return $insights;
    }

    protected function generateClassInsights($results)
    {
        $insights = [];
        $studentCount = $results->pluck('student_record_id')->unique()->count();
        $classAvg = $results->avg('total_score');

        $insights[] = [
            'type' => 'info',
            'icon' => 'fa-users',
            'title' => 'Class Overview',
            'message' => "{$studentCount} students with " . round($classAvg, 2) . "% class average",
        ];

        // Pass Rate
        $passing = $results->groupBy('student_record_id')->filter(function($studentResults) {
            return $studentResults->avg('total_score') >= 50;
        })->count();

        $passRate = $studentCount > 0 ? ($passing / $studentCount) * 100 : 0;

        $insights[] = [
            'type' => $passRate >= 80 ? 'success' : ($passRate >= 60 ? 'warning' : 'danger'),
            'icon' => 'fa-chart-pie',
            'title' => 'Pass Rate',
            'message' => round($passRate, 2) . "% ({$passing}/{$studentCount} students)",
        ];

        return $insights;
    }

    protected function getPerformanceLabel($score)
    {
        return match(true) {
            $score >= 80 => 'Excellent',
            $score >= 70 => 'Very Good',
            $score >= 60 => 'Good',
            $score >= 50 => 'Average',
            default => 'Needs Improvement',
        };
    }

    public function render()
    {
        return view('livewire.result.performance-analytics');
    }
}
