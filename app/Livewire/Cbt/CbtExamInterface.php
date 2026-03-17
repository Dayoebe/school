<?php

namespace App\Livewire\Cbt;

use App\Models\Assessment\Assessment;
use App\Models\Assessment\AttemptSession;
use App\Models\Assessment\StudentAnswer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;

#[Layout('layouts.exam', ['title' => 'CBT Exam', 'description' => 'Secure CBT exam interface'])]
class CbtExamInterface extends Component
{
    public $assessment;
    public $questions;
    public $currentQuestionIndex = 0;
    public $answers = [];
    public $timeRemaining;
    public $examStarted = false;
    public $examCompleted = false;
    public $results = null;
    public $flaggedQuestions = [];
    public $attemptNumber;
    public $startTime;
    public $securityViolations = [];
    public $isFullscreenForced = false;
    public $questionOrder = [];
    public ?int $attemptSessionId = null;
    public bool $resumedAttempt = false;
    public bool $resultsVisible = true;
    public bool $pendingPublish = false;
    public string $resultNotice = '';
    public string $resumeBanner = '';

    public $progressTracking = [
        'question_start_times' => [],
        'question_end_times' => [],
        'question_durations' => [],
        'total_active_time' => 0,
        'pause_count' => 0,
        'navigation_count' => 0,
    ];

    public function mount($assessment)
    {
        $currentUserId = (int) Auth::id();

        $this->assessment = $this->assessmentsForCurrentSchool()
            ->with([
                'questions',
                'studentLocks',
                'studentAnswers' => fn ($query) => $query->where('user_id', $currentUserId),
                'attemptSessions' => fn ($query) => $query->where('user_id', $currentUserId),
            ])
            ->find($assessment);

        if (!$this->assessment || $this->assessment->questions->count() === 0) {
            session()->flash('error', 'Invalid assessment.');
            return redirect()->route('cbt.exams');
        }

        if ($this->isCurrentStudentLocked()) {
            session()->flash('warning', $this->currentStudentLockMessage());
            return redirect()->route('cbt.exams');
        }

        if (!$this->isAssessmentPublished()) {
            session()->flash('warning', $this->unpublishedAssessmentMessage());
            return redirect()->route('cbt.exams');
        }

        $activeSession = $this->assessment->getActiveAttemptSession($currentUserId);
        if ($activeSession) {
            if ($activeSession->isExpired()) {
                $this->attemptNumber = (int) $activeSession->attempt_number;
                $this->loadQuestions();
                $this->initializeExam();
                $this->restoreAttemptSnapshot($activeSession);
                $this->submitExam(true, $activeSession);
                session()->flash('warning', 'Your previous attempt expired and was auto-submitted.');
                return redirect()->route('cbt.viewer');
            }

            $this->attemptNumber = (int) $activeSession->attempt_number;
            $this->loadQuestions();
            $this->initializeExam();
            $this->restoreAttemptSnapshot($activeSession);
            $this->activateExamSession($activeSession);
            $this->resumedAttempt = true;
            $this->resumeBanner = 'Resumed your in-progress attempt.';
            return;
        }

        [$canTake, $message] = $this->assessment->canUserTakeAssessment(Auth::id());
        if (!$canTake) {
            session()->flash('error', $message);
            return redirect()->route('cbt.exams');
        }

        $this->attemptNumber = $this->assessment->getNextAttemptNumber(Auth::id());
        $this->loadQuestions();
        $this->initializeExam();

        if (request()->boolean('autostart')) {
            try {
                $session = $this->createAttemptSession();
                $this->activateExamSession($session);
            } catch (\Throwable $e) {
                report($e);
                session()->flash('error', $this->resolveStartExamErrorMessage($e));
                return redirect()->route('cbt.exam.take', ['assessment' => $this->assessment->id]);
            }
        }
    }

    public function render()
    {
        return view('livewire.cbt.cbt-exam-interface');
    }

