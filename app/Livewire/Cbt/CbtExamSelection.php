<?php

namespace App\Livewire\Cbt;

use Livewire\Component;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\AttemptSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard', ['title' => 'CBT Exams', 'description' => 'Select a CBT exam', 'icon' => 'fas fa-laptop-code'])]
class CbtExamSelection extends Component
{
    public function render()
    {
        $isAuthorizedStudent = $this->isAuthorizedStudent();
        $userId = Auth::id();
        $canViewUnpublished = $this->canViewUnpublishedResults();
        $availableAssessments = collect();

        if ($isAuthorizedStudent) {
            $availableAssessments = $this->publishedAssessmentsForCurrentSchool()
                ->with([
                    'questions',
                    'course',
                    'lesson',
                    'studentAnswers' => fn ($query) => $query
                        ->where('user_id', $userId)
                        ->with('question'),
                    'attemptSessions' => fn ($query) => $query
                        ->where('user_id', $userId),
                ])
                ->whereHas('questions')
                ->get()
                ->map(function ($assessment) use ($canViewUnpublished) {
                    $submittedAnswers = $assessment->studentAnswers
                        ->whereNotNull('submitted_at');
                    $attemptCount = $submittedAnswers
                        ->pluck('attempt_number')
                        ->unique()
                        ->count();
                    $activeAttempt = $this->resolveActiveAttempt($assessment->attemptSessions);
                    $resultsVisible = $canViewUnpublished || $assessment->isResultPublished();

                    $assessment->results_visible = $resultsVisible;
                    $assessment->user_result = $resultsVisible
                        ? $this->buildUserResultSummary($assessment, $submittedAnswers)
                        : null;
                    $assessment->has_submitted_attempt = $attemptCount > 0;
                    $assessment->has_active_attempt = $activeAttempt !== null && !$activeAttempt->isExpired();
                    $assessment->attempts_count = $attemptCount;

                    [$canTake, $message] = $this->determineAttemptAvailability($assessment, $attemptCount, $activeAttempt);
                    $assessment->can_take = $canTake;
                    $assessment->attempt_message = $message;
                    $assessment->remaining_attempts = $this->calculateRemainingAttempts($assessment, $attemptCount);

                    return $assessment;
                });
        }

        return view('livewire.cbt.cbt-exam-selection', compact('availableAssessments', 'isAuthorizedStudent'));
    }

    public function startExam($assessmentId)
    {
        if (!$this->isAuthorizedStudent()) {
            session()->flash('error', 'Only authorised students assigned to the approved class can take CBT exams.');
            return;
        }

        $assessment = $this->assessmentsForCurrentSchool()
            ->with('questions')
            ->find($assessmentId);
        
        if (!$assessment || $assessment->questions->count() === 0) {
            session()->flash('error', 'Assessment not found or has no questions.');
            return;
        }

        // Check if user can take the exam (attempts check)
        [$canTake, $message] = $assessment->canUserTakeAssessment(Auth::id());
        
        if (!$canTake) {
            session()->flash('error', $message);
            return;
        }

        // Redirect to secure exam interface
        return redirect()->route('cbt.exam.take', ['assessment' => $assessmentId]);
        
    }

    public function viewResults($assessmentId)
    {
        return redirect()->route('cbt.viewer');
    }

    protected function isAuthorizedStudent(): bool
    {
        $user = auth()->user();

        return $user !== null
            && $user->hasRole('student')
            && Assessment::resolveAssignedClassIdForUser($user) !== null;
    }

    protected function assessmentsForCurrentSchool(): Builder
    {
        return Assessment::query()
            ->standaloneCBT()
            ->visibleToUser(auth()->user());
    }

    protected function publishedAssessmentsForCurrentSchool(): Builder
    {
        return Assessment::query()
            ->standaloneCBT()
            ->availableForStudentExamAccess(auth()->user());
    }

    protected function canViewUnpublishedResults(): bool
    {
        return (bool) auth()->user()?->can('manage cbt');
    }

    protected function resolveActiveAttempt(Collection $attemptSessions): ?AttemptSession
    {
        return $attemptSessions
            ->where('status', 'in_progress')
            ->sortByDesc(fn ($attemptSession) => $attemptSession->started_at?->getTimestamp() ?? 0)
            ->first();
    }

    protected function buildUserResultSummary(Assessment $assessment, Collection $submittedAnswers): ?array
    {
        if ($submittedAnswers->isEmpty()) {
            return null;
        }

        $latestAttemptNumber = $submittedAnswers->max('attempt_number');
        $answers = $submittedAnswers->where('attempt_number', $latestAttemptNumber);

        if ($answers->isEmpty()) {
            return null;
        }

        $maxPoints = $assessment->questions->sum('points');
        $totalPoints = $answers->sum('points_earned');
        $percentage = $maxPoints > 0
            ? round(($totalPoints / $maxPoints) * 100, 1)
            : 0;

        return [
            'total_questions' => $assessment->questions->count(),
            'answered_questions' => $answers->count(),
            'correct_answers' => $answers->where('is_correct', true)->count(),
            'total_points' => $totalPoints,
            'max_points' => $maxPoints,
            'percentage' => $percentage,
            'passed' => $percentage >= $assessment->pass_percentage,
            'attempt_number' => $latestAttemptNumber,
            'submitted_at' => $answers->sortByDesc('submitted_at')->first()?->submitted_at,
            'answers' => $answers->keyBy('question_id'),
        ];
    }

    protected function determineAttemptAvailability(
        Assessment $assessment,
        int $attemptCount,
        ?AttemptSession $activeAttempt
    ): array {
        if ($activeAttempt && !$activeAttempt->isExpired()) {
            return [true, 'You have an in-progress attempt that can be resumed'];
        }

        if ($assessment->max_attempts === null) {
            return [true, 'You can take this assessment'];
        }

        if ($attemptCount >= $assessment->max_attempts) {
            return [false, "Maximum attempts ({$assessment->max_attempts}) exhausted"];
        }

        $remainingAttempts = $assessment->max_attempts - $attemptCount;

        return [true, "You have {$remainingAttempts} attempt(s) remaining"];
    }

    protected function calculateRemainingAttempts(Assessment $assessment, int $attemptCount): int|string
    {
        if ($assessment->max_attempts === null) {
            return 'Unlimited';
        }

        return max(0, $assessment->max_attempts - $attemptCount);
    }
}
