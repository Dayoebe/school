<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\StudentRecord;
use App\Models\TermReport;
use App\Models\Result;
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
        // Eager load academic years with their semesters
        $academicYears = AcademicYear::with('semesters')
            ->orderBy('start_year', 'desc')
            ->get();

        $latestAcademicYear = $academicYears->first();
        $semesters = $latestAcademicYear ? $latestAcademicYear->semesters : collect();

        if (!$request->has('classId')) {
            return view('pages.result.class-result', [
                'class' => null,
                'academicYear' => $latestAcademicYear,
                'semester' => $semesters->first(),
                'subjects' => collect(),
                'studentReports' => null,
                'classStats' => null,
                'academicYears' => $academicYears,
                'semesters' => $semesters
            ]);
        }

        $request->validate([
            'classId' => 'required|exists:my_classes,id',
            'academicYearId' => 'required|exists:academic_years,id',
            'semesterId' => 'required|exists:semesters,id'
        ]);

        // Get the selected academic period with eager loading
        $academicYear = AcademicYear::with(['semesters' => function ($q) use ($request) {
            $q->where('id', $request->semesterId);
        }])
            ->find($request->academicYearId);

        $semester = $academicYear->semesters->first();
        $class = \App\Models\MyClass::find($request->classId);

        // Main optimized query with all necessary eager loading

        $students = StudentRecord::with([
            'user:id,name,email,profile_photo_path', // Only select needed columns
            'myClass:id,name',
            'section:id,name',
            'studentSubjects.subject:id,name', // If you need subjects through studentSubjects
            'results' => function ($q) use ($academicYear, $semester) {
                $q->with(['subject:id,name']) // Eager load subject with only needed columns
                    ->where('academic_year_id', $academicYear->id)
                    ->where('semester_id', $semester->id);
            }
        ])
            ->where('my_class_id', $class->id)
            ->join('users', 'student_records.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('student_records.*')
            ->get();

        // Get subjects for the class
        $subjects = Subject::where('my_class_id', $class->id)
            ->orderBy('name')
            ->get(['id', 'name']); // Only select needed columns

        $classStats = null;
        $studentReports = [];

        if ($students->isNotEmpty() && $subjects->isNotEmpty()) {
            $classStats = [
                'total_students' => $students->count(),
                'subjects_count' => $subjects->count(),
                'max_total_score' => $subjects->count() * 100,
            ];

            foreach ($students as $student) {
                $results = $student->results->keyBy('subject_id');
                $totalScore = $results->sum('total_score');
                $percentage = $classStats['subjects_count'] > 0
                    ? round(($totalScore / $classStats['max_total_score']) * 100, 2)
                    : 0;

                $studentReports[] = [
                    'student' => $student->only(['id', 'user', 'admission_number']),
                    'results' => $results,
                    'total_score' => $totalScore,
                    'percentage' => $percentage,
                    'position' => $this->calculatePosition($student, $academicYear->id, $semester->id),
                ];
            }

            usort($studentReports, fn($a, $b) => $b['total_score'] <=> $a['total_score']);

            foreach ($studentReports as $index => &$report) {
                $report['rank'] = $index + 1;
            }
        }

        return view('pages.result.class-result', [
            'class' => $class,
            'academicYear' => $academicYear,
            'semester' => $semester,
            'subjects' => $subjects,
            'studentReports' => $studentReports,
            'classStats' => $classStats,
            'academicYears' => $academicYears,
            'semesters' => Semester::where('academic_year_id', $academicYear->id)->get()
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
        // Get the data (same as classResult method)
        $data = $this->classResult($request)->getData();

        // Create CSV filename
        $filename = "class-results-{$data['class']->name}-{$data['semester']->name}-" . now()->format('Y-m-d') . ".csv";

        // Set headers for download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        // Create CSV content
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Rank',
                'Student Name',
                'Admission Number',
                ...$data['subjects']->pluck('name')->toArray(), // Subject names as columns
                'Total Score',
                'Percentage',
                'Position'
            ]);

            // Add student data
            foreach ($data['studentReports'] as $report) {
                $row = [
                    $report['rank'],
                    $report['student']->user->name,
                    $report['student']->admission_number,
                ];

                // Add subject scores
                foreach ($data['subjects'] as $subject) {
                    $result = $report['results'][$subject->id] ?? null;
                    $row[] = $result ? $result->total_score : '-';
                }

                // Add summary data
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
        // Get all classes and academic years for dropdowns
        $classes = \App\Models\MyClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        // Validate required parameters if they exist
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

        // Get class
        $class = \App\Models\MyClass::find($request->classId);
        if (!$class) {
            return back()->with('error', 'Class not found');
        }

        // Get academic year
        $academicYear = AcademicYear::find($request->academicYearId);
        if (!$academicYear) {
            return back()->with('error', 'Academic year not found');
        }

        // Get all semesters for this academic year
        $semesters = Semester::where('academic_year_id', $academicYear->id)->get();
        if ($semesters->isEmpty()) {
            return back()->with('error', 'No semesters found for selected academic year');
        }

        // Get all students in the class with user eager loaded (fixes N+1)

// Update the students query to include subject eager loading
$students = StudentRecord::with([
    'user', 
    'results' => function ($q) use ($request, $semesters) {
        $q->where('academic_year_id', $request->academicYearId)
          ->whereIn('semester_id', $semesters->pluck('id'))
          ->with('subject'); // Add this to eager load subjects
    }
])
->where('my_class_id', $request->classId)
->join('users', 'users.id', '=', 'student_records.user_id')
->orderBy('users.name')
->select('student_records.*')
->get();

    

        // Get all subjects for this class
        $subjects = Subject::where('my_class_id', $class->id)
            ->orderBy('name')
            ->get();

        // Prepare annual reports
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

            // Group results by subject
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

            // Calculate totals
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

        // Sort by grand total
        usort($annualReports, function ($a, $b) {
            return $b['grand_total'] <=> $a['grand_total'];
        });

        // Add ranks
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

            // CSV headers
            $headers = ['Rank', 'Student Name', 'Admission Number'];
            foreach ($data['subjects'] as $subject) {
                $headers[] = $subject->name;
            }
            $headers[] = 'Total Score';
            $headers[] = 'Average Percentage';

            fputcsv($file, $headers);

            // Student data
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

    // use Barryvdh\DomPDF\Facade\Pdf;

    public function exportAnnualResultPdf(Request $request)
    {
        // Validate required parameters
        $request->validate([
            'classId' => 'required|exists:my_classes,id',
            'academicYearId' => 'required|exists:academic_years,id'
        ]);
    
        // Get the data (reuse the annualResult method logic)
        $data = $this->annualResult($request)->getData();
        
        // Generate PDF
        $pdf = Pdf::loadView('pages.result.annual-result-pdf', [
            'data' => $data,
            'title' => "Annual Results - {$data['class']->name} ({$data['academicYear']->name})"
        ])->setPaper('a4', 'portrait');
        
        return $pdf->download("annual-results-{$data['class']->name}-{$data['academicYear']->name}.pdf");
    }



    public function showStudentAnnualResult($studentId, $academicYearId)
{
    // Get the student record
    $studentRecord = StudentRecord::with(['user', 'myClass'])
        ->where('user_id', $studentId)
        ->firstOrFail();

    // Get academic year
    $academicYear = AcademicYear::findOrFail($academicYearId);

    // Get all semesters for this academic year
    $semesters = Semester::where('academic_year_id', $academicYear->id)->get();

    // Get all subjects for the student's class
    $subjects = Subject::where('my_class_id', $studentRecord->my_class_id)
        ->orderBy('name')
        ->get();

    // Get all results for this student across all semesters
    $results = Result::with('subject')
        ->where('user_id', $studentId)
        ->where('academic_year_id', $academicYear->id)
        ->whereIn('semester_id', $semesters->pluck('id'))
        ->get()
        ->groupBy('subject_id');

    // Calculate annual totals and averages
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

    // Calculate overall average percentage
    $averagePercentage = $maxPossibleTotal > 0 ? round(($grandTotal / $maxPossibleTotal) * 100, 2) : 0;

    // Get class position (you'll need to implement this based on your logic)
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
    // Implement your position calculation logic here
    // This is a placeholder - replace with your actual logic
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
}
