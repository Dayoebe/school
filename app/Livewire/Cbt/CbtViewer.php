<?php

namespace App\Livewire\Cbt;

use Livewire\Component;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\StudentAnswer;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard', ['title' => 'View CBT Exam', 'description' => 'View CBT exams', 'icon' => 'fas fa-microphone-alt'])]
class CbtViewer extends Component
{
    use WithPagination;

    public $selectedAssessment = null;
    public $selectedAttempt = null;
    public $viewDetails = false;

    public function render()
    {
        // Get ONLY standalone CBT assessments where user has attempted
        $userAssessments = Assessment::where('type', 'quiz')
            ->whereNull('section_id')
            ->whereNull('lesson_id')
            ->whereHas('studentAnswers', function($query) {
                $query->where('user_id', Auth::id())
                      ->whereNotNull('submitted_at');
            })
            ->with(['studentAnswers' => function($query) {
                $query->where('user_id', Auth::id())
                      ->whereNotNull('submitted_at')
                      ->with('question');
            }, 'questions'])
            ->paginate(10);

        return view('livewire.cbt.cbt-viewer', compact('userAssessments'));
    }

    public function viewAssessmentDetails($assessmentId)
    {
        $this->selectedAssessment = Assessment::with([
            'studentAnswers' => function($query) {
                $query->where('user_id', Auth::id())
                      ->whereNotNull('submitted_at')
                      ->with('question')
                      ->orderBy('attempt_number', 'desc');
            },
            'questions'
        ])->findOrFail($assessmentId);

        $this->viewDetails = true;
    }

    public function viewAttemptDetails($attemptNumber)
    {
        $this->selectedAttempt = $this->selectedAssessment->getStudentResults(Auth::id(), $attemptNumber);

        if (!$this->selectedAttempt) {
            session()->flash('error', 'No results found for this attempt.');
        }
    }

    public function closeDetails()
    {
        $this->viewDetails = false;
        $this->selectedAssessment = null;
        $this->selectedAttempt = null;
    }

    /**
     * Better attempt aggregation with proper grouping
     */
    public function getAttemptsForAssessment($assessment)
    {
        $attempts = $assessment->studentAnswers
            ->where('user_id', Auth::id())
            ->whereNotNull('submitted_at')
            ->groupBy('attempt_number')
            ->map(function($answers, $attemptNumber) use ($assessment) {
                // Calculate totals for this attempt
                $totalPoints = $answers->sum('points_earned');
                $correctAnswersCount = $answers->where('is_correct', true)->count();
                
                // Get max possible points
                $maxPoints = $assessment->questions->sum('points') ?: $assessment->max_score ?: 100;
                
                // Calculate percentage
                $percentage = $maxPoints > 0 ? round(($totalPoints / $maxPoints) * 100, 1) : 0;
                $passed = $percentage >= $assessment->pass_percentage;

                return [
                    'attempt_number' => $attemptNumber,
                    'total_points' => $totalPoints,
                    'max_points' => $maxPoints,
                    'percentage' => $percentage,
                    'passed' => $passed,
                    'submitted_at' => $answers->first()->submitted_at,
                    'answers_count' => $answers->count(),
                    'correct_answers' => $correctAnswersCount
                ];
            })
            ->sortByDesc('attempt_number')
            ->values();

        return $attempts;
    }
}