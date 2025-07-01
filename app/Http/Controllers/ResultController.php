<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\StudentRecord;
use App\Models\TermReport;
use App\Models\Result;
use App\Models\MyClass;
use Barryvdh\DomPDF\Facade\Pdf;

class ResultController extends Controller
{

    public function viewResults(Request $request)
    {
        // Initialize base data
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $classes = MyClass::orderBy('name')->get();
        
        // Get filter parameters
        $academicYearId = $request->input('academicYearId');
        $semesterId = $request->input('semesterId');
        $classId = $request->input('classId');
        $subjectId = $request->input('subjectId');
        $mode = $request->input('mode', 'subject');
        
        // Initialize all variables with default values
        $subjectResults = collect();
        $classResults = collect();
        $subjects = collect();
        $semesters = collect();
        $selectedSubject = null;
        $selectedClass = null;
        $academicYearName = 'Not Selected';
        $semesterName = 'Not Selected';
        
        // Load dependent data
        if ($academicYearId) {
            $semesters = Semester::where('academic_year_id', $academicYearId)->get();
        }
        
        if ($classId) {
            $subjects = Subject::where('my_class_id', $classId)->get();
        }
        
        // Process results when all filters are selected
        if ($academicYearId && $semesterId && $classId) {
            $academicYear = AcademicYear::find($academicYearId);
            $semester = Semester::find($semesterId);
            $class = MyClass::find($classId);
            
            $academicYearName = $academicYear->name ?? 'Unknown Academic Year';
            $semesterName = $semester->name ?? 'Unknown Term';
            $selectedClass = $class;
            
            if ($mode === 'subject' && $subjectId) {
                // SUBJECT VIEW LOGIC
                $selectedSubject = Subject::find($subjectId);
                
                $subjectResults = Result::with([
                    'student.user', 
                    'subject'
                ])
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->whereHas('student', function($q) use ($classId) {
                    $q->where('my_class_id', $classId)
                      ->where('is_graduated', false)
                      ->whereHas('user', function($q) {
                          $q->whereNull('deleted_at');
                      });
                })
                ->get()
                ->each(function ($result) {
                    // Calculate total if not set
                    if (!isset($result->total_score)) {
                        $result->setAttribute('total_score',
                            ($result->ca1_score ?? 0) + 
                            ($result->ca2_score ?? 0) + 
                            ($result->ca3_score ?? 0) + 
                            ($result->ca4_score ?? 0) + 
                            ($result->exam_score ?? 0)
                        );
                    }
                    
                    // Calculate grade if not set
                    if (!isset($result->grade)) {
                        $result->setAttribute('grade', $this->calculateGrade($result->total_score));
                    }
                });
                
            } else {
                // CLASS VIEW LOGIC
                $students = StudentRecord::with([
                    'user',
                    'results' => function($query) use ($academicYearId, $semesterId) {
                        $query->where('academic_year_id', $academicYearId)
                              ->where('semester_id', $semesterId)
                              ->with('subject');
                    }
                ])
                ->where('my_class_id', $classId)
                ->where('is_graduated', false)
                ->whereHas('user', function($q) {
                    $q->whereNull('deleted_at');
                })
                ->get()
                ->each(function ($student) {
                    // Calculate totals and averages
                    $student->setAttribute('total_score', $student->results->sum('total_score'));
                    $student->setAttribute('average_score', $student->results->avg('total_score'));
                    
                    // Ensure all results have grades
                    $student->results->each(function ($result) {
                        if (!isset($result->grade) && isset($result->total_score)) {
                            $result->setAttribute('grade', $this->calculateGrade($result->total_score));
                        }
                    });
                })
                ->sortByDesc('total_score')
                ->values();
                
                // Calculate positions with tie handling
                $position = 1;
                $prevScore = null;
                $actualPosition = 1;
                
                foreach ($students as $student) {
                    if ($prevScore !== null && $student->total_score == $prevScore) {
                        $student->setAttribute('position', $position);
                    } else {
                        $student->setAttribute('position', $actualPosition);
                        $position = $actualPosition;
                    }
                    $prevScore = $student->total_score;
                    $actualPosition++;
                }
                
                $classResults = $students;
            }
        }
        
        return view('pages.result.view-result', compact(
            'academicYears', 'classes',
            'academicYearId', 'semesterId', 'classId', 'subjectId', 'mode',
            'subjectResults', 'classResults',
            'subjects', 'semesters',
            'selectedSubject', 'selectedClass',
            'academicYearName', 'semesterName'
        ));
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

    public function getSemesters(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $semesters = Semester::where('academic_year_id', $academicYearId)->get();
        return response()->json($semesters);
    }

    public function getSubjects(Request $request)
    {
        $classId = $request->input('class_id');
        $subjects = Subject::where('my_class_id', $classId)->get();
        return response()->json($subjects);
    }

    public function print(Request $request, $studentId)
    {
        $academicYearId = $request->academicYearId ?? $request->input('academicYearId');
        $semesterId = $request->semesterId ?? $request->input('semesterId');
        
        $studentRecord = StudentRecord::with([
                'user',
                'myClass',
                'section',
                'results' => function ($query) use ($academicYearId, $semesterId) {
                    $query->where('academic_year_id', $academicYearId)
                        ->where('semester_id', $semesterId)
                        ->with('subject');
                }
            ])
            ->findOrFail($studentId);

        $data = $this->prepareReportData($studentRecord, $academicYearId, $semesterId);

        return view('pages.result.print', $data);
    }
    
    public function printClassResults($academicYearId, $semesterId, $classId)
    {
        $academicYear = AcademicYear::findOrFail($academicYearId);
        $semester = Semester::findOrFail($semesterId);
        $class = MyClass::findOrFail($classId);

        $students = StudentRecord::with([
                'user',
                'myClass',
                'section',
                'results' => function ($query) use ($academicYearId, $semesterId) {
                    $query->where('academic_year_id', $academicYearId)
                        ->where('semester_id', $semesterId)
                        ->with('subject');
                }
            ])
            ->where('my_class_id', $classId)
            ->where('is_graduated', false)
            ->whereHas('user', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->get();

        $studentsData = [];
        
        foreach ($students as $student) {
            $studentsData[] = $this->prepareReportData($student, $academicYearId, $semesterId);
        }

        return view('pages.result.print-class', [
            'academicYear' => $academicYear,
            'semester' => $semester,
            'class' => $class,
            'studentsData' => $studentsData,
        ]);
    }

    protected function prepareReportData($studentRecord, $academicYearId, $semesterId)
    {
        // Get the raw results for this student
        $rawResults = $studentRecord->results->filter(function ($result) use ($academicYearId, $semesterId) {
            return $result->academic_year_id == $academicYearId && 
                   $result->semester_id == $semesterId;
        });

        // Get class results for statistics
        $classResults = Result::with('subject')
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->whereIn(
                'student_record_id',
                StudentRecord::where('my_class_id', $studentRecord->my_class_id)->pluck('id')
            )
            ->get()
            ->groupBy('subject_id');

        $subjectStats = [];
        foreach ($classResults as $subjectId => $results) {
            $subjectStats[$subjectId] = [
                'highest' => (int)$results->max('total_score'),
                'lowest' => (int)$results->min('total_score')
            ];
        }

        // Process results
        $results = $rawResults->keyBy('subject_id')->map(function ($result) {
            $ca1 = (int) $result->ca1_score;
            $ca2 = (int) $result->ca2_score;
            $ca3 = (int) $result->ca3_score;
            $ca4 = (int) $result->ca4_score;
            $exam = (int) $result->exam_score;
            $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;

            $grade = match (true) {
                $total >= 75 => 'A1',
                $total >= 70 => 'B2',
                $total >= 65 => 'B3',
                $total >= 60 => 'C4',
                $total >= 55 => 'C5',
                $total >= 50 => 'C6',
                $total >= 45 => 'D7',
                $total >= 40 => 'E8',
                default => 'F9',
            };

            $comment = match ($grade) {
                'A1' => 'Distinction',
                'B2' => 'Very good',
                'B3' => 'Good',
                'C4' => 'Credit',
                'C5' => 'Credit',
                'C6' => 'Credit',
                'D7' => 'Pass',
                'E8' => 'Pass',
                default => 'Fail',
            };

            return [
                'ca1_score' => $ca1,
                'ca2_score' => $ca2,
                'ca3_score' => $ca3,
                'ca4_score' => $ca4,
                'exam_score' => $exam,
                'total_score' => $total,
                'grade' => $grade,
                'comment' => $result->teacher_comment ?: $comment,
            ];
        });

        // Get subjects from eager-loaded relationships
        $subjectIds = $rawResults->pluck('subject_id')->unique()->toArray();
        $subjects = Subject::whereIn('id', $subjectIds)
            ->orderBy('name')
            ->get();

        $totalSubjects = $subjects->count();
        $maxTotalScore = $totalSubjects * 100;

        $grandTotal = $rawResults->sum('total_score');
        $grandTotalTest = $rawResults->sum(function($result) {
            return $result->ca1_score + $result->ca2_score + $result->ca3_score + $result->ca4_score;
        });
        $grandTotalExam = $rawResults->sum('exam_score');
        $percentage = $totalSubjects ? round($grandTotal / $totalSubjects, 2) : 0;
        $principalComment = 'Keep up the good work';

        $academicYearName = optional(AcademicYear::find($academicYearId))->name ?? 'Unknown Academic Year';
        $semesterName = Semester::find($semesterId)->name ?? 'Unknown Semester';

        $totalStudents = StudentRecord::where('my_class_id', $studentRecord->myClass->id)->count();
        $classPosition = $this->calculatePosition($studentRecord, $academicYearId, $semesterId);

        $totalScore = 0;
        $subjectsPassed = 0;

        foreach ($subjects as $subject) {
            $result = $results[$subject->id] ?? ['total_score' => 0, 'grade' => 'F9'];
            $totalScore += $result['total_score'];

            if ($result['total_score'] >= 40) {
                $subjectsPassed++;
            }
        }

        $termReport = TermReport::firstOrCreate([
            'student_record_id' => $studentRecord->id,
            'academic_year_id' => $academicYearId,
            'semester_id' => $semesterId,
        ]);
        $termReport->update([
            'principal_comment' => $principalComment,
            'total_score' => $grandTotal,
            'percentage' => $percentage,
            'position' => $classPosition,
        ]);

        return [
            'studentRecord' => $studentRecord,
            'subjects' => $subjects,
            'results' => $results,
            'grandTotal' => $grandTotal,
            'grandTotalTest' => $grandTotalTest,
            'grandTotalExam' => $grandTotalExam,
            'subjectsPassed' => $subjectsPassed,
            'totalScore' => $totalScore,    
            'subjectStats' => $subjectStats,
            'percentage' => $percentage,
            'principalComment' => $principalComment,
            'totalStudents' => $totalStudents,
            'classPosition' => $classPosition,
            'academicYearId' => $academicYearId,
            'semesterId' => $semesterId,
            'academicYearName' => $academicYearName,
            'semesterName' => $semesterName,
            'termReport' => $termReport,
            'maxTotalScore' => $maxTotalScore,
            'totalSubjects' => $totalSubjects,
        ];
    }

    protected function calculatePosition($studentRecord, $academicYearId, $semesterId)
    {
        if (!$studentRecord || !$studentRecord->myClass) {
            return null;
        }

        // Eager load necessary relationships to prevent N+1 queries
        $students = StudentRecord::with([
            'user',
            'results' => function ($query) use ($academicYearId, $semesterId) {
                $query->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $semesterId);
            }
        ])->where('my_class_id', $studentRecord->myClass->id)->get();

        $scores = $students->map(function ($record) {
            return [
                'id' => $record->id,
                'name' => $record->user?->name ?? 'Deleted User',
                'total_score' => $record->results->sum('total_score'),
            ];
        })->sortByDesc('total_score')->values();

        foreach ($scores as $index => $data) {
            if ($data['id'] == $studentRecord->id) {
                return $index + 1;
            }
        }

        return null;
    }

    public function generatePdf($studentId)
    {
        $studentRecord = StudentRecord::with([
            'user',
            'myClass',
            'section',
            'results' => function ($query) {
                $query->with('subject');
            }
        ])->findOrFail($studentId);
        
        $academicYearId = AcademicYear::latest()->first()->id;
        $semesterId = Semester::latest()->first()->id;
        
        $data = $this->prepareReportData($studentRecord, $academicYearId, $semesterId);
        
        $pdf = PDF::loadView('pages.result.official-report', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download("report-{$data['studentRecord']->user->name}-{$data['semesterName']}.pdf");
    }
    














    
    public function annualClassResult(Request $request)
    {
        $classes = MyClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        if (!$request->has('classId') || !$request->has('academicYearId')) {
            return view('pages.result.annual-class-result', [
                'classes' => $classes,
                'academicYears' => $academicYears,
                'class' => null,
                'academicYear' => null,
                'students' => collect(),
                'subjects' => collect(),
                'annualReports' => [],
                'termReports' => [],
                'termStats' => [],
                'stats' => [
                    'total_students' => 0,
                    'subjects_count' => 0,
                    'max_total_score' => 0,
                ],
            ]);
        }

        $class = MyClass::findOrFail($request->classId);
        $academicYear = AcademicYear::findOrFail($request->academicYearId);
        $semesters = Semester::where('academic_year_id', $academicYear->id)->get();

        $students = StudentRecord::with([
            'user' => function($query) {
                $query->whereNull('deleted_at');
            },
            'studentSubjects',
            'results' => function ($query) use ($academicYear, $semesters) {
                $query->where('academic_year_id', $academicYear->id)
                    ->whereIn('semester_id', $semesters->pluck('id'))
                    ->with('subject');
            }
        ])
        ->where('my_class_id', $class->id)
        ->join('users', 'users.id', '=', 'student_records.user_id')
        ->whereNull('users.deleted_at')
        ->orderBy('users.name')
        ->select('student_records.*')
        ->get();

        $subjects = Subject::where('my_class_id', $class->id)
            ->orderBy('name')
            ->get();

        $stats = [
            'total_students' => $students->count(),
            'subjects_count' => $subjects->count(),
            'max_total_score' => $subjects->count() * 100 * $semesters->count(),
        ];

        $termReports = [];
        $termStats = [];
        $annualReports = [];

        // Preload all results for all students and semesters
        $allResults = Result::whereIn('student_record_id', $students->pluck('id'))
            ->where('academic_year_id', $academicYear->id)
            ->whereIn('semester_id', $semesters->pluck('id'))
            ->with('subject')
            ->get()
            ->groupBy(['student_record_id', 'semester_id']);

        foreach ($semesters as $semester) {
            $termReports[$semester->id] = [];
            $termTotals = [];
            $subjectScores = [];

            foreach ($students as $student) {
                if (!$student->user) {
                    continue;
                }

                $semesterResults = $allResults[$student->id][$semester->id] ?? collect();
                $results = $semesterResults->keyBy('subject_id');

                $formattedResults = [];
                foreach ($results as $subjectId => $result) {
                    // Calculate test score as sum of all CAs
                    $testScore = ($result->ca1_score ?? 0) + 
                                ($result->ca2_score ?? 0) + 
                                ($result->ca3_score ?? 0) + 
                                ($result->ca4_score ?? 0);

                    $formattedResults[$subjectId] = [
                        'ca1_score' => $result->ca1_score,
                        'ca2_score' => $result->ca2_score,
                        'ca3_score' => $result->ca3_score,
                        'ca4_score' => $result->ca4_score,
                        'test_score' => $testScore,
                        'exam_score' => $result->exam_score,
                        'total_score' => $result->total_score,
                        'grade' => $this->calculateGrade($result->total_score),
                        'comment' => $result->teacher_comment ?: $this->getDefaultComment($result->total_score)
                    ];
                }

                $totalScore = $results->sum('total_score');
                $percentage = $stats['subjects_count'] > 0
                    ? round(($totalScore / ($stats['subjects_count'] * 100)) * 100, 2)
                    : 0;

                $termReports[$semester->id][] = [
                    'student' => $student,
                    'results' => $formattedResults,
                    'total_score' => $totalScore,
                    'percentage' => $percentage,
                    'rank' => 0
                ];

                $termTotals[$student->id] = $totalScore;
            }

            // Sort and rank students for this term
            usort($termReports[$semester->id], function ($a, $b) {
                return $b['total_score'] <=> $a['total_score'];
            });

            foreach ($termReports[$semester->id] as $index => &$report) {
                $report['rank'] = $index + 1;
            }

            // Calculate term statistics
            if (!empty($termReports[$semester->id])) {
                $termStats[$semester->id] = [
                    'average_percentage' => collect($termReports[$semester->id])->avg('percentage'),
                    'pass_rate' => (collect($termReports[$semester->id])->filter(fn($r) => $r['percentage'] >= 50)->count() / max(1, count($termReports[$semester->id]))) * 100,
                    'top_student' => $termReports[$semester->id][0]['student']->user->name ?? 'N/A',
                    'top_score' => $termReports[$semester->id][0]['percentage'] ?? 0,
                ];
            }
        }

        // Process annual reports
        foreach ($students as $student) {
            if (!$student->user) {
                continue;
            }

            $annualResult = [
                'student' => $student,
                'term_totals' => [],
                'subject_totals' => [],
                'grand_total' => 0,
                'average_percentage' => 0,
            ];

            // Calculate totals for each semester
            foreach ($semesters as $semester) {
                $termTotal = collect($termReports[$semester->id])
                    ->firstWhere('student.id', $student->id)['total_score'] ?? 0;
                $annualResult['term_totals'][$semester->id] = $termTotal;
                $annualResult['grand_total'] += $termTotal;
            }

            // Calculate subject totals across all semesters
            foreach ($subjects as $subject) {
                $subjectTotal = 0;
                foreach ($semesters as $semester) {
                    $termReport = collect($termReports[$semester->id])
                        ->firstWhere('student.id', $student->id);

                    if ($termReport && isset($termReport['results'][$subject->id])) {
                        $subjectTotal += $termReport['results'][$subject->id]['total_score'];
                    }
                }

                $annualResult['subject_totals'][$subject->id] = [
                    'subject' => $subject,
                    'total' => $subjectTotal,
                    'average' => $semesters->count() > 0 ? round($subjectTotal / $semesters->count(), 2) : 0
                ];
            }

            // Calculate overall average
            $annualResult['average_percentage'] = $stats['max_total_score'] > 0
                ? round(($annualResult['grand_total'] / $stats['max_total_score']) * 100, 2)
                : 0;

            $annualReports[] = $annualResult;
        }

        // Sort annual reports by grand total
        usort($annualReports, function ($a, $b) {
            return $b['grand_total'] <=> $a['grand_total'];
        });

        // Assign ranks
        foreach ($annualReports as $index => &$report) {
            $report['rank'] = $index + 1;
        }

        // Calculate top subject
        $topSubject = collect($subjects)->map(function($subject) use ($termReports, $semesters) {
            $scores = [];
            foreach ($termReports as $semesterId => $reports) {
                foreach ($reports as $report) {
                    if (isset($report['results'][$subject->id])) {
                        $scores[] = $report['results'][$subject->id]['total_score'];
                    }
                }
            }
            return [
                'name' => $subject->name,
                'avg' => count($scores) ? round(array_sum($scores) / count($scores), 1) : 0
            ];
        })->sortByDesc('avg')->first();

        // Subject performance for the chart
        $subjectPerformance = collect($subjects)->map(function($subject) use ($termReports) {
            $scores = [];
            foreach ($termReports as $semesterId => $reports) {
                foreach ($reports as $report) {
                    if (isset($report['results'][$subject->id])) {
                        $scores[] = $report['results'][$subject->id]['total_score'];
                    }
                }
            }
            return [
                'name' => $subject->name,
                'avg' => count($scores) ? round(array_sum($scores) / count($scores), 1) : 0,
                'max' => count($scores) ? max($scores) : 0,
                'min' => count($scores) ? min($scores) : 0,
                'pass_rate' => count($scores) ? round(count(array_filter($scores, fn($s) => $s >= 50)) / count($scores) * 100, 1) : 0
            ];
        });

        return view('pages.result.annual-class-result', [
            'classes' => $classes,
            'academicYears' => $academicYears,
            'class' => $class,
            'academicYear' => $academicYear,
            'semesters' => $semesters,
            'subjects' => $subjects,
            'students' => $students,
            'termReports' => $termReports,
            'termStats' => $termStats,
            'annualReports' => $annualReports,
            'stats' => $stats,
            'topSubject' => $topSubject,
            'subjectPerformance' => $subjectPerformance
        ]);
    }

    public function showStudentAnnualResult($studentId, $academicYearId)
    {
        $studentRecord = StudentRecord::with(['user', 'myClass'])
            ->where('user_id', $studentId)
            ->firstOrFail();
        $academicYear = AcademicYear::findOrFail($academicYearId);
        $semesters = Semester::where('academic_year_id', $academicYear->id)->get();
        $subjects = Subject::where('my_class_id', $studentRecord->my_class_id)
            ->orderBy('name')
            ->get();
        $results = Result::with('subject')
            ->where('user_id', $studentId)
            ->where('academic_year_id', $academicYear->id)
            ->whereIn('semester_id', $semesters->pluck('id'))
            ->get()
            ->groupBy('subject_id');
        $annualResults = [];
        $grandTotal = 0;
        $maxPossibleTotal = $subjects->count() * 100 * $semesters->count();
        foreach ($subjects as $subject) {
            $subjectResults = $results->get($subject->id, collect());
            $subjectTotal = $subjectResults->sum('total_score');
            $subjectAverage = $semesters->count() > 0 ? $subjectTotal / $semesters->count() : 0;
            $annualResults[$subject->id] = [
                'subject' => $subject,
                'total' => $subjectTotal,
                'average' => $subjectAverage,
                'results' => $subjectResults
            ];
            $grandTotal += $subjectTotal;
        }
        $averagePercentage = $maxPossibleTotal > 0 ? round(($grandTotal / $maxPossibleTotal) * 100, 2) : 0;
        $classPosition = $this->calculateStudentPosition($studentId, $academicYear->id, $studentRecord->my_class_id);
        return view('pages.result.student-annual-result', [
            'studentRecord' => $studentRecord,
            'academicYear' => $academicYear,
            'semesters' => $semesters,
            'subjects' => $subjects,
            'annualResults' => $annualResults,
            'grandTotal' => $grandTotal,
            'averagePercentage' => $averagePercentage,
            'classPosition' => $classPosition,
            'totalStudents' => StudentRecord::where('my_class_id', $studentRecord->my_class_id)->count()
        ]);
    }

    private function calculateStudentPosition($studentId, $academicYearId, $classId)
    {
        $students = StudentRecord::where('my_class_id', $classId)->pluck('user_id');
        $rankings = Result::whereIn('user_id', $students)
            ->where('academic_year_id', $academicYearId)
            ->selectRaw('user_id, SUM(total_score) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->get()
            ->pluck('user_id')
            ->toArray();
        $position = array_search($studentId, $rankings) + 1;
        $totalStudents = count($rankings);
        return $position . '/' . $totalStudents;
    }
    
        private function getDefaultComment($score)
        {
            return match (true) {
                $score >= 75 => 'Distinction',
                $score >= 70 => 'Very good',
                $score >= 65 => 'Good',
                $score >= 60 => 'Credit',
                $score >= 55 => 'Credit',
                $score >= 50 => 'Credit',
                $score >= 45 => 'Pass',
                $score >= 40 => 'Pass',
                default => 'Fail',
            };
        }
    }
