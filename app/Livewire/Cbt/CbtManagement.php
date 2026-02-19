<?php

namespace App\Livewire\Cbt;

use Livewire\Component;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\Question;
use App\Models\Assessment\StudentAnswer;
use App\Models\MyClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard', ['title' => 'CBT Exam Management', 'description' => 'Manage CBT exams', 'icon' => 'fas fa-microphone-alt'])]
class CbtManagement extends Component
{
    use WithPagination;

    public $showCreateModal = false;
    public $showEditModal = false;
    public $showQuestionModal = false;
    public $showParticipantsModal = false;
    public $showEditQuestionModal = false;

    public $title = '';
    public $description = '';
    public $pass_percentage = 70;
    public $estimated_duration_minutes = 60;
    public $max_score = 100;
    public $max_attempts = null;
    public $shuffle_questions = false; // NEW
    public $shuffle_options = false;   // NEW
    public $type = 'quiz';
    public $course_id = null;

    public $selectedAssessment = null;
    public $editingAssessment = null;
    public $editingQuestion = null;

    // Question properties
    public $question_text = '';
    public $question_type = 'multiple_choice';
    public $points = 1;
    public $options = ['', '', '', ''];
    public $correct_answers = [];
    public $explanation = '';

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'pass_percentage' => 'required|integer|min:1|max:100',
        'estimated_duration_minutes' => 'required|integer|min:1',
        'max_score' => 'required|integer|min:1',
        'max_attempts' => 'nullable|integer|min:1|max:100',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'course_id' => 'required|exists:my_classes,id',
        'question_text' => 'required|string',
        'question_type' => 'required|string',
        'points' => 'required|numeric|min:0.1',
        'options' => 'required_if:question_type,multiple_choice,true_false|array|min:2',
        'correct_answers' => 'required|array|min:1',
    ];

    public function mount()
    {
        $this->resetForm();
        $this->resetQuestionForm();
    }

    public function updatedQuestionText($value)
    {
        $this->dispatch('question-text-updated', $value);
    }

    public function updatedExplanation($value)
    {
        $this->dispatch('explanation-updated', $value);
    }

    public function render()
    {
        $assessments = $this->assessmentsForCurrentSchool()
            ->with(['questions', 'course'])
            ->latest()
            ->paginate(10);

        $courses = $this->coursesForCurrentSchool()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (MyClass $class) {
                return (object) [
                    'id' => $class->id,
                    'title' => $class->name,
                ];
            });

        return view('livewire.cbt.cbt-management', compact('assessments', 'courses'));
    }

    public function createAssessment()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pass_percentage' => 'required|integer|min:1|max:100',
            'estimated_duration_minutes' => 'required|integer|min:1',
            'max_score' => 'required|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1|max:100',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'course_id' => 'required|exists:my_classes,id',
        ]);

        if (!$this->isCourseInCurrentSchool($this->course_id)) {
            session()->flash('error', 'Selected class does not belong to your school.');
            return;
        }

        Assessment::create([
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => 'quiz',
            'pass_percentage' => $this->pass_percentage,
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'max_score' => $this->max_score,
            'max_attempts' => $this->max_attempts,
            'shuffle_questions' => $this->shuffle_questions,
            'shuffle_options' => $this->shuffle_options,
            'is_mandatory' => true,
            'section_id' => null,
            'lesson_id' => null,
        ]);

        $this->resetForm();
        $this->showCreateModal = false;
        session()->flash('message', 'CBT Assessment created successfully!');
    }

    public function editAssessment($assessmentId)
    {
        $this->editingAssessment = $this->getAssessmentForCurrentSchool($assessmentId);
        if (!$this->editingAssessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $this->title = $this->editingAssessment->title;
        $this->description = $this->editingAssessment->description;
        $this->pass_percentage = $this->editingAssessment->pass_percentage;
        $this->estimated_duration_minutes = $this->editingAssessment->estimated_duration_minutes;
        $this->max_score = $this->editingAssessment->max_score;
        $this->max_attempts = $this->editingAssessment->max_attempts;
        $this->shuffle_questions = $this->editingAssessment->shuffle_questions ?? false;
        $this->shuffle_options = $this->editingAssessment->shuffle_options ?? false;
        $this->course_id = $this->editingAssessment->course_id;

        $this->showEditModal = true;
    }

    public function updateAssessment()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pass_percentage' => 'required|integer|min:1|max:100',
            'estimated_duration_minutes' => 'required|integer|min:1',
            'max_score' => 'required|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1|max:100',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'course_id' => 'required|exists:my_classes,id',
        ]);

        if (!$this->editingAssessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        if (!$this->isCourseInCurrentSchool($this->course_id)) {
            session()->flash('error', 'Selected class does not belong to your school.');
            return;
        }

        $assessment = $this->getAssessmentForCurrentSchool($this->editingAssessment->id);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $assessment->update([
            'title' => $this->title,
            'description' => $this->description,
            'pass_percentage' => $this->pass_percentage,
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'max_score' => $this->max_score,
            'max_attempts' => $this->max_attempts,
            'shuffle_questions' => $this->shuffle_questions,
            'shuffle_options' => $this->shuffle_options,
            'course_id' => $this->course_id,
        ]);

        $this->resetForm();
        $this->showEditModal = false;
        session()->flash('message', 'CBT Assessment updated successfully!');
    }

    public function deleteAssessment($assessmentId)
    {
        $assessment = $this->getAssessmentForCurrentSchool($assessmentId);
        if ($assessment) {
            $assessment->delete();
            session()->flash('message', 'CBT Assessment deleted successfully!');
        }
    }

    public function manageQuestions($assessmentId)
    {
        $this->selectedAssessment = $this->assessmentsForCurrentSchool()
            ->with('questions')
            ->find($assessmentId);

        if (!$this->selectedAssessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }
        $this->showQuestionModal = true;
        $this->resetQuestionForm();
    }

    public function addQuestion()
    {
        if (!$this->selectedAssessment) {
            session()->flash('error', 'No assessment selected.');
            return;
        }

        $assessment = $this->getAssessmentForCurrentSchool($this->selectedAssessment->id);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $this->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|string',
            'points' => 'required|numeric|min:0.1',
        ]);

        if ($this->question_type === 'multiple_choice') {
            $this->validate([
                'options' => 'required|array|min:2',
                'correct_answers' => 'required|array|min:1',
            ]);

            $this->options = array_values(array_filter($this->options, function ($option) {
                return !empty(trim($option));
            }));

            if (count($this->options) < 2) {
                $this->addError('options', 'Please provide at least 2 non-empty options.');
                return;
            }
        }

        Question::create([
            'assessment_id' => $assessment->id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'points' => $this->points,
            'options' => $this->question_type === 'multiple_choice' ? $this->options : null,
            'correct_answers' => $this->correct_answers,
            'explanation' => $this->explanation,
        ]);

        $this->resetQuestionForm();
        $this->selectedAssessment = $assessment->fresh('questions');
        session()->flash('message', 'Question added successfully!');
    }

    public function editQuestion($questionId)
    {
        if (!$this->selectedAssessment) {
            session()->flash('error', 'No assessment selected.');
            return;
        }

        $assessment = $this->getAssessmentForCurrentSchool($this->selectedAssessment->id);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $this->selectedAssessment = $assessment;
        $this->editingQuestion = Question::query()
            ->where('assessment_id', $assessment->id)
            ->find($questionId);

        if (!$this->editingQuestion) {
            session()->flash('error', 'Question not found.');
            return;
        }

        $this->question_text = $this->editingQuestion->question_text;
        $this->question_type = $this->editingQuestion->question_type;
        $this->points = $this->editingQuestion->points;
        $this->options = $this->editingQuestion->options ?? ['', '', '', ''];
        $this->correct_answers = $this->editingQuestion->correct_answers ?? [];
        $this->explanation = $this->editingQuestion->explanation ?? '';

        $this->showEditQuestionModal = true;
    }

    public function updateQuestion()
    {
        if (!$this->editingQuestion) {
            session()->flash('error', 'Question not found.');
            return;
        }

        if (!$this->selectedAssessment) {
            session()->flash('error', 'No assessment selected.');
            return;
        }

        $assessment = $this->getAssessmentForCurrentSchool($this->selectedAssessment->id);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $question = Question::query()
            ->where('assessment_id', $assessment->id)
            ->find($this->editingQuestion->id);

        if (!$question) {
            session()->flash('error', 'Question not found.');
            return;
        }

        $this->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|string',
            'points' => 'required|numeric|min:0.1',
        ]);

        if ($this->question_type === 'multiple_choice') {
            $this->validate([
                'options' => 'required|array|min:2',
                'correct_answers' => 'required|array|min:1',
            ]);

            $this->options = array_values(array_filter($this->options, function ($option) {
                return !empty(trim($option));
            }));
        }

        $question->update([
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'points' => $this->points,
            'options' => $this->question_type === 'multiple_choice' ? $this->options : null,
            'correct_answers' => $this->correct_answers,
            'explanation' => $this->explanation,
        ]);

        $this->resetQuestionForm();
        $this->showEditQuestionModal = false;
        $this->selectedAssessment = $assessment->fresh('questions');
        session()->flash('message', 'Question updated successfully!');
    }

    public function reorderQuestions($orderedIds)
    {
        if (!$this->selectedAssessment) {
            session()->flash('error', 'No assessment selected.');
            return;
        }

        $assessment = $this->getAssessmentForCurrentSchool($this->selectedAssessment->id);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $validQuestionIds = Question::query()
            ->where('assessment_id', $assessment->id)
            ->pluck('id')
            ->flip();

        $order = 1;
        foreach ($orderedIds as $id) {
            if (!$validQuestionIds->has($id)) {
                continue;
            }

            Question::where('id', $id)->update(['order' => $order]);
            $order++;
        }

        $this->selectedAssessment = $assessment->fresh('questions');
        session()->flash('message', 'Questions reordered successfully!');
    }

    public function deleteQuestion($questionId)
    {
        if (!$this->selectedAssessment) {
            session()->flash('error', 'No assessment selected.');
            return;
        }

        $assessment = $this->getAssessmentForCurrentSchool($this->selectedAssessment->id);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $question = Question::query()
            ->where('assessment_id', $assessment->id)
            ->find($questionId);

        if ($question) {
            $question->delete();
            $this->selectedAssessment = $assessment->fresh('questions');
            session()->flash('message', 'Question deleted successfully!');
        }
    }

    public function viewParticipants($assessmentId)
    {
        $schoolId = $this->currentSchoolId();

        $this->selectedAssessment = $this->assessmentsForCurrentSchool()->with([
            'studentAnswers' => function ($query) {
                $query->whereNotNull('submitted_at')
                    ->whereHas('user', function ($userQuery) {
                        $userQuery->where('school_id', $this->currentSchoolId());
                    })
                    ->with('user', 'question')
                    ->orderBy('user_id')
                    ->orderBy('attempt_number', 'desc');
            }
        ])->find($assessmentId);

        if (!$this->selectedAssessment || !$schoolId) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $this->showParticipantsModal = true;
    }

    public function getParticipantsData()
    {
        if (!$this->selectedAssessment) {
            return collect();
        }

        $participants = $this->selectedAssessment->studentAnswers
            ->filter(fn ($answer) => $answer->user !== null)
            ->groupBy('user_id')
            ->map(function ($answers, $userId) {
                $user = $answers->first()->user;
                $attempts = $answers->groupBy('attempt_number')->map(function ($attemptAnswers, $attemptNumber) {
                    $totalPoints = $attemptAnswers->sum('points_earned');
                    $maxPoints = $attemptAnswers->sum(function ($answer) {
                        return $answer->question ? $answer->question->points : 0;
                    });
                    $percentage = $maxPoints > 0 ? round(($totalPoints / $maxPoints) * 100, 1) : 0;

                    return [
                        'attempt_number' => $attemptNumber,
                        'total_points' => $totalPoints,
                        'max_points' => $maxPoints,
                        'percentage' => $percentage,
                        'passed' => $percentage >= $this->selectedAssessment->pass_percentage,
                        'submitted_at' => $attemptAnswers->first()->submitted_at,
                    ];
                })->sortByDesc('attempt_number')->values();

                $bestAttempt = $attempts->sortByDesc('percentage')->first();

                return [
                    'user' => $user,
                    'user_id' => $userId,
                    'attempts' => $attempts,
                    'best_attempt' => $bestAttempt,
                    'total_attempts' => $attempts->count(),
                ];
            })
            ->sortByDesc('best_attempt.percentage')
            ->values();

        return $participants;
    }

    public function clearAttempt($userId, $attemptNumber)
    {
        if (!$this->selectedAssessment) {
            session()->flash('error', 'No assessment selected.');
            return;
        }

        $assessment = $this->getAssessmentForCurrentSchool($this->selectedAssessment->id);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $user = User::role('student')
            ->where('school_id', $this->currentSchoolId())
            ->find($userId);

        if (!$user) {
            session()->flash('error', 'User not found.');
            return;
        }

        $deleted = StudentAnswer::where('user_id', $userId)
            ->where('assessment_id', $assessment->id)
            ->whereHas('user', function ($query) {
                $query->where('school_id', $this->currentSchoolId());
            })
            ->where('attempt_number', $attemptNumber)
            ->delete();

        if ($deleted > 0) {
            $this->selectedAssessment = $assessment->fresh('studentAnswers');
            session()->flash('message', "Attempt #{$attemptNumber} cleared for {$user->name}");
        } else {
            session()->flash('error', 'Failed to clear attempt.');
        }
    }

    public function clearAllUserAttempts($userId)
    {
        if (!$this->selectedAssessment) {
            session()->flash('error', 'No assessment selected.');
            return;
        }

        $assessment = $this->getAssessmentForCurrentSchool($this->selectedAssessment->id);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $user = User::role('student')
            ->where('school_id', $this->currentSchoolId())
            ->find($userId);

        if (!$user) {
            session()->flash('error', 'User not found.');
            return;
        }

        $deleted = StudentAnswer::where('user_id', $userId)
            ->where('assessment_id', $assessment->id)
            ->whereHas('user', function ($query) {
                $query->where('school_id', $this->currentSchoolId());
            })
            ->delete();

        if ($deleted > 0) {
            $this->selectedAssessment = $assessment->fresh('studentAnswers');
            session()->flash('message', "All attempts cleared for {$user->name} ({$deleted} answer(s) removed)");
        } else {
            session()->flash('error', 'No attempts found to clear.');
        }
    }

    public function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->pass_percentage = 70;
        $this->estimated_duration_minutes = 60;
        $this->max_score = 100;
        $this->max_attempts = null;
        $this->shuffle_questions = false;
        $this->shuffle_options = false;
        $this->course_id = null;
        $this->editingAssessment = null;
    }

    public function resetQuestionForm()
    {
        $this->question_text = '';
        $this->question_type = 'multiple_choice';
        $this->points = 1;
        $this->options = ['', '', '', ''];
        $this->correct_answers = [];
        $this->explanation = '';
        $this->editingQuestion = null;
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showQuestionModal = false;
        $this->showParticipantsModal = false;
        $this->showEditQuestionModal = false;
        $this->selectedAssessment = null;
        $this->resetForm();
        $this->resetQuestionForm();
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

    protected function coursesForCurrentSchool(): Builder
    {
        $schoolId = $this->currentSchoolId();
        $query = MyClass::query();

        if (!$schoolId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('classGroup', function ($classGroupQuery) use ($schoolId) {
            $classGroupQuery->where('school_id', $schoolId);
        });
    }

    protected function getAssessmentForCurrentSchool($assessmentId): ?Assessment
    {
        if (!$assessmentId) {
            return null;
        }

        return $this->assessmentsForCurrentSchool()->find($assessmentId);
    }

    protected function isCourseInCurrentSchool($courseId): bool
    {
        if (!$courseId) {
            return false;
        }

        return $this->coursesForCurrentSchool()
            ->whereKey($courseId)
            ->exists();
    }
}