    protected function loadQuestions(): void
    {
        $questionCollection = $this->assessment->questions;

        if ($this->assessment->shuffle_questions) {
            $questionCollection = $questionCollection
                ->sortBy(fn ($question) => $this->deterministicHash('question-order', (int) $question->id))
                ->values();
        }

        $this->questions = $questionCollection->map(function ($question) {
            $q = $question->toArray();

            $q['options'] = is_string($q['options'] ?? null)
                ? json_decode($q['options'], true) ?? []
                : ($q['options'] ?? []);

            $correctAnswers = $q['correct_answers'] ?? $question->correct_answers;
            if (is_string($correctAnswers)) {
                $correctAnswers = json_decode($correctAnswers, true) ?? [$correctAnswers];
            } elseif (!is_array($correctAnswers)) {
                $correctAnswers = [$correctAnswers];
            }
            $q['original_correct_answers'] = array_map('intval', $correctAnswers);

            if (
                $this->assessment->shuffle_options
                && !empty($q['options'])
                && $q['question_type'] === 'multiple_choice'
            ) {
                $indexed = [];
                foreach ($q['options'] as $idx => $text) {
                    $indexed[] = [
                        'orig_idx' => (int) $idx,
                        'text' => $text,
                        'weight' => $this->deterministicHash('option-order:' . $q['id'], (int) $idx),
                    ];
                }

                usort($indexed, fn ($a, $b) => strcmp($a['weight'], $b['weight']));

                $newOptions = [];
                $displayToOriginal = [];
                foreach ($indexed as $newIdx => $item) {
                    $newOptions[$newIdx] = $item['text'];
                    $displayToOriginal[$newIdx] = $item['orig_idx'];
                }

                $q['options'] = $newOptions;
                $q['display_to_original_map'] = $displayToOriginal;
                $q['was_shuffled'] = true;
            } else {
                $q['display_to_original_map'] = null;
                $q['was_shuffled'] = false;
            }

            if ($q['question_type'] === 'true_false') {
                $q['original_correct_answers'] = array_map('intval', $q['original_correct_answers']);
            }

            $q['points'] = floatval($q['points'] ?? 1);
            return $q;
        })->values()->toArray();

        $this->questionOrder = collect($this->questions)->pluck('id')->toArray();
    }

    protected function initializeExam(): void
    {
        $this->timeRemaining = max(60, (int) $this->assessment->estimated_duration_minutes * 60);
        foreach ($this->questions as $question) {
            $this->answers[$question['id']] = null;
        }
    }

    public function startExam(): void
    {
        if ($this->isCurrentStudentLocked(true)) {
            $message = $this->currentStudentLockMessage();
            $this->dispatch('exam-start-feedback', state: 'warning', message: $message);
            session()->flash('warning', $message);
            return;
        }

        if (!$this->isAssessmentPublished(true)) {
            $message = $this->unpublishedAssessmentMessage();
            $this->dispatch('exam-start-feedback', state: 'warning', message: $message);
            session()->flash('warning', $message);
            return;
        }

        if ($this->examCompleted) {
            $this->dispatch('exam-start-feedback', state: 'warning', message: 'This attempt is already completed.');
            return;
        }

        if ($this->examStarted && $this->attemptSessionId) {
            $this->dispatch('exam-start-feedback', state: 'info', message: 'Exam is already running.');
            return;
        }

        try {
            $session = $this->createAttemptSession();
            $this->activateExamSession($session);
            $this->dispatch('exam-start-feedback', state: 'success', message: 'Exam started successfully. Timer is now running.');
            $this->dispatch('startTimer');
        } catch (\Throwable $e) {
            report($e);
            $message = $this->resolveStartExamErrorMessage($e);
            $this->dispatch('exam-start-feedback', state: 'error', message: $message);
            session()->flash('error', $message);
        }
    }

    #[Renderless]
    public function saveAnswer($questionId, $answer): void
    {
        if ($answer === null || $answer === '' || $answer === 'null') {
            $this->answers[$questionId] = null;
        } else {
            $this->answers[$questionId] = (int) $answer;
        }

        $this->persistCurrentAnswer((int) $questionId);
        $this->persistAttemptSessionState();
    }

    #[Renderless]
    public function syncAnswers(array $answers): void
    {
        foreach ($this->answers as $questionId => $currentValue) {
            $incomingAnswer = $answers[$questionId] ?? $answers[(string) $questionId] ?? null;

            if ($incomingAnswer === null || $incomingAnswer === '' || $incomingAnswer === 'null') {
                $this->answers[$questionId] = null;
                continue;
            }

            $this->answers[$questionId] = (int) $incomingAnswer;
        }

        $this->persistAttemptSessionState();
    }

    public function submitExamWithAnswers(array $answers): void
    {
        $this->syncAnswers($answers);
        $this->submitExam();
    }

    public function heartbeat(?int $clientTimeRemaining = null): array
    {
        if (!$this->examStarted || $this->examCompleted) {
            $this->skipRender();

            return [
                'time_remaining' => (int) $this->timeRemaining,
                'exam_completed' => (bool) $this->examCompleted,
            ];
        }

        if ($this->isCurrentStudentLocked(true)) {
            session()->flash('warning', $this->currentStudentLockMessage());
            $this->skipRender();

            return [
                'time_remaining' => (int) $this->timeRemaining,
                'exam_completed' => (bool) $this->examCompleted,
                'redirect' => route('cbt.exams'),
            ];
        }

        if (!$this->isAssessmentPublished(true)) {
            session()->flash('warning', $this->unpublishedAssessmentMessage());
            $this->skipRender();

            return [
                'time_remaining' => (int) $this->timeRemaining,
                'exam_completed' => (bool) $this->examCompleted,
                'redirect' => route('cbt.exams'),
            ];
        }

        $session = $this->currentAttemptSession();
        if (!$session) {
            $this->skipRender();

            return [
                'time_remaining' => (int) $this->timeRemaining,
                'exam_completed' => (bool) $this->examCompleted,
                'reload' => true,
            ];
        }

        $remaining = max(0, now()->diffInSeconds($session->expires_at, false));
        $this->timeRemaining = $remaining;

        $session->update([
            'last_activity_at' => now(),
            'current_question_index' => (int) $this->currentQuestionIndex,
            'answers_snapshot' => $this->answers,
            'flagged_question_ids' => array_values($this->flaggedQuestions),
        ]);

        if ($remaining <= 0) {
            $this->submitExam(true, $session);
            return [
                'time_remaining' => 0,
                'exam_completed' => true,
                'auto_submitted' => true,
            ];
        }

        $this->skipRender();

        return [
            'time_remaining' => $remaining,
            'exam_completed' => (bool) $this->examCompleted,
        ];
    }

