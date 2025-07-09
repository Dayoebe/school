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
use Illuminate\Support\Facades\DB; // Import DB facade

class ResultController extends Controller
{

    public function viewResults(Request $request)
    {
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $classes = MyClass::orderBy('name')->get();
        $academicYearId = $request->input('academicYearId');
        $semesterId = $request->input('semesterId');
        $classId = $request->input('classId');
        $subjectId = $request->input('subjectId');
        $mode = $request->input('mode', 'subject');
        $subjectResults = collect();
        $classResults = collect();
        $subjects = collect();
        $semesters = collect();
        $selectedSubject = null;
        $selectedClass = null;
        $academicYearName = 'Not Selected';
        $semesterName = 'Not Selected';
        if ($academicYearId) {
            $semesters = Semester::where('academic_year_id', $academicYearId)->get();
        }
        if ($classId) {
            $subjects = Subject::where('my_class_id', $classId)->get();
        }
        if ($academicYearId && $semesterId && $classId) {
            $academicYear = AcademicYear::find($academicYearId);
            $semester = Semester::find($semesterId);
            $class = MyClass::find($classId);
            $academicYearName = $academicYear->name ?? 'Unknown Academic Year';
            $semesterName = $semester->name ?? 'Unknown Term';
            $selectedClass = $class;
            if ($mode === 'subject' && $subjectId) {
                $selectedSubject = Subject::find($subjectId);
                $subjectResults = Result::with([
                    'student.user',
                    'subject'
                ])
                    ->where('subject_id', $subjectId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $semesterId)
                    ->whereHas('student', function ($q) use ($classId) {
                        $q->where('my_class_id', $classId)
                            ->where('is_graduated', false)
                            ->whereHas('user', function ($q) {
                                $q->whereNull('deleted_at');
                            });
                    })
                    ->get()
                    ->each(function ($result) {
                        if (!isset($result->total_score)) {
                            $result->setAttribute(
                                'total_score',
                                ($result->ca1_score ?? 0) +
                                    ($result->ca2_score ?? 0) +
                                    ($result->ca3_score ?? 0) +
                                    ($result->ca4_score ?? 0) +
                                    ($result->exam_score ?? 0)
                            );
                        }
                        if (!isset($result->grade)) {
                            $result->setAttribute('grade', $this->calculateGrade($result->total_score));
                        }
                    });
            } else {
                $students = StudentRecord::with([
                    'user',
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
                    ->get()
                    ->each(function ($student) use ($academicYearId, $semesterId, $classId) { // Pass necessary variables
                        $student->setAttribute('total_score', $student->results->sum('total_score'));
                        $student->setAttribute('average_score', $student->results->avg('total_score'));
                        $student->results->each(function ($result) {
                            if (!isset($result->grade) && isset($result->total_score)) {
                                $result->setAttribute('grade', $this->calculateGrade($result->total_score));
                            }
                        });
                        // Use the new semester-specific position calculation
                        $student->setAttribute('position', $this->calculateSemesterStudentPosition($student->id, $academicYearId, $semesterId, $classId));
                    })
                    ->sortBy('position') // Sort by the calculated position
                    ->values();
                $classResults = $students;
            }
        }
        return view('pages.result.view-result', compact(
            'academicYears',
            'classes',
            'academicYearId',
            'semesterId',
            'classId',
            'subjectId',
            'mode',
            'subjectResults',
            'classResults',
            'subjects',
            'semesters',
            'selectedSubject',
            'selectedClass',
            'academicYearName',
            'semesterName'
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
        // No need to pass pre-calculated values here, prepareReportData will handle it
        $data = $this->prepareReportData($studentRecord, $academicYearId, $semesterId);
        return view('pages.result.print', $data);
    }

    public function printClassResults($academicYearId, $semesterId, $classId)
    {
        $academicYear = AcademicYear::findOrFail($academicYearId);
        $semester = Semester::findOrFail($semesterId);
        $class = MyClass::findOrFail($classId);

        // Fetch all students for the class with their related data in one go
        $students = StudentRecord::with([
            'user' => function ($query) {
                $query->whereNull('deleted_at'); // Exclude soft-deleted users
            },
            'myClass',
            'section',
            'results' => function ($query) use ($academicYearId, $semesterId) {
                $query->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $semesterId)
                    ->with('subject');
            }
        ])
            ->where('my_class_id', $classId)
            ->where('is_graduated', false) // Exclude graduated students
            ->whereHas('user', function ($q) { // Ensure user is not soft-deleted
                $q->whereNull('deleted_at');
            })
            ->get();

        // Fetch all subjects for the class
        $allSubjects = Subject::where('my_class_id', $classId)->orderBy('name')->get();

        // Fetch all results for the class for the given academic year and semester
        $allClassResults = Result::with('subject')
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->whereIn('student_record_id', $students->pluck('id'))
            ->get();

        // Group results by subject for highest/lowest calculations
        $subjectOverallStats = [];
        foreach ($allClassResults->groupBy('subject_id') as $subjectId => $resultsCollection) {
            $subjectOverallStats[$subjectId] = [
                'highest' => (int)$resultsCollection->max('total_score'),
                'lowest' => (int)$resultsCollection->min('total_score')
            ];
        }

        // Fetch all term reports for the class and key them by student ID
        $allTermReports = TermReport::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->whereIn('student_record_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_record_id');

        // Calculate positions once for all students for the current semester
        $studentTotalScores = $students->mapWithKeys(function ($student) use ($academicYearId, $semesterId) {
            // Sum total scores from the already loaded results relationship, filtered by semester
            $totalScore = $student->results->where('academic_year_id', $academicYearId)
                                         ->where('semester_id', $semesterId)
                                         ->sum('total_score');
            return [$student->id => $totalScore];
        })->sortDesc();

        $classPositions = [];
        $rank = 1;
        $prevScore = null;
        $studentsAtRank = 0;

        foreach ($studentTotalScores as $studentId => $score) {
            if ($prevScore !== null && $score < $prevScore) {
                $rank += $studentsAtRank;
                $studentsAtRank = 1;
            } else {
                $studentsAtRank++;
            }
            $classPositions[$studentId] = $rank;
            $prevScore = $score;
        }

        $totalStudentsInClass = $students->count();

        $studentsData = [];
        foreach ($students as $student) {
            // Get the term report for this student
            $termReport = $allTermReports->get($student->id);
            
            // If no term report exists, create a default one (but don't save it)
            if (!$termReport) {
                $termReport = new TermReport([
                    'student_record_id' => $student->id,
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                    'psychomotor_traits' => TermReport::getDefaultPsychomotorScores(),
                    'affective_traits' => TermReport::getDefaultAffectiveScores(),
                    'co_curricular_activities' => TermReport::getDefaultCoCurricularScores()
                ]);
            }

            // Pass pre-fetched data to prepareReportData
            $studentsData[] = $this->prepareReportData(
                $student,
                $academicYearId,
                $semesterId,
                $allSubjects,
                $allClassResults->where('student_record_id', $student->id),
                $subjectOverallStats,
                $termReport, // Pass the term report
                $classPositions[$student->id] ?? 'N/A',
                $totalStudentsInClass
            );
        }

        return view('pages.result.print-class', [
            'academicYear' => $academicYear,
            'semester' => $semester,
            'class' => $class,
            'studentsData' => $studentsData,
        ]);
    }

    // This method calculates annual position (across all semesters in an academic year)
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

    // New method for semester-specific position calculation
    private function calculateSemesterStudentPosition($studentId, $academicYearId, $semesterId, $myClassId)
    {
        // Fetch all students in the class for the given academic year and semester
        $classStudents = StudentRecord::with([
            'user',
            'results' => function ($query) use ($academicYearId, $semesterId) {
                $query->where('academic_year_id', $academicYearId)
                      ->where('semester_id', $semesterId);
            }
        ])
        ->where('my_class_id', $myClassId)
        ->where('is_graduated', false)
        ->whereHas('user', function ($q) {
            $q->whereNull('deleted_at');
        })
        ->get();

        $scores = $classStudents->map(function ($record) {
            return [
                'id' => $record->id,
                'total_score' => (int) $record->results->sum('total_score'), // Ensure integer
            ];
        })->sortByDesc('total_score')->values();

        $rank = 1;
        $prevScore = null;
        $studentsAtRank = 0;
        $studentPosition = 'N/A';

        foreach ($scores as $data) {
            if ($prevScore !== null && $data['total_score'] < $prevScore) {
                $rank += $studentsAtRank;
                $studentsAtRank = 1;
            } else {
                $studentsAtRank++;
            }

            if ($data['id'] == $studentId) {
                $studentPosition = $rank;
                break;
            }
            $prevScore = $data['total_score'];
        }

        return $studentPosition;
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

    protected function prepareReportData(
        $studentRecord,
        $academicYearId,
        $semesterId,
        $allSubjects = null, // Pre-fetched subjects (not directly used for filtering now)
        $studentResults = null, // Pre-fetched results for this student
        $subjectOverallStats = null, // Pre-calculated subject stats
        $termReport = null, // Pre-fetched term report for this student
        $classPosition = 'N/A', // This will be the default if not provided by printClassResults
        $totalStudents = 0 // This will be the default if not provided by printClassResults
    ) {
        // Filter results for the current academic year and semester
        $rawResults = $studentResults ?? $studentRecord->results->filter(function ($result) use ($academicYearId, $semesterId) {
            return $result->academic_year_id == $academicYearId &&
                   $result->semester_id == $semesterId;
        });

        // Get unique subject IDs from the filtered results for the current student
        $subjectIdsWithResults = $rawResults->pluck('subject_id')->unique();

        // Fetch subjects corresponding to these IDs, ordered by name, ensuring uniqueness by name
        // This handles cases where a subject might be duplicated in the database with different IDs
        $fetchedSubjects = Subject::whereIn('id', $subjectIdsWithResults)->get();
        $subjects = $fetchedSubjects->unique('name')->sortBy('name');


        // Determine subject stats if not pre-calculated (i.e., for single print)
        if (empty($subjectOverallStats)) {
            $allClassResultsForStats = Result::with('subject')
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->whereIn('student_record_id', StudentRecord::where('my_class_id', $studentRecord->myClass->id)->pluck('id'))
                ->get();

            foreach ($allClassResultsForStats->groupBy('subject_id') as $subjectId => $resultsCollection) {
                $subjectOverallStats[$subjectId] = [
                    'highest' => (int)$resultsCollection->max('total_score'),
                    'lowest' => (int)$resultsCollection->min('total_score')
                ];
            }
        }
        $subjectStats = $subjectOverallStats;


        $results = $rawResults->keyBy('subject_id')->map(function ($result) {
            $ca1 = (int) $result->ca1_score;
            $ca2 = (int) $result->ca2_score;
            $ca3 = (int) $result->ca3_score;
            $ca4 = (int) $result->ca4_score;
            $exam = (int) $result->exam_score;
            $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
            $grade = $this->calculateGrade($total);
            $comment = $result->teacher_comment ?: $this->getDefaultComment($total);
            return [
                'ca1_score' => $ca1, 'ca2_score' => $ca2, 'ca3_score' => $ca3, 'ca4_score' => $ca4,
                'exam_score' => $exam, 'total_score' => $total, 'grade' => $grade,
                'comment' => $comment,
            ];
        });

        // Determine total students and class position if not pre-calculated (i.e., for single print)
        if ($totalStudents === 0 || $classPosition === 'N/A') {
            $classStudents = StudentRecord::with([
                'user',
                'results' => function ($query) use ($academicYearId, $semesterId) {
                    $query->where('academic_year_id', $academicYearId)
                        ->where('semester_id', $semesterId);
                }
            ])
            ->where('my_class_id', $studentRecord->myClass->id)
            ->where('is_graduated', false)
            ->whereHas('user', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->get();

            $totalStudents = $classStudents->count();

            $scores = $classStudents->map(function ($record) {
                return [
                    'id' => $record->id,
                    'total_score' => $record->results->sum('total_score'),
                ];
            })->sortByDesc('total_score')->values();

            $rank = 1;
            $prevScore = null;
            $studentsAtRank = 0;

            foreach ($scores as $index => $data) {
                if ($prevScore !== null && $data['total_score'] < $prevScore) {
                    $rank += $studentsAtRank;
                    $studentsAtRank = 1;
                } else {
                    $studentsAtRank++;
                }

                if ($data['id'] == $studentRecord->id) {
                    $classPosition = $rank;
                    // No need to continue loop once position is found for this student
                    break;
                }
                $prevScore = $data['total_score'];
            }
        }


        $totalSubjects = $subjects->count();
        $maxTotalScore = $totalSubjects * 100;
        $grandTotal = $rawResults->sum('total_score');
        $grandTotalTest = $rawResults->sum(fn($r) => $r->ca1_score + $r->ca2_score + $r->ca3_score + $r->ca4_score);
        $grandTotalExam = $rawResults->sum('exam_score');
        $percentage = $totalSubjects > 0 ? round(($grandTotal / $maxTotalScore) * 100, 2) : 0; // Corrected percentage calculation based on maxTotalScore
        $academicYearName = optional(AcademicYear::find($academicYearId))->name ?? 'Unknown Academic Year';
        $semesterName = Semester::find($semesterId)->name ?? 'Unknown Semester';

        $subjectsPassed = 0;
        foreach ($subjects as $subject) {
            $result = $results[$subject->id] ?? ['total_score' => 0, 'grade' => 'F9'];
            if ($result['total_score'] >= 40) {
                $subjectsPassed++;
            }
        }

        // Use pre-fetched term report or create if not exists (for single print scenario)
        $termReport = $termReport ?? TermReport::firstOrCreate([
            'student_record_id' => $studentRecord->id,
            'academic_year_id' => $academicYearId,
            'semester_id' => $semesterId,
        ]);

        // Calculate dynamic comments here
        $dynamicTeacherComment = match (true) {
            $percentage >= 80 => 'An excellent and truly outstanding performance. Keep it up!',
            $percentage >= 70 => 'Very good work this term. A commendable effort.',
            $percentage >= 60 => 'A good and consistent performance. Well done.',
            $percentage >= 50 => 'This is a satisfactory result, but there is room for improvement.',
            $percentage >= 40 => 'Shows potential but needs to apply more effort to improve.',
            default => 'An unsatisfactory performance. Requires significant improvement.',
        };
        $dynamicPrincipalComment = match (true) {
            $percentage >= 80 => 'Outstanding achievement! A model student for others to emulate.',
            $percentage >= 70 => 'A very strong performance. We are proud of your progress.',
            $percentage >= 60 => 'Good results. Continue to aim higher next term.',
            $percentage >= 50 => 'An adequate performance. Greater focus is required for better results.',
            $percentage >= 40 => 'A marginal pass. Serious improvement is required.',
            default => 'This result is below the expected standard. Urgent intervention is needed.',
        };

        // Determine final comments based on priority: manual comment first, then dynamic
        $finalTeacherComment = !empty($termReport->class_teacher_comment) && $termReport->class_teacher_comment !== 'Impressive'
                               ? $termReport->class_teacher_comment
                               : $dynamicTeacherComment;

        $finalPrincipalComment = !empty($termReport->principal_comment) && $termReport->principal_comment !== 'Keep up the good work!'
                                 ? $termReport->principal_comment
                                 : $dynamicPrincipalComment;


        // Update term report with calculated values
        $termReport->update([
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
            'totalScore' => $grandTotal,
            'subjectStats' => $subjectStats,
            'percentage' => $percentage,
            'totalStudents' => $totalStudents,
            'classPosition' => $classPosition,
            'academicYearId' => $academicYearId,
            'semesterId' => $semesterId,
            'academicYearName' => $academicYearName,
            'semesterName' => $semesterName,
            'termReport' => $termReport,
            'maxTotalScore' => $maxTotalScore,
            'totalSubjects' => $totalSubjects,
            'dynamicTeacherComment' => $dynamicTeacherComment, // Keep for reference if needed
            'dynamicPrincipalComment' => $dynamicPrincipalComment, // Keep for reference if needed
            'finalTeacherComment' => $finalTeacherComment, // Pass the determined comment
            'finalPrincipalComment' => $finalPrincipalComment, // Pass the determined comment
        ];
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
        // For single PDF generation, we don't have pre-fetched data, so call prepareReportData without them
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
            'user' => function ($query) {
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
            usort($termReports[$semester->id], function ($a, $b) {
                return $b['total_score'] <=> $a['total_score'];
            });
            foreach ($termReports[$semester->id] as $index => &$report) {
                $report['rank'] = $index + 1;
            }
            if (!empty($termReports[$semester->id])) {
                $termStats[$semester->id] = [
                    'average_percentage' => collect($termReports[$semester->id])->avg('percentage'),
                    'pass_rate' => (collect($termReports[$semester->id])->filter(fn($r) => $r['percentage'] >= 50)->count() / max(1, count($termReports[$semester->id]))) * 100,
                    'top_student' => $termReports[$semester->id][0]['student']->user->name ?? 'N/A',
                    'top_score' => $termReports[$semester->id][0]['percentage'] ?? 0,
                ];
            }
        }
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
            foreach ($semesters as $semester) {
                $termTotal = collect($termReports[$semester->id])
                    ->firstWhere('student.id', $student->id)['total_score'] ?? 0;
                $annualResult['term_totals'][$semester->id] = $termTotal;
                $annualResult['grand_total'] += $termTotal;
            }
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
            $annualResult['average_percentage'] = $stats['max_total_score'] > 0
                ? round(($annualResult['grand_total'] / $stats['max_total_score']) * 100, 2)
                : 0;
            $annualReports[] = $annualResult;
        }
        usort($annualReports, function ($a, $b) {
            return $b['grand_total'] <=> $a['grand_total'];
        });
        foreach ($annualReports as $index => &$report) {
            $report['rank'] = $index + 1;
        }
        $topSubject = collect($subjects)->map(function ($subject) use ($termReports, $semesters) {
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
        $subjectPerformance = collect($subjects)->map(function ($subject) use ($termReports) {
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
}
