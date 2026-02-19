<?php

namespace App\Livewire\Cbt;

use Livewire\Component;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\StudentAnswer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use App\Jobs\SendExamResultsEmail;
use Carbon\Carbon;

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
    public $showSubmitModal = false;
    public $isFullscreenForced = false;
    public $questionOrder = [];
    
    public $progressTracking = [
        'question_start_times' => [],
        'question_end_times' => [],
        'question_durations' => [],
        'total_active_time' => 0,
        'pause_count' => 0,
        'navigation_count' => 0
    ];

    public function mount($assessment)
    {
        $this->assessment = $this->assessmentsForCurrentSchool()
            ->with('questions')
            ->find($assessment);
        
        if (!$this->assessment || $this->assessment->questions->count() === 0) {
            session()->flash('error', 'Invalid assessment.');
            return redirect()->route('cbt.exams');
        }

        [$canTake, $message] = $this->assessment->canUserTakeAssessment(Auth::id());
        if (!$canTake) {
            session()->flash('error', $message);
            return redirect()->route('cbt.exams');
        }

        $this->loadQuestions();
        $this->initializeExam();
    }

    public function render()
    {
        return view('livewire.cbt.cbt-exam-interface');
    }

    protected function loadQuestions()
    {
        $questionCollection = $this->assessment->questions;
        
        if ($this->assessment->shuffle_questions) {
            $questionCollection = $questionCollection->shuffle();
        }
        
        $this->questions = $questionCollection->map(function ($question) {
            $q = $question->toArray();

            // Format options
            $q['options'] = is_string($q['options'] ?? null) 
                ? json_decode($q['options'], true) ?? [] 
                : ($q['options'] ?? []);

            // Format correct answers
            $correctAnswers = $q['correct_answers'] ?? $question->correct_answers;
            if (is_string($correctAnswers)) {
                $correctAnswers = json_decode($correctAnswers, true) ?? [$correctAnswers];
            } elseif (!is_array($correctAnswers)) {
                $correctAnswers = [$correctAnswers];
            }
            $q['original_correct_answers'] = array_map('intval', $correctAnswers);
            
            // Shuffle options if enabled
            if ($this->assessment->shuffle_options && 
                !empty($q['options']) && 
                $q['question_type'] === 'multiple_choice') {
                
                $original = $q['options'];
                $originalCorrect = $q['original_correct_answers'];
                
                // Create indexed array
                $indexed = [];
                foreach ($original as $idx => $text) {
                    $indexed[] = ['orig_idx' => $idx, 'text' => $text];
                }
                
                shuffle($indexed);
                
                // Build mappings
                $newOptions = [];
                $displayToOriginal = [];
                $originalToDisplay = [];
                
                foreach ($indexed as $newIdx => $item) {
                    $origIdx = $item['orig_idx'];
                    $newOptions[$newIdx] = $item['text'];
                    $displayToOriginal[$newIdx] = $origIdx; // NEW → ORIGINAL
                    $originalToDisplay[$origIdx] = $newIdx; // ORIGINAL → NEW
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

    protected function initializeExam()
    {
        $this->timeRemaining = $this->assessment->estimated_duration_minutes * 60;
        $this->attemptNumber = $this->assessment->getNextAttemptNumber(Auth::id());
        foreach ($this->questions as $question) {
            $this->answers[$question['id']] = null;
        }
    }

    public function saveAnswer($questionId, $answer)
    {
        if ($answer === null || $answer === '' || $answer === 'null') {
            $this->answers[$questionId] = null;
            return;
        }
        $this->answers[$questionId] = (int)$answer; // Store displayed position
    }

    public function submitExam()
    {
        if ($this->examCompleted) {
            return;
        }

        $this->showSubmitModal = false;

        try {
            $this->trackQuestionTime($this->currentQuestionIndex);

            $timeSpent = $this->startTime ? abs($this->startTime->diffInSeconds(now(), false)) : 0;

            $submissionSummary = DB::transaction(function () {
                $totalPoints = 0;
                $correctAnswers = 0;
                $answeredQuestions = 0;

                foreach ($this->questions as $questionIndex => $qData) {
                    $qId = $qData['id'];
                    $userDisplayAnswer = $this->answers[$qId] ?? null;

                    if ($userDisplayAnswer === null || $userDisplayAnswer === '') {
                        continue;
                    }

                    $answeredQuestions++;

                    // Map displayed answer to original
                    $userOriginalAnswer = $userDisplayAnswer;
                    if ($qData['was_shuffled'] && isset($qData['display_to_original_map'][$userDisplayAnswer])) {
                        $userOriginalAnswer = $qData['display_to_original_map'][$userDisplayAnswer];
                    }

                    $examData = [
                        'was_shuffled' => $qData['was_shuffled'],
                        'user_displayed_answer' => $userDisplayAnswer,
                        'user_original_answer' => $userOriginalAnswer,
                        'display_to_original_map' => $qData['display_to_original_map'],
                        'original_correct_answers' => $qData['original_correct_answers'],
                    ];

                    $questionDurationSeconds = (int) round($this->progressTracking['question_durations'][$questionIndex] ?? 0);

                    // Idempotent write for each question in this attempt.
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
                            'submitted_at' => now(),
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

            $this->examCompleted = true;
            $this->dispatch('examCompleted');
            
        } catch (\Exception $e) {
            \Log::error('Submit failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to submit exam.');
        }
    }
    public function nextQuestion()
    {
        if ($this->currentQuestionIndex < count($this->questions) - 1) {
            $this->trackQuestionTime($this->currentQuestionIndex);
            $previousIndex = $this->currentQuestionIndex;
            $this->currentQuestionIndex++;
            $this->progressTracking['navigation_count']++;
            $this->startQuestionTimer();
            
            $this->dispatch('questionChanged', 
                previousIndex: $previousIndex, 
                currentIndex: $this->currentQuestionIndex
            );
        }
    }

    public function previousQuestion()
    {
        if ($this->currentQuestionIndex > 0) {
            $this->trackQuestionTime($this->currentQuestionIndex);
            $previousIndex = $this->currentQuestionIndex;
            $this->currentQuestionIndex--;
            $this->progressTracking['navigation_count']++;
            $this->startQuestionTimer();
            
            $this->dispatch('questionChanged', 
                previousIndex: $previousIndex, 
                currentIndex: $this->currentQuestionIndex
            );
        }
    }

    public function goToQuestion($index)
    {
        if ($index >= 0 && $index < count($this->questions)) {
            $this->trackQuestionTime($this->currentQuestionIndex);
            $previousIndex = $this->currentQuestionIndex;
            $this->currentQuestionIndex = $index;
            $this->progressTracking['navigation_count']++;
            $this->startQuestionTimer();
            
            $this->dispatch('questionChanged', 
                previousIndex: $previousIndex, 
                currentIndex: $this->currentQuestionIndex
            );
        }
    }

    protected function startQuestionTimer()
    {
        $this->progressTracking['current_question_start'] = microtime(true);
    }

    protected function trackQuestionTime($questionIndex)
    {
        if (isset($this->progressTracking['current_question_start'])) {
            $startTime = $this->progressTracking['current_question_start'];
            $endTime = microtime(true);
            $duration = $endTime - $startTime;
            
            $this->progressTracking['question_durations'][$questionIndex] = $duration;
            $this->progressTracking['total_active_time'] += $duration;
        }
    }

    public function toggleFlag($questionIndex)
    {
        if (!isset($this->questions[$questionIndex])) {
            return;
        }

        $questionId = $this->questions[$questionIndex]['id'];

        if (in_array($questionId, $this->flaggedQuestions)) {
            $this->flaggedQuestions = array_values(array_filter($this->flaggedQuestions, function ($id) use ($questionId) {
                return $id !== $questionId;
            }));
        } else {
            $this->flaggedQuestions[] = $questionId;
        }
    }

    public function retakeExam()
    {
        return redirect()->route('cbt.exams');
    }

    public function handleSecurityViolation($type, $details = null)
    {
        if (!$this->examStarted || $this->examCompleted) {
            return;
        }

        $violation = [
            'type' => $type,
            'details' => $details,
            'timestamp' => Carbon::now(),
            'question_index' => $this->currentQuestionIndex,
            'time_remaining' => $this->timeRemaining
        ];

        $this->securityViolations[] = $violation;

        if ($type === 'app_switch' || $type === 'visibility_change') {
            $this->progressTracking['pause_count']++;
        }

        if ($type === 'fullscreen_exit') {
            $this->dispatch('forceFullscreen');
        }

        if (count($this->securityViolations) >= 10) {
            session()->flash('error', 'Too many security violations detected. Exam auto-submitted.');
            $this->submitExam();
        }
    }

    public function startExam()
    {
        try {
            $this->examStarted = true;
            $this->isFullscreenForced = true;
            $this->startTime = Carbon::now();
            $this->startQuestionTimer();
            
            $this->dispatch('startTimer');
            $this->dispatch('markExamStarted');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to start exam. Please try again.');
        }
    }


    public function showSubmitConfirmation()
    {
        $this->showSubmitModal = true;
    }

    public function cancelSubmission()
    {
        $this->showSubmitModal = false;
    }

   



    public function getCurrentQuestion()
    {
        if ($this->currentQuestionIndex >= 0 && $this->currentQuestionIndex < count($this->questions)) {
            return $this->questions[$this->currentQuestionIndex];
        }
        return null;
    }

    public function getAnsweredQuestionsCount()
    {
        return count(array_filter($this->answers, function ($answer) {
            return $answer !== null && $answer !== '';
        }));
    }

    public function isQuestionFlagged($questionIndex)
    {
        if (!isset($this->questions[$questionIndex])) {
            return false;
        }

        $questionId = $this->questions[$questionIndex]['id'];
        return in_array($questionId, $this->flaggedQuestions);
    }

    public function getProgressPercentage()
    {
        return count($this->questions) > 0 ? (($this->currentQuestionIndex + 1) / count($this->questions)) * 100 : 0;
    }

    public function canGoNext()
    {
        return $this->currentQuestionIndex < count($this->questions) - 1;
    }

    public function canGoPrevious()
    {
        return $this->currentQuestionIndex > 0;
    }

    public function isLastQuestion()
    {
        return $this->currentQuestionIndex === count($this->questions) - 1;
    }

    public function formatTimeSpent($seconds)
    {
        $seconds = abs($seconds);
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }
        return sprintf('%02d:%02d', $minutes, $secs);
    }

    public function showSummary()
    {
        return view('livewire.cbt.exam.cbt-exam-summary', [
            'questions' => $this->questions,
            'answers' => $this->answers,
            'currentQuestionIndex' => $this->currentQuestionIndex,
            'flaggedQuestions' => $this->flaggedQuestions,
            'timeRemaining' => $this->timeRemaining,
        ]);
    }

    protected function sendResultsEmail()
    {
        try {
            SendExamResultsEmail::dispatch(
                Auth::user(),
                $this->assessment,
                $this->attemptNumber,
                $this->results
            );
            
            session()->flash('message', 'Results will be sent to your email shortly!');
            
        } catch (\Exception $e) {
            // Silent fail - email not critical
        }
    }

    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id;
    }

    protected function assessmentsForCurrentSchool(): Builder
    {
        $query = Assessment::query()
            ->where('type', 'quiz')
            ->whereNull('section_id')
            ->whereNull('lesson_id');

        $schoolId = $this->currentSchoolId();
        if (!$schoolId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('course.classGroup', function ($classGroupQuery) use ($schoolId) {
            $classGroupQuery->where('school_id', $schoolId);
        });
    }
}