    public function submitExam(bool $isAutoSubmit = false, ?AttemptSession $attemptSession = null): void
    {
        if ($this->examCompleted) {
            return;
        }

        try {
            $this->trackQuestionTime($this->currentQuestionIndex);
            $session = $attemptSession ?: $this->currentAttemptSession();
            $this->mergeAnswersFromSession($session);

            $timeSpent = $this->startTime ? abs($this->startTime->diffInSeconds(now(), false)) : 0;

            $submissionSummary = DB::transaction(function () use ($session, $isAutoSubmit) {
                $totalPoints = 0;
                $correctAnswers = 0;
                $answeredQuestions = 0;
                $submittedAt = now();

                foreach ($this->questions as $questionIndex => $qData) {
                    $qId = (int) $qData['id'];
                    $userDisplayAnswer = $this->answers[$qId] ?? null;

                    if ($userDisplayAnswer === null || $userDisplayAnswer === '') {
                        continue;
                    }

                    $answeredQuestions++;
                    $userOriginalAnswer = $this->mapDisplayToOriginalAnswer($qData, (int) $userDisplayAnswer);

                    $examData = [
                        'was_shuffled' => $qData['was_shuffled'],
                        'user_displayed_answer' => (int) $userDisplayAnswer,
                        'user_original_answer' => $userOriginalAnswer,
                        'display_to_original_map' => $qData['display_to_original_map'],
                        'original_correct_answers' => $qData['original_correct_answers'],
                    ];

                    $questionDurationSeconds = (int) round($this->progressTracking['question_durations'][$questionIndex] ?? 0);

                    $studentAnswer = StudentAnswer::updateOrCreate(
                        [
                            'user_id' => Auth::id(),
                            'assessment_id' => $this->assessment->id,
                            'question_id' => $qId,
                            'attempt_number' => $this->attemptNumber,
                        ],
                        [
                            'answer' => $userOriginalAnswer,
                            'time_spent_seconds' => $questionDurationSeconds,
                            'submitted_at' => $submittedAt,
                            'question_order' => $this->questionOrder,
                            'exam_data' => $examData,
                        ]
                    );

                    if ($studentAnswer->autoGrade()) {
                        $studentAnswer->refresh();
                        if ($studentAnswer->is_correct) {
                            $correctAnswers++;
                        }
                        $totalPoints += $studentAnswer->points_earned ?? 0;
                    }
                }

                if ($session) {
                    $session->update([
                        'status' => $isAutoSubmit ? 'expired' : 'submitted',
                        'completed_at' => now(),
                        'last_activity_at' => now(),
                        'current_question_index' => (int) $this->currentQuestionIndex,
                        'answers_snapshot' => $this->answers,
                        'flagged_question_ids' => array_values($this->flaggedQuestions),
                        'security_violations' => $this->securityViolations,
                    ]);
                }

                return [
                    'total_points' => $totalPoints,
                    'correct_answers' => $correctAnswers,
                    'answered_questions' => $answeredQuestions,
                ];
            });

            $maxPoints = collect($this->questions)->sum('points') ?: 100;
            $percentage = $maxPoints > 0
                ? round(($submissionSummary['total_points'] / $maxPoints) * 100, 1)
                : 0;

            $this->results = [
                'total_questions' => count($this->questions),
                'answered_questions' => $submissionSummary['answered_questions'],
                'correct_answers' => $submissionSummary['correct_answers'],
                'total_points' => $submissionSummary['total_points'],
                'max_points' => $maxPoints,
                'percentage' => $percentage,
                'passed' => $percentage >= $this->assessment->pass_percentage,
                'attempt_number' => $this->attemptNumber,
                'time_spent' => $timeSpent,
            ];

            $this->resultsVisible = $this->assessment->canUserViewResults(Auth::user());
            $this->pendingPublish = !$this->resultsVisible;
            $this->resultNotice = $this->pendingPublish
                ? 'Exam submitted. Your school will publish the result when ready.'
                : '';

            $this->examCompleted = true;
            $this->dispatch('examCompleted');
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Failed to submit exam.');
        }
    }

    #[Renderless]
    public function nextQuestion(): void
    {
        if ($this->currentQuestionIndex < count($this->questions) - 1) {
            $this->trackQuestionTime($this->currentQuestionIndex);
            $previousIndex = $this->currentQuestionIndex;
            $this->currentQuestionIndex++;
            $this->progressTracking['navigation_count']++;
            $this->startQuestionTimer();
            $this->persistAttemptSessionState();

            $this->dispatch('questionChanged', previousIndex: $previousIndex, currentIndex: $this->currentQuestionIndex);
        }
    }

    #[Renderless]
    public function previousQuestion(): void
    {
        if ($this->currentQuestionIndex > 0) {
            $this->trackQuestionTime($this->currentQuestionIndex);
            $previousIndex = $this->currentQuestionIndex;
            $this->currentQuestionIndex--;
            $this->progressTracking['navigation_count']++;
            $this->startQuestionTimer();
            $this->persistAttemptSessionState();

            $this->dispatch('questionChanged', previousIndex: $previousIndex, currentIndex: $this->currentQuestionIndex);
        }
    }

