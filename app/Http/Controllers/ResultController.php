<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\StudentRecord;
use App\Models\Result;

class ResultController extends Controller
{

    public function print(Request $request, $studentId)
    {
        $academicYearId = $request->academicYearId ?? $request->input('academicYearId');
        $semesterId = $request->semesterId ?? $request->input('semesterId');

        $studentRecord = StudentRecord::with(['user', 'myClass', 'section'])
            ->findOrFail($studentId);

        // Fetch all results for this student, academic year and semester
        $rawResults = Result::where([
            'student_record_id' => $studentId,
            'academic_year_id' => $academicYearId,
            'semester_id' => $semesterId,
        ])->get();

        // Prepare results with grades and comments
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
                'A1' => 'Outstanding! Keep up the brilliance ✨',
                'B2' => 'Excellent work! You’re almost at the top 💪',
                'B3' => 'Very good! Stay consistent 🔥',
                'C4' => 'Good effort, room for improvement 👍',
                'C5' => 'You did well. Keep aiming higher 🌱',
                'C6' => 'Satisfactory. Try to do better next time 📈',
                'D7' => 'Passable, but improvement is needed ⏳',
                'E8' => 'Weak performance. More effort required ⚠️',
                default => 'Failing grade. Needs urgent attention 🚨',
            };

            return [
                'test_score' => $test,
                'exam_score' => $exam,
                'total_score' => $total,
                'grade' => $grade,
                'comment' => $result->teacher_comment ?: $comment,
            ];
        });

        // Subjects only from the results
        $subjects = Subject::whereIn('id', array_keys($results->toArray()))->get();

        $totalSubjects = $subjects->count();
        $maxTotalScore = $totalSubjects * 100; // 100 per subject


        // Totals and percentage
        $grandTotal = $rawResults->sum('total_score');
        $grandTotalTest = $rawResults->sum('test_score');
        $grandTotalExam = $rawResults->sum('exam_score');
        $percentage = $results->count() ? round($grandTotal / $results->count(), 2) : 0;
        $principalComment = 'Keep up the good work';

        // Academic year and semester name
        $academicYearName = AcademicYear::find($academicYearId)->name ?? 'Unknown Academic Year';
        $semesterName = Semester::find($semesterId)->name ?? 'Unknown Semester';

        // Class Position
        $totalStudents = StudentRecord::where('my_class_id', $studentRecord->myClass->id)->count();
        $classPosition = $this->calculatePosition($studentRecord, $academicYearId, $semesterId);

        // Calculate subject pass/fail
        $totalScore = 0;
        $subjectsPassed = 0;

        foreach ($subjects as $subject) {
            $result = $results[$subject->id] ?? ['total_score' => 0, 'grade' => 'F9'];
            $totalScore += $result['total_score'];

            if ($result['total_score'] >= 40) {
                $subjectsPassed++;
            }
        }

        return view('pages.result.print', compact(
            'studentRecord',
            'subjects',
            'results',
            'grandTotal',
            'grandTotalTest',
            'grandTotalExam',
            'subjectsPassed',
            'totalScore',
            'percentage',
            'principalComment',
            'totalStudents',
            'classPosition',
            'academicYearId',
            'semesterId',
            'academicYearName',
            'semesterName',
            'maxTotalScore', // ✅ New
            'totalSubjects'  // ✅ Optional if needed in the view
        ));
    }

    protected function calculatePosition($studentRecord, $academicYearId, $semesterId)
    {
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
}
