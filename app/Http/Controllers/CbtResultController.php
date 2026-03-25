<?php

namespace App\Http\Controllers;

use App\Models\Assessment\Assessment;
use App\Models\User;
use App\Traits\RestrictsTeacherCbtManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CbtResultController extends Controller
{
    use RestrictsTeacherCbtManagement;

    public function print(Request $request, Assessment $assessment, User $student, int $attemptNumber)
    {
        $viewer = $request->user();

        abort_unless($viewer !== null, 403);
        abort_unless(
            !$viewer->hasRole('teacher') || $viewer->hasAnyRole(['super-admin', 'super_admin', 'principal', 'admin']),
            403
        );
        abort_unless($this->currentUserCanManageCbtAssessment($assessment->id, $viewer), 403);
        abort_unless($student->school_id === $viewer->school_id && $student->hasRole('student'), 404);

        $attempt = $assessment->getStudentResults($student->id, $attemptNumber);
        abort_unless($attempt !== null, 404);

        $assessment->loadMissing(['course.classGroup.school', 'lesson', 'questions']);

        return view('cbt.print-result', [
            'assessment' => $assessment,
            'attempt' => $attempt,
            'school' => $assessment->course?->classGroup?->school ?: $viewer->school,
            'student' => $student,
        ]);
    }

    public function printClassResults(Request $request, Assessment $assessment)
    {
        return $this->printAssessmentSummary($request, $assessment, 'class');
    }

    public function printSubjectResults(Request $request, Assessment $assessment)
    {
        return $this->printAssessmentSummary($request, $assessment, 'subject');
    }

    protected function printAssessmentSummary(Request $request, Assessment $assessment, string $reportType)
    {
        $viewer = $request->user();

        abort_unless($viewer !== null, 403);

        $assessment->loadMissing([
            'course.classGroup.school',
            'course.teachers:id,name',
            'lesson',
            'questions',
            'studentLocks',
            'studentAnswers' => function ($query) use ($viewer) {
                $query->whereNotNull('submitted_at')
                    ->whereHas('user', function ($userQuery) use ($viewer) {
                        $userQuery->where('school_id', $viewer->school_id);
                    })
                    ->with(['user.studentRecord', 'question'])
                    ->orderBy('user_id')
                    ->orderByDesc('attempt_number');
            },
        ]);

        $this->authorizeAssessmentSummaryPrint($assessment, $viewer);

        $rows = $this->buildAssessmentSummaryRows($assessment, $viewer);
        $submittedRows = $rows->filter(fn (array $row) => $row['has_submission'])->values();
        $averagePercentage = $submittedRows->avg(fn (array $row) => (float) data_get($row, 'best_attempt.percentage', 0));
        $school = $assessment->course?->classGroup?->school ?: $viewer->school;

        return view('cbt.print-summary', [
            'assessment' => $assessment,
            'school' => $school,
            'viewer' => $viewer,
            'rows' => $rows,
            'submittedRows' => $submittedRows,
            'reportType' => $reportType,
            'metrics' => [
                'students_in_class' => $rows->count(),
                'submitted_count' => $submittedRows->count(),
                'not_submitted_count' => max(0, $rows->count() - $submittedRows->count()),
                'pass_count' => $submittedRows->where('best_attempt.passed', true)->count(),
                'fail_count' => $submittedRows->where('best_attempt.passed', false)->count(),
                'average_percentage' => $submittedRows->isEmpty() ? 0 : round((float) $averagePercentage, 1),
                'top_percentage' => $submittedRows->max(fn (array $row) => (float) data_get($row, 'best_attempt.percentage', 0)) ?? 0,
                'participation_rate' => $rows->isEmpty()
                    ? 0
                    : round(($submittedRows->count() / max(1, $rows->count())) * 100, 1),
            ],
        ]);
    }

    protected function authorizeAssessmentSummaryPrint(Assessment $assessment, User $viewer): void
    {
        $course = $assessment->course;
        $schoolId = $course?->classGroup?->school_id;

        abort_unless($course !== null && $schoolId !== null, 404);
        abort_unless((int) $viewer->school_id === (int) $schoolId, 404);

        $isSuperAdmin = $viewer->hasAnyRole(['super-admin', 'super_admin']);
        $isClassTeacher = $course->relationLoaded('teachers')
            ? $course->teachers->contains(fn (User $teacher) => (int) $teacher->id === (int) $viewer->id)
            : $course->hasTeacher($viewer->id);

        abort_unless($isSuperAdmin || $isClassTeacher, 403);
    }

    protected function buildAssessmentSummaryRows(Assessment $assessment, User $viewer): Collection
    {
        $answersByUser = $assessment->studentAnswers
            ->filter(fn ($answer) => $answer->user !== null)
            ->groupBy('user_id');

        $eligibleStudents = $this->eligibleStudentsForAssessment($assessment, $viewer)->keyBy('id');
        $attemptedStudents = $assessment->studentAnswers
            ->filter(fn ($answer) => $answer->user !== null)
            ->pluck('user')
            ->filter()
            ->keyBy('id');
        $students = $eligibleStudents
            ->merge($attemptedStudents)
            ->keyBy('id');
        $lockedUserIds = $assessment->studentLocks
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        $rows = $students
            ->map(function (User $student) use ($assessment, $answersByUser, $eligibleStudents, $lockedUserIds) {
                $attempts = $this->summarizeAttempts(
                    $answersByUser->get($student->id, collect()),
                    $assessment
                );
                $bestAttempt = $this->resolveBestAttempt($attempts);
                $latestAttempt = $attempts->sortByDesc('attempt_number')->first();
                $studentRecord = $student->studentRecord;
                $isEligible = !$lockedUserIds->has((int) $student->id);

                return [
                    'student' => $student,
                    'student_id' => (int) $student->id,
                    'admission_number' => $studentRecord?->admission_number,
                    'attempts' => $attempts,
                    'attempt_count' => $attempts->count(),
                    'best_attempt' => $bestAttempt,
                    'latest_attempt' => $latestAttempt,
                    'has_submission' => $attempts->isNotEmpty(),
                    'is_eligible' => $isEligible,
                    'was_expected' => $eligibleStudents->has($student->id),
                ];
            })
            ->sort(function (array $left, array $right) {
                if ($left['has_submission'] !== $right['has_submission']) {
                    return $left['has_submission'] ? -1 : 1;
                }

                $leftPercentage = (float) data_get($left, 'best_attempt.percentage', 0);
                $rightPercentage = (float) data_get($right, 'best_attempt.percentage', 0);

                if ($leftPercentage !== $rightPercentage) {
                    return $rightPercentage <=> $leftPercentage;
                }

                $leftPoints = (float) data_get($left, 'best_attempt.total_points', 0);
                $rightPoints = (float) data_get($right, 'best_attempt.total_points', 0);

                if ($leftPoints !== $rightPoints) {
                    return $rightPoints <=> $leftPoints;
                }

                return strcasecmp($left['student']->name, $right['student']->name);
            })
            ->values();

        $rank = 0;

        return $rows->map(function (array $row) use (&$rank) {
            $row['rank'] = $row['has_submission'] ? ++$rank : null;

            return $row;
        });
    }

    protected function summarizeAttempts(Collection $answers, Assessment $assessment): Collection
    {
        return $answers
            ->groupBy('attempt_number')
            ->map(function (Collection $attemptAnswers, $attemptNumber) use ($assessment) {
                $totalPoints = (float) $attemptAnswers->sum('points_earned');
                $maxPoints = (float) $attemptAnswers->sum(function ($answer) {
                    return $answer->question?->points ?? 0;
                });
                $percentage = $maxPoints > 0 ? round(($totalPoints / $maxPoints) * 100, 1) : 0;

                return [
                    'attempt_number' => (int) $attemptNumber,
                    'total_points' => $totalPoints,
                    'max_points' => $maxPoints > 0 ? $maxPoints : (float) $assessment->questions->sum('points'),
                    'percentage' => $percentage,
                    'passed' => $percentage >= $assessment->pass_percentage,
                    'submitted_at' => $attemptAnswers->sortByDesc('submitted_at')->first()?->submitted_at,
                ];
            })
            ->sortByDesc('attempt_number')
            ->values();
    }

    protected function resolveBestAttempt(Collection $attempts): ?array
    {
        if ($attempts->isEmpty()) {
            return null;
        }

        return $attempts
            ->sort(function (array $left, array $right) {
                if ($left['percentage'] !== $right['percentage']) {
                    return $right['percentage'] <=> $left['percentage'];
                }

                if ($left['total_points'] !== $right['total_points']) {
                    return $right['total_points'] <=> $left['total_points'];
                }

                return $right['attempt_number'] <=> $left['attempt_number'];
            })
            ->first();
    }

    protected function eligibleStudentsForAssessment(Assessment $assessment, User $viewer): Collection
    {
        $course = $assessment->course;
        $academicYearId = $viewer->school?->academic_year_id;

        if ($course === null || !$academicYearId) {
            return collect();
        }

        $students = $assessment->section_id
            ? $course->studentsForAcademicYearAndSection($academicYearId, $assessment->section_id)
            : $course->studentsForAcademicYear($academicYearId);

        return $students
            ->filter(function ($student) use ($viewer) {
                return (int) $student->school_id === (int) $viewer->school_id
                    && !($student->studentRecord?->is_graduated ?? false);
            })
            ->sortBy('name')
            ->values();
    }
}