    #[Renderless]
    public function goToQuestion($index): void
    {
        if ($index >= 0 && $index < count($this->questions)) {
            $this->trackQuestionTime($this->currentQuestionIndex);
            $previousIndex = $this->currentQuestionIndex;
            $this->currentQuestionIndex = (int) $index;
            $this->progressTracking['navigation_count']++;
            $this->startQuestionTimer();
            $this->persistAttemptSessionState();

            $this->dispatch('questionChanged', previousIndex: $previousIndex, currentIndex: $this->currentQuestionIndex);
        }
    }

    protected function startQuestionTimer(): void
    {
        $this->progressTracking['current_question_start'] = microtime(true);
    }

    protected function trackQuestionTime($questionIndex): void
    {
        if (isset($this->progressTracking['current_question_start'])) {
            $startTime = $this->progressTracking['current_question_start'];
            $endTime = microtime(true);
            $duration = $endTime - $startTime;

            $this->progressTracking['question_durations'][$questionIndex] = $duration;
            $this->progressTracking['total_active_time'] += $duration;
        }
    }

    #[Renderless]
    public function toggleFlag($questionIndex): void
    {
        if (!isset($this->questions[$questionIndex])) {
            return;
        }

        $questionId = (int) $this->questions[$questionIndex]['id'];

        if (in_array($questionId, $this->flaggedQuestions, true)) {
            $this->flaggedQuestions = array_values(array_filter($this->flaggedQuestions, fn ($id) => (int) $id !== $questionId));
        } else {
            $this->flaggedQuestions[] = $questionId;
        }

        $this->persistAttemptSessionState();
    }

    public function retakeExam()
    {
        return redirect()->route('cbt.exams');
    }

    public function handleSecurityViolation($type, $details = null): void
    {
        if (!$this->examStarted || $this->examCompleted) {
            $this->skipRender();
            return;
        }

        $violation = [
            'type' => $type,
            'details' => $details,
            'timestamp' => Carbon::now(),
            'question_index' => $this->currentQuestionIndex,
            'time_remaining' => $this->timeRemaining,
        ];

        $this->securityViolations[] = $violation;

        if ($type === 'app_switch' || $type === 'visibility_change') {
            $this->progressTracking['pause_count']++;
        }

        if ($type === 'fullscreen_exit') {
            $this->dispatch('forceFullscreen');
        }

        $this->persistAttemptSessionState();

        if (count($this->securityViolations) >= 10) {
            session()->flash('error', 'Too many security violations detected. Exam auto-submitted.');
            $this->submitExam(true);
            return;
        }

        $this->skipRender();
    }

    public function getCurrentQuestion()
    {
        if ($this->currentQuestionIndex >= 0 && $this->currentQuestionIndex < count($this->questions)) {
            return $this->questions[$this->currentQuestionIndex];
        }

        return null;
    }

    public function getAnsweredQuestionsCount(): int
    {
        return count(array_filter($this->answers, fn ($answer) => $answer !== null && $answer !== ''));
    }

    public function isQuestionFlagged($questionIndex): bool
    {
        if (!isset($this->questions[$questionIndex])) {
            return false;
        }

        $questionId = (int) $this->questions[$questionIndex]['id'];
        return in_array($questionId, $this->flaggedQuestions, true);
    }

    public function getProgressPercentage(): float
    {
        return count($this->questions) > 0
            ? (($this->currentQuestionIndex + 1) / count($this->questions)) * 100
            : 0;
    }

    public function canGoNext(): bool
    {
        return $this->currentQuestionIndex < count($this->questions) - 1;
    }

    public function canGoPrevious(): bool
    {
        return $this->currentQuestionIndex > 0;
    }

    public function isLastQuestion(): bool
    {
        return $this->currentQuestionIndex === count($this->questions) - 1;
    }

    public function formatTimeSpent($seconds): string
    {
        $seconds = abs((int) $seconds);

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }

    protected function createAttemptSession(): AttemptSession
    {
        if ($this->isCurrentStudentLocked(true)) {
            throw new \RuntimeException($this->currentStudentLockMessage());
        }

        if (!$this->isAssessmentPublished(true)) {
            throw new \RuntimeException($this->unpublishedAssessmentMessage());
        }

        return DB::transaction(function (): AttemptSession {
            // Serialize exam-start creation per student so duplicate clicks/tabs
            // cannot create conflicting attempt records for the same paper.
            DB::table('users')
                ->where('id', Auth::id())
                ->lockForUpdate()
                ->first();

            $active = AttemptSession::query()
                ->where('assessment_id', $this->assessment->id)
                ->where('user_id', Auth::id())
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->latest('started_at')
                ->first();

            if ($active && !$active->isExpired()) {
                $this->attemptNumber = (int) $active->attempt_number;
                return $active;
            }

            $durationSeconds = max(60, (int) $this->assessment->estimated_duration_minutes * 60);
            $this->attemptNumber = (int) $this->assessment->getNextAttemptNumber(Auth::id());

            return AttemptSession::create([
                'assessment_id' => $this->assessment->id,
                'user_id' => Auth::id(),
                'school_id' => (int) $this->currentSchoolId(),
                'attempt_number' => (int) $this->attemptNumber,
                'current_question_index' => (int) $this->currentQuestionIndex,
                'status' => 'in_progress',
                'started_at' => now(),
                'expires_at' => now()->addSeconds($durationSeconds),
                'last_activity_at' => now(),
                'question_order' => $this->questionOrder,
                'answers_snapshot' => $this->answers,
                'flagged_question_ids' => array_values($this->flaggedQuestions),
                'security_violations' => $this->securityViolations,
                'ip_address' => request()->ip(),
                'user_agent' => (string) request()->userAgent(),
            ]);
        }, 3);
    }

    protected function activateExamSession(AttemptSession $session): void
    {
        $this->attemptSessionId = $session->id;
        $this->examStarted = true;
        $this->isFullscreenForced = true;
        $this->startTime = $session->started_at ?: Carbon::now();
        $this->timeRemaining = max(0, now()->diffInSeconds($session->expires_at, false));
        $this->startQuestionTimer();
    }

    protected function getActiveAttemptSession(): ?AttemptSession
    {
        return AttemptSession::query()
            ->where('assessment_id', $this->assessment->id)
            ->where('user_id', Auth::id())
            ->where('status', 'in_progress')
            ->latest('started_at')
            ->first();
    }

    protected function currentAttemptSession(): ?AttemptSession
    {
        if ($this->attemptSessionId) {
            return AttemptSession::query()->find($this->attemptSessionId);
        }

        return $this->getActiveAttemptSession();
    }

    protected function restoreAttemptSnapshot(AttemptSession $session): void
    {
        $snapshot = is_array($session->answers_snapshot) ? $session->answers_snapshot : [];

        foreach ($this->answers as $questionId => $value) {
            if (array_key_exists($questionId, $snapshot)) {
                $this->answers[$questionId] = $snapshot[$questionId];
            } elseif (array_key_exists((string) $questionId, $snapshot)) {
                $this->answers[$questionId] = $snapshot[(string) $questionId];
            }
        }

        $this->flaggedQuestions = array_values(array_map(
            'intval',
            is_array($session->flagged_question_ids) ? $session->flagged_question_ids : []
        ));
        $this->securityViolations = is_array($session->security_violations) ? $session->security_violations : [];

        $maxIndex = max(0, count($this->questions) - 1);
        $this->currentQuestionIndex = min(max(0, (int) $session->current_question_index), $maxIndex);
    }

    protected function mergeAnswersFromSession(?AttemptSession $session): void
    {
        if (!$session || !is_array($session->answers_snapshot)) {
            return;
        }

        foreach ($session->answers_snapshot as $questionId => $answer) {
            if (array_key_exists((int) $questionId, $this->answers) && $this->answers[(int) $questionId] === null) {
                $this->answers[(int) $questionId] = $answer;
            }
        }
    }

