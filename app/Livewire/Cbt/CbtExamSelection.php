<?php

namespace App\Livewire\Cbt;

use Livewire\Component;
use App\Models\Assessment\Assessment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard', ['title' => 'CBT Exams', 'description' => 'Select a CBT exam', 'icon' => 'fas fa-laptop-code'])]
class CbtExamSelection extends Component
{
    public function render()
    {
        // Get only standalone CBT assessments
        $availableAssessments = Assessment::where('type', 'quiz')
            ->whereNull('section_id')
            ->whereNull('lesson_id')
            ->with(['questions', 'course'])
            ->whereHas('questions') // Only show assessments that have questions
            ->get()
            ->map(function($assessment) {
                // Check if user has attempted this assessment
                $userResult = $assessment->getStudentResults(Auth::id());
                $assessment->user_result = $userResult;
                
                // Get attempt count and check if can take
                $attemptCount = $assessment->getStudentAttemptCount(Auth::id());
                $assessment->attempts_count = $attemptCount;
                
                // Check if user can take the assessment
                [$canTake, $message] = $assessment->canUserTakeAssessment(Auth::id());
                $assessment->can_take = $canTake;
                $assessment->attempt_message = $message;
                
                // Calculate remaining attempts
                $assessment->remaining_attempts = $assessment->getRemainingAttempts(Auth::id());
                
                return $assessment;
            });

        return view('livewire.cbt.cbt-exam-selection', compact('availableAssessments'));
    }

    public function startExam($assessmentId)
    {
        
        $assessment = Assessment::with('questions')->find($assessmentId);
        
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
}