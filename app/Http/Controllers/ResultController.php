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
    public function print(Request $request, $studentId)
    {
        $studentRecord = StudentRecord::with(['user', 'myClass', 'section'])->find($studentId);
        if (!$studentRecord) {
            abort(404, 'Student record not found');
        }

        if (!$studentRecord->myClass) {
            abort(404, 'Student class information not found');
        }

        $academicYearId = $request->academicYearId ?? $request->input('academicYearId');
        $semesterId = $request->semesterId ?? $request->input('semesterId');

        $studentRecord = StudentRecord::with(['user', 'myClass', 'section'])->findOrFail($studentId);

        $rawResults = Result::where([
            'student_record_id' => $studentId,
            'academic_year_id' => $academicYearId,
            'semester_id' => $semesterId,
        ])->get();

        $classResults = Result::where([
            'academic_year_id' => $academicYearId,
            'semester_id' => $semesterId
        ])
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

        $results = $rawResults->keyBy('subject_id')->map(function ($result) {
            $test = (int) $result->test_score;
            $exam = (int) $result->exam_score;
            $total = $test + $exam;

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
                'A1' => 'Distinction ✨',
                'B2' => 'Very good 💪',
                'B3' => 'Good 🔥',
                'C4' => 'Credit 👍',
                'C5' => 'Credit 🌱',
                'C6' => 'Credit 📈',
                'D7' => 'Pass ⏳',
                'E8' => 'Pass ⚠️',
                default => 'Fail 🚨',
            };

            return [
                'test_score' => $test,
                'exam_score' => $exam,
                'total_score' => $total,
                'grade' => $grade,
                'comment' => $result->teacher_comment ?: $comment,
            ];
        });

        $subjects = Subject::whereIn('id', array_keys($results->toArray()))
            ->orderBy('name')
            ->get();

        $totalSubjects = $subjects->count();
        $maxTotalScore = $totalSubjects * 100;

        $grandTotal = $rawResults->sum('total_score');
        $grandTotalTest = $rawResults->sum('test_score');
        $grandTotalExam = $rawResults->sum('exam_score');
        $percentage = $results->count() ? round($grandTotal / $results->count(), 2) : 0;
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

        return view('pages.result.print', compact(
            'studentRecord',
            'subjects',
            'results',
            'grandTotal',
            'grandTotalTest',
            'grandTotalExam',
            'subjectsPassed',
            'totalScore',
            'subjectStats',
            'percentage',
            'principalComment',
            'totalStudents',
            'classPosition',
            'academicYearId',
            'semesterId',
            'academicYearName',
            'semesterName',
            'termReport',
            'maxTotalScore',
            'totalSubjects'
        ));
    }

    protected function calculatePosition($studentRecord, $academicYearId, $semesterId)
    {
        if (!$studentRecord || !$studentRecord->myClass) {
            return null;
        }

        $students = StudentRecord::where('my_class_id', $studentRecord->myClass->id)
            ->with(['user', 'results' => function ($query) use ($academicYearId, $semesterId) {
                $query->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $semesterId);
            }])->get();

        $scores = $students->map(function ($record) {
            return [
                'id' => $record->id,
                'name' => $record->user->name,
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
        $data = $this->prepareReportData($studentId);
        $pdf = PDF::loadView('pages.result.official-report', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download("report-{$data['studentRecord']->user->name}-{$data['semesterName']}.pdf");
    }

    public function classResult(Request $request)
    {
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $allSemesters = Semester::orderBy('name')->get();
        $classes = MyClass::orderBy('name')->get();
        $studentReports = [];
        $subjectStats = [];
        $classStats = [
            'total_students' => 0,
            'subjects_count' => 0,
            'max_total_score' => 0,
        ];
        $subjects = collect();
        $students = collect();
        $academicYear = $request->filled('academicYearId') 
            ? AcademicYear::find($request->academicYearId)
            : AcademicYear::latest()->first();
        $semesters = $academicYear
            ? Semester::where('academic_year_id', $academicYear->id)->orderBy('name')->get()
            : $allSemesters;
        $semester = $request->filled('semesterId')
            ? Semester::find($request->semesterId)
            : ($semesters->first() ?? $allSemesters->first());
        $class = $request->filled('classId') 
            ? MyClass::find($request->classId)
            : null;
        if (!$request->filled('classId') || !$class) {
            return view('pages.result.class-result', [
                'academicYears' => $academicYears,
                'semesters' => $semesters,
                'classes' => $classes,
                'studentReports' => $studentReports,
                'subjectStats' => $subjectStats,
                'classStats' => $classStats,
                'academicYear' => $academicYear,
                'semester' => $semester,
                'class' => $class,
                'subjects' => $subjects,
                'students' => $students,
                'showResults' => false
            ]);
        }
        $students = StudentRecord::with([
                'user' => function($query) {
                    $query->orderBy('name');
                },
                'results' => function($query) use ($academicYear, $semester) {
                    $query->where('academic_year_id', $academicYear->id)
                          ->where('semester_id', $semester->id)
                          ->with('subject');
                },
                'myClass'
            ])
            ->where('my_class_id', $class->id)
            ->whereHas('user')
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->orderBy('users.name')
            ->select('student_records.*')
            ->paginate(20);
        $subjects = Subject::where('my_class_id', $class->id)
            ->orderBy('name')
            ->get();
        if ($students->count() > 0) {
            $classStats = [
                'total_students' => $students->total(),
                'subjects_count' => $subjects->count(),
                'max_total_score' => $subjects->count() * 100,
            ];
            foreach ($students as $student) {
                if (!$student->user) {
                    continue;
                }
                $results = $student->results ? $student->results->keyBy('subject_id') : collect();
                $totalScore = $results->sum('total_score');
                $percentage = $classStats['subjects_count'] > 0
                    ? round(($totalScore / $classStats['max_total_score']) * 100, 2)
                    : 0;
                $studentReports[] = [
                    'student' => $student,
                    'results' => $results,
                    'total_score' => $totalScore,
                    'percentage' => $percentage,
                    'position' => $this->calculatePosition($student, $academicYear->id, $semester->id),
                ];
            }
            if (!empty($studentReports)) {
                usort($studentReports, fn($a, $b) => $b['total_score'] <=> $a['total_score']);
                foreach ($studentReports as $index => &$report) {
                    $report['rank'] = $index + 1;
                }
            }
            if ($subjects->count() > 0) {
                foreach ($subjects as $subject) {
                    $subjectScores = collect($studentReports)->map(function ($report) use ($subject) {
                        return $report['results'][$subject->id]->total_score ?? 0;
                    });
                    $subjectStats[] = [
                        'subject' => $subject,
                        'average' => $subjectScores->avg(),
                        'highest' => $subjectScores->max(),
                        'lowest' => $subjectScores->min(),
                        'pass_rate' => ($subjectScores->filter(fn($s) => $s >= 40)->count() / max(1, $subjectScores->count())) * 100
                    ];
                }
            }
        }
        return view('pages.result.class-result', [
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'classes' => $classes,
            'class' => $class,
            'academicYear' => $academicYear,
            'semester' => $semester,
            'subjects' => $subjects,
            'students' => $students,
            'studentReports' => $studentReports,
            'subjectStats' => $subjectStats,
            'classStats' => $classStats,
            'showResults' => true
        ]);
    }

    public function printClassResult(Request $request)
    {
        $data = $this->classResult($request)->getData();
        $pdf = PDF::loadView('pages.result.class-result-print', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download("class-result-{$data['class']->name}-{$data['semester']->name}.pdf");
    }

    public function exportClassResult(Request $request)
    {
        $data = $this->classResult($request)->getData();
        $filename = "class-results-{$data['class']->name}-{$data['semester']->name}-" . now()->format('Y-m-d') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Rank',
                'Student Name',
                'Admission Number',
                ...$data['subjects']->pluck('name')->toArray(),
                'Total Score',
                'Percentage',
                'Position'
            ]);
            foreach ($data['studentReports'] as $report) {
                $row = [
                    $report['rank'],
                    $report['student']->user->name,
                    $report['student']->admission_number,
                ];
                foreach ($data['subjects'] as $subject) {
                    $result = $report['results'][$subject->id] ?? null;
                    $row[] = $result ? $result->total_score : '-';
                }
                $row[] = $report['total_score'];
                $row[] = $report['percentage'] . '%';
                $row[] = $report['position'];
                fputcsv($file, $row);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function annualResult(Request $request)
    {
        $classes = \App\Models\MyClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        if (!$request->has('classId') || !$request->has('academicYearId')) {
            return view('pages.result.annual-result', [
                'classes' => $classes,
                'academicYears' => $academicYears,
                'class' => null,
                'academicYear' => null,
                'students' => collect(),
                'subjects' => collect(),
                'annualReports' => [],
                'stats' => [
                    'total_students' => 0,
                    'subjects_count' => 0,
                    'max_total_score' => 0
                ]
            ]);
        }
        $class = \App\Models\MyClass::find($request->classId);
        if (!$class) {
            return back()->with('error', 'Class not found');
        }
        $academicYear = AcademicYear::find($request->academicYearId);
        if (!$academicYear) {
            return back()->with('error', 'Academic year not found');
        }
        $semesters = Semester::where('academic_year_id', $academicYear->id)->get();
        if ($semesters->isEmpty()) {
            return back()->with('error', 'No semesters found for selected academic year');
        }
        $students = StudentRecord::with([
            'user',
            'results' => function ($q) use ($request, $semesters) {
                $q->where('academic_year_id', $request->academicYearId)
                    ->whereIn('semester_id', $semesters->pluck('id'))
                    ->with('subject');
            }
        ])
            ->where('my_class_id', $request->classId)
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->orderBy('users.name')
            ->select('student_records.*')
            ->get();
        $subjects = Subject::where('my_class_id', $class->id)
            ->orderBy('name')
            ->get();
        $annualReports = [];
        $stats = [
            'total_students' => $students->count(),
            'subjects_count' => $subjects->count(),
            'max_total_score' => $subjects->count() * 100 * $semesters->count(),
        ];
        foreach ($students as $student) {
            $annualResult = [
                'student' => $student,
                'terms' => [],
                'subject_totals' => [],
                'grand_total' => 0,
                'average_percentage' => 0,
            ];
            $subjectResults = [];
            foreach ($student->results as $result) {
                $subjectId = $result->subject_id;
                if (!isset($subjectResults[$subjectId])) {
                    $subjectResults[$subjectId] = [
                        'subject' => $result->subject,
                        'terms' => [],
                        'total' => 0,
                    ];
                }
                $termScore = $result->total_score;
                $subjectResults[$subjectId]['terms'][$result->semester_id] = $termScore;
                $subjectResults[$subjectId]['total'] += $termScore;
            }
            $grandTotal = 0;
            foreach ($subjectResults as $subjectId => $subjectData) {
                $grandTotal += $subjectData['total'];
                $annualResult['subject_totals'][$subjectId] = [
                    'subject' => $subjectData['subject'],
                    'total' => $subjectData['total'],
                    'average' => $subjectData['total'] / $semesters->count(),
                ];
            }
            $annualResult['grand_total'] = $grandTotal;
            $annualResult['average_percentage'] = $stats['subjects_count'] > 0
                ? round(($grandTotal / ($stats['subjects_count'] * 100 * $semesters->count())) * 100, 2)
                : 0;
            $annualReports[] = $annualResult;
        }
        usort($annualReports, function ($a, $b) {
            return $b['grand_total'] <=> $a['grand_total'];
        });
        foreach ($annualReports as $index => &$report) {
            $report['rank'] = $index + 1;
        }
        return view('pages.result.annual-result', compact(
            'classes',
            'academicYears',
            'class',
            'academicYear',
            'semesters',
            'subjects',
            'annualReports',
            'stats'
        ));
    }

    public function exportAnnualResult(Request $request)
    {
        $data = $this->annualResult($request)->getData();
        $filename = "annual-results-{$data['class']->name}-{$data['academicYear']->name}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            $headers = ['Rank', 'Student Name', 'Admission Number'];
            foreach ($data['subjects'] as $subject) {
                $headers[] = $subject->name;
            }
            $headers[] = 'Total Score';
            $headers[] = 'Average Percentage';
            fputcsv($file, $headers);
            foreach ($data['annualReports'] as $report) {
                $row = [
                    $report['rank'],
                    $report['student']->user->name,
                    $report['student']->admission_number,
                ];
                foreach ($data['subjects'] as $subject) {
                    $row[] = $report['subject_totals'][$subject->id]['total'] ?? '-';
                }
                $row[] = $report['grand_total'];
                $row[] = $report['average_percentage'] . '%';
                fputcsv($file, $row);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportAnnualResultPdf(Request $request)
    {
        $request->validate([
            'classId' => 'required|exists:my_classes,id',
            'academicYearId' => 'required|exists:academic_years,id'
        ]);
        $data = $this->annualResult($request)->getData();
        $pdf = Pdf::loadView('pages.result.annual-result-pdf', [
            'data' => $data,
            'title' => "Annual Results - {$data['class']->name} ({$data['academicYear']->name})"
        ])->setPaper('a4', 'portrait');
        return $pdf->download("annual-results-{$data['class']->name}-{$data['academicYear']->name}.pdf");
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
                    'max_total_score' => 0
                ]
            ]);
        }
        $class = MyClass::findOrFail($request->classId);
        $academicYear = AcademicYear::findOrFail($request->academicYearId);
        $semesters = Semester::where('academic_year_id', $academicYear->id)->get();
        $students = StudentRecord::with(['user', 'results' => function($q) use ($academicYear) {
                $q->where('academic_year_id', $academicYear->id)
                  ->with('subject');
            }])
            ->where('my_class_id', $class->id)
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->orderBy('users.name')
            ->select('student_records.*')
            ->get();
        $subjects = Subject::where('my_class_id', $class->id)
            ->orderBy('name')
            ->get();
        $termReports = [];
        $termStats = [];
        $annualReports = [];
        $stats = [
            'total_students' => $students->count(),
            'subjects_count' => $subjects->count(),
            'max_total_score' => $subjects->count() * 100 * $semesters->count(),
        ];
        foreach ($semesters as $semester) {
            $termReports[$semester->id] = [];
            $termTotals = [];
            foreach ($students as $student) {
                $results = $student->results
                    ->where('semester_id', $semester->id)
                    ->keyBy('subject_id');
                $totalScore = $results->sum('total_score');
                $percentage = $stats['subjects_count'] > 0 
                    ? round(($totalScore / ($stats['subjects_count'] * 100)) * 100, 2)
                    : 0;
                $termReports[$semester->id][] = [
                    'student' => $student,
                    'results' => $results,
                    'total_score' => $totalScore,
                    'percentage' => $percentage,
                ];
                $termTotals[$student->id] = $totalScore;
            }
            usort($termReports[$semester->id], fn($a, $b) => $b['total_score'] <=> $a['total_score']);
            foreach ($termReports[$semester->id] as $index => &$report) {
                $report['rank'] = $index + 1;
            }
            $termStats[$semester->id] = [
                'average_percentage' => collect($termReports[$semester->id])->avg('percentage'),
                'pass_rate' => (collect($termReports[$semester->id])->filter(fn($r) => $r['percentage'] >= 50)->count() / max(1, count($termReports[$semester->id]))) * 100,
                'top_student' => $termReports[$semester->id][0]['student']->user->name ?? 'N/A',
                'top_score' => $termReports[$semester->id][0]['percentage'] ?? 0,
                'average_attendance' => '95%',
            ];
        }
        foreach ($students as $student) {
            $annualResult = [
                'student' => $student,
                'term_totals' => [],
                'subject_totals' => [],
                'subject_details' => [],
                'grand_total' => 0,
                'average_percentage' => 0,
            ];
            foreach ($subjects as $subject) {
                $subjectTotal = 0;
                $subjectDetails = [];
                foreach ($semesters as $semester) {
                    $result = $student->results->firstWhere('subject_id', $subject->id);
                    $termScore = $result ? $result->total_score : 0;
                    $subjectTotal += $termScore;
                    $subjectDetails[$semester->id] = [
                        'test' => $result ? $result->test_score : 0,
                        'exam' => $result ? $result->exam_score : 0,
                        'total' => $termScore,
                    ];
                }
                $annualResult['subject_details'][$subject->id] = [
                    'subject' => $subject,
                    'total' => $subjectTotal,
                    'average' => $subjectTotal / $semesters->count(),
                    'details' => $subjectDetails,
                ];
                $annualResult['grand_total'] += $subjectTotal;
            }
            $annualResult['average_percentage'] = $stats['subjects_count'] > 0 
                ? round(($annualResult['grand_total'] / ($stats['subjects_count'] * 100 * $semesters->count())) * 100, 2)
                : 0;
            $annualReports[] = $annualResult;
        }
        usort($annualReports, fn($a, $b) => $b['grand_total'] <=> $a['grand_total']);
        foreach ($annualReports as $index => &$report) {
            $report['rank'] = $index + 1;
        }
        return view('pages.result.annual-class-result', compact(
            'classes',
            'academicYears',
            'class',
            'academicYear',
            'semesters',
            'subjects',
            'termReports',
            'termStats',
            'annualReports',
            'stats'
        ));
    }
}