    protected function persistCurrentAnswer(int $questionId): void
    {
        if (!$this->examStarted || $this->examCompleted) {
            return;
        }

        $session = $this->currentAttemptSession();
        if (!$session) {
            return;
        }

        $questionData = collect($this->questions)->firstWhere('id', $questionId);
        if (!$questionData) {
            return;
        }

        $displayAnswer = $this->answers[$questionId] ?? null;
        $originalAnswer = $displayAnswer === null
            ? null
            : $this->mapDisplayToOriginalAnswer($questionData, (int) $displayAnswer);

        $examData = [
            'was_shuffled' => $questionData['was_shuffled'] ?? false,
            'user_displayed_answer' => $displayAnswer,
            'user_original_answer' => $originalAnswer,
            'display_to_original_map' => $questionData['display_to_original_map'] ?? null,
            'original_correct_answers' => $questionData['original_correct_answers'] ?? [],
        ];

        StudentAnswer::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'assessment_id' => $this->assessment->id,
                'question_id' => $questionId,
                'attempt_number' => $this->attemptNumber,
            ],
            [
                'answer' => $originalAnswer,
                'submitted_at' => null,
                'question_order' => $this->questionOrder,
                'exam_data' => $examData,
            ]
        );
    }

    protected function persistAttemptSessionState(): void
    {
        $session = $this->currentAttemptSession();
        if (!$session || $session->status !== 'in_progress') {
            return;
        }

        $session->update([
            'last_activity_at' => now(),
            'current_question_index' => (int) $this->currentQuestionIndex,
            'answers_snapshot' => $this->answers,
            'flagged_question_ids' => array_values($this->flaggedQuestions),
            'security_violations' => $this->securityViolations,
        ]);
    }

    protected function mapDisplayToOriginalAnswer(array $questionData, int $displayAnswer): int
    {
        if (
            !empty($questionData['was_shuffled'])
            && isset($questionData['display_to_original_map'][$displayAnswer])
        ) {
            return (int) $questionData['display_to_original_map'][$displayAnswer];
        }

        return $displayAnswer;
    }

    protected function deterministicHash(string $scope, int $entityId): string
    {
        return hash('sha256', implode('|', [
            $scope,
            (string) $entityId,
            (string) $this->assessment->id,
            (string) $this->attemptNumber,
            (string) Auth::id(),
        ]));
    }

    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id;
    }

    protected function assessmentsForCurrentSchool(): Builder
    {
        return Assessment::query()
            ->standaloneCBT()
            ->visibleToUser(auth()->user());
    }

    protected function isAssessmentPublished(bool $refresh = false): bool
    {
        if ($refresh && $this->assessment) {
            $this->assessment->refresh();
        }

        return (bool) $this->assessment?->isExamPublished();
    }

    protected function isCurrentStudentLocked(bool $refresh = false): bool
    {
        if ($refresh && $this->assessment) {
            $this->assessment->refresh();
        }

        return (bool) $this->assessment?->isLockedForStudent(Auth::id());
    }

    protected function currentStudentLockMessage(): string
    {
        return $this->assessment?->lockedStudentMessage()
            ?? 'You are currently restricted from writing this CBT exam. Please contact school administration.';
    }

    protected function unpublishedAssessmentMessage(): string
    {
        return 'This CBT exam has not been published to students yet.';
    }

    protected function resolveStartExamErrorMessage(\Throwable $exception): string
    {
        if ($exception instanceof \RuntimeException) {
            return $exception->getMessage();
        }

        if ($exception instanceof QueryException) {
            $message = $exception->getMessage();

            if (
                str_contains($message, 'assessment_attempt_sessions_attempt_unique')
                || str_contains($message, 'Duplicate entry')
            ) {
                return 'This exam is already opening in another tab or request. Reload once and resume the active attempt.';
            }

            if (
                str_contains($message, 'assessment_attempt_sessions')
                || str_contains($message, 'student_answers')
            ) {
                return 'CBT setup is incomplete. Ask the administrator to run the latest CBT database update, then try again.';
            }
        }

        return 'Failed to start exam. Please try again.';
    }
}
