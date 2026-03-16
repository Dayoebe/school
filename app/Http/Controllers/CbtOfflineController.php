<?php

namespace App\Http\Controllers;

use App\Models\Assessment\Assessment;
use App\Models\Assessment\AttemptSession;
use App\Models\Assessment\StudentAnswer;
use App\Support\Cbt\CbtExamPayloadBuilder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CbtOfflineController extends Controller
{
    public function package(Assessment $assessment, CbtExamPayloadBuilder $payloadBuilder): JsonResponse
    {
        $user = Auth::user();

        abort_unless($user !== null && $user->hasRole('student'), 403);

        $assessment = Assessment::query()
            ->availableForStudentExamAccess($user)
            ->with([
                'questions',
                'course',
                'lesson',
                'studentLocks' => fn ($query) => $query
                    ->select('id', 'assessment_id', 'user_id')
                    ->where('user_id', (int) $user->id),
                'studentAnswers' => fn ($query) => $query
                    ->select(
                        'id',
                        'assessment_id',
                        'user_id',
                        'attempt_number',
                        'submitted_at'
                    )
                    ->where('user_id', (int) $user->id),
                'attemptSessions' => fn ($query) => $query
                    ->select(
                        'id',
                        'assessment_id',
                        'user_id',
                        'attempt_number',
                        'status',
                        'started_at',
                        'expires_at'
                    )
                    ->where('user_id', (int) $user->id),
            ])
            ->findOrFail($assessment->id);

        if ($assessment->questions->isEmpty()) {
            return response()->json([
                'message' => 'This assessment has no questions yet.',
            ], 422);
        }

        $userId = (int) $user->id;
        $activeSession = $assessment->getActiveAttemptSession($userId);

        if ($activeSession && $activeSession->isExpired()) {
            $activeSession = null;
        }

        [$canTake, $message] = $assessment->canUserTakeAssessment($userId);

        if (!$canTake && !$activeSession) {
            return response()->json([
                'message' => $message ?: 'This CBT exam is not currently available.',
            ], 422);
        }

        $attemptNumber = $activeSession
            ? (int) $activeSession->attempt_number
            : (int) $assessment->getNextAttemptNumber($userId);

        $durationSeconds = max(60, (int) $assessment->estimated_duration_minutes * 60);
        $timeRemaining = $activeSession
            ? max(0, now()->diffInSeconds($activeSession->expires_at, false))
            : $durationSeconds;

        return response()->json([
            'assessment' => [
                'id' => (int) $assessment->id,
                'title' => $assessment->title,
                'description' => $assessment->description,
                'pass_percentage' => (float) $assessment->pass_percentage,
                'duration_seconds' => $durationSeconds,
                'formatted_duration' => $assessment->formatted_duration,
                'class_name' => $assessment->course?->name ?? 'Not assigned',
                'subject_name' => $assessment->lesson?->name ?? 'General CBT',
            ],
            'attempt' => [
                'attempt_number' => $attemptNumber,
                'attempt_session_id' => $activeSession?->id,
                'started_at' => $activeSession?->started_at?->toIso8601String(),
                'time_remaining' => $timeRemaining,
                'current_question_index' => (int) ($activeSession?->current_question_index ?? 0),
                'answers' => is_array($activeSession?->answers_snapshot) ? $activeSession->answers_snapshot : [],
                'flagged_question_ids' => is_array($activeSession?->flagged_question_ids) ? $activeSession->flagged_question_ids : [],
                'security_violations' => is_array($activeSession?->security_violations) ? $activeSession->security_violations : [],
            ],
            'offline' => [
                'downloaded_at' => now()->toIso8601String(),
                'package_version' => 1,
                'launch_url' => url('/cbt-recovery.html?assessment=' . $assessment->id),
                'selection_url' => route('cbt.exams'),
                'sync_url' => route('cbt.offline.sync'),
                'viewer_url' => route('cbt.viewer'),
            ],
            'questions' => $payloadBuilder->build($assessment, $userId, $attemptNumber),
        ]);
    }

    public function sync(Request $request, CbtExamPayloadBuilder $payloadBuilder): JsonResponse
    {
        $user = Auth::user();

        abort_unless($user !== null && $user->hasRole('student'), 403);

        $validated = $request->validate([
            'assessment_id' => ['required', 'integer'],
            'attempt_number' => ['required', 'integer', 'min:1'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'current_question_index' => ['nullable', 'integer', 'min:0'],
            'time_remaining' => ['nullable', 'integer', 'min:0'],
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'integer'],
            'flagged_question_ids' => ['nullable', 'array'],
            'flagged_question_ids.*' => ['integer'],
            'security_violations' => ['nullable', 'array'],
            'question_durations' => ['nullable', 'array'],
            'question_durations.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $assessment = Assessment::query()
            ->visibleToUser($user)
            ->with('questions')
            ->findOrFail((int) $validated['assessment_id']);

        $durationSeconds = max(60, (int) $assessment->estimated_duration_minutes * 60);
        $attemptNumber = (int) $validated['attempt_number'];
        $userId = (int) $user->id;

        $questionPayload = collect($payloadBuilder->build($assessment, $userId, $attemptNumber))
            ->keyBy(fn (array $question): int => (int) $question['id']);

        $answers = collect($validated['answers'] ?? [])
            ->mapWithKeys(fn ($answer, $questionId): array => [(int) $questionId => $answer])
            ->all();

        $flaggedQuestionIds = array_values(array_map('intval', $validated['flagged_question_ids'] ?? []));
        $questionDurations = collect($validated['question_durations'] ?? [])
            ->mapWithKeys(fn ($duration, $questionId): array => [(int) $questionId => (int) round((float) $duration)])
            ->all();

        $existingSubmission = StudentAnswer::query()
            ->where('assessment_id', $assessment->id)
            ->where('user_id', $userId)
            ->where('attempt_number', $attemptNumber)
            ->whereNotNull('submitted_at')
            ->exists();

        if ($existingSubmission) {
            return response()->json([
                'message' => 'This CBT attempt was already synced.',
                'already_synced' => true,
            ]);
        }

        $startedAt = isset($validated['started_at'])
            ? Carbon::parse($validated['started_at'])
            : now();
        $completedAt = isset($validated['completed_at'])
            ? Carbon::parse($validated['completed_at'])
            : now();

        if ($completedAt->lessThan($startedAt)) {
            $completedAt = $startedAt->copy();
        }

        $expiresAt = $startedAt->copy()->addSeconds($durationSeconds);

        $summary = DB::transaction(function () use (
            $assessment,
            $attemptNumber,
            $answers,
            $flaggedQuestionIds,
            $questionDurations,
            $questionPayload,
            $validated,
            $userId,
            $user,
            $payloadBuilder,
            $startedAt,
            $completedAt,
            $expiresAt
        ): array {
            $session = AttemptSession::query()
                ->where('assessment_id', $assessment->id)
                ->where('user_id', $userId)
                ->where('attempt_number', $attemptNumber)
                ->first();

            if (!$session) {
                $session = AttemptSession::create([
                    'assessment_id' => $assessment->id,
                    'user_id' => $userId,
                    'school_id' => (int) (auth()->user()?->school_id ?? 0),
                    'attempt_number' => $attemptNumber,
                    'current_question_index' => (int) ($validated['current_question_index'] ?? 0),
                    'status' => 'submitted',
                    'started_at' => $startedAt,
                    'expires_at' => $expiresAt,
                    'completed_at' => $completedAt,
                    'last_activity_at' => $completedAt,
                    'question_order' => $questionPayload->pluck('id')->values()->all(),
                    'answers_snapshot' => $answers,
                    'flagged_question_ids' => $flaggedQuestionIds,
                    'security_violations' => $validated['security_violations'] ?? [],
                    'ip_address' => request()->ip(),
                    'user_agent' => (string) request()->userAgent(),
                ]);
            } else {
                $session->update([
                    'current_question_index' => (int) ($validated['current_question_index'] ?? $session->current_question_index ?? 0),
                    'status' => 'submitted',
                    'completed_at' => $completedAt,
                    'last_activity_at' => $completedAt,
                    'question_order' => $questionPayload->pluck('id')->values()->all(),
                    'answers_snapshot' => $answers,
                    'flagged_question_ids' => $flaggedQuestionIds,
                    'security_violations' => $validated['security_violations'] ?? [],
                    'started_at' => $session->started_at ?? $startedAt,
                    'expires_at' => $session->expires_at ?? $expiresAt,
                ]);
            }

            $submittedAt = $completedAt->copy();
            $totalPoints = 0;
            $correctAnswers = 0;
            $answeredQuestions = 0;

            foreach ($questionPayload as $questionId => $questionData) {
                if (!array_key_exists((int) $questionId, $answers)) {
                    continue;
                }

                $displayAnswer = $answers[(int) $questionId];

                if ($displayAnswer === null || $displayAnswer === '' || $displayAnswer === 'null') {
                    continue;
                }

                $answeredQuestions++;
                $originalAnswer = $payloadBuilder->mapDisplayToOriginalAnswer($questionData, (int) $displayAnswer);

                $studentAnswer = StudentAnswer::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'assessment_id' => $assessment->id,
                        'question_id' => (int) $questionId,
                        'attempt_number' => $attemptNumber,
                    ],
                    [
                        'answer' => $originalAnswer,
                        'time_spent_seconds' => (int) ($questionDurations[(int) $questionId] ?? 0),
                        'submitted_at' => $submittedAt,
                        'question_order' => $questionPayload->pluck('id')->values()->all(),
                        'exam_data' => [
                            'recovery_copy' => true,
                            'was_shuffled' => $questionData['was_shuffled'] ?? false,
                            'user_displayed_answer' => (int) $displayAnswer,
                            'user_original_answer' => $originalAnswer,
                            'display_to_original_map' => $questionData['display_to_original_map'] ?? null,
                        ],
                    ]
                );

                if ($studentAnswer->autoGrade()) {
                    $studentAnswer->refresh();

                    if ($studentAnswer->is_correct) {
                        $correctAnswers++;
                    }

                    $totalPoints += (float) ($studentAnswer->points_earned ?? 0);
                }
            }

            $maxPoints = (float) $assessment->questions->sum('points');
            $percentage = $maxPoints > 0
                ? round(($totalPoints / $maxPoints) * 100, 1)
                : 0.0;

            return [
                'total_questions' => $questionPayload->count(),
                'answered_questions' => $answeredQuestions,
                'correct_answers' => $correctAnswers,
                'total_points' => $totalPoints,
                'max_points' => $maxPoints,
                'percentage' => $percentage,
                'passed' => $percentage >= (float) $assessment->pass_percentage,
                'attempt_number' => $attemptNumber,
                'results_visible' => $assessment->canUserViewResults($user),
                'pending_publish' => !$assessment->canUserViewResults($user),
            ];
        });

        return response()->json([
            'message' => 'CBT attempt synced successfully.',
            'already_synced' => false,
            'results' => $summary,
        ]);
    }
}
