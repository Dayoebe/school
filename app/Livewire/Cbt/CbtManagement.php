<?php

namespace App\Livewire\Cbt;

use App\Models\Assessment\Assessment;
use App\Models\Assessment\AssessmentStudentLock;
use App\Models\Assessment\Question;
use App\Models\Assessment\StudentAnswer;
use App\Models\User;
use App\Traits\RestrictsTeacherCbtManagement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard', ['title' => 'CBT Exam Management', 'description' => 'Manage CBT exams', 'icon' => 'fas fa-microphone-alt'])]
class CbtManagement extends Component
{
    use RestrictsTeacherCbtManagement;
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
    public $lesson_id = null;

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

    protected $validationAttributes = [
        'course_id' => 'class',
        'lesson_id' => 'subject',
        'question_text' => 'question text',
        'question_type' => 'question type',
        'correct_answers' => 'correct answer',
    ];

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
        'lesson_id' => 'required|exists:subjects,id',
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

    public function updatedCourseId($value): void
    {
        $classId = $value ? (int) $value : null;

        if (!$classId || !$this->currentUserCanManageCbtClass($classId)) {
            $this->lesson_id = null;
            return;
        }

        if ($this->lesson_id && !$this->currentUserCanManageCbtSubject($this->lesson_id, $classId)) {
            $this->lesson_id = null;
        }
    }

    public function render()
    {
        $assessments = $this->assessmentsForCurrentSchool()
            ->with(['questions', 'course', 'lesson'])
            ->latest()
            ->paginate(10);

        $classes = $this->classesForCurrentSchool()
            ->orderBy('name')
            ->get(['id', 'name']);

        $subjects = $this->availableSubjectsForSelectedClass();
        $isRestrictedTeacherManager = $this->isRestrictedTeacherCbtManager();
        $canLockAssessments = $this->currentUserCanLockAssessments();

        return view('livewire.cbt.cbt-management', compact(
            'assessments',
            'classes',
            'subjects',
            'isRestrictedTeacherManager',
            'canLockAssessments',
        ));
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
            'lesson_id' => 'required|exists:subjects,id',
        ]);

        if (!$this->currentUserCanManageCbtClass($this->course_id)) {
            session()->flash('error', 'You can only create CBT assessments for your assigned classes.');
            return;
        }

        if (!$this->currentUserCanManageCbtSubject($this->lesson_id, $this->course_id)) {
            session()->flash('error', 'You can only create CBT assessments for subjects assigned to you in this class.');
            return;
        }

        Assessment::create([
            'course_id' => $this->course_id,
            'lesson_id' => $this->lesson_id,
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
        $this->lesson_id = $this->editingAssessment->lesson_id;

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
            'lesson_id' => 'required|exists:subjects,id',
        ]);

        if (!$this->editingAssessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        if (!$this->currentUserCanManageCbtClass($this->course_id)) {
            session()->flash('error', 'You can only update CBT assessments for your assigned classes.');
            return;
        }

        if (!$this->currentUserCanManageCbtSubject($this->lesson_id, $this->course_id)) {
            session()->flash('error', 'You can only update CBT assessments for subjects assigned to you in this class.');
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
            'lesson_id' => $this->lesson_id,
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

    public function publishResults($assessmentId): void
    {
        $assessment = $this->getAssessmentForCurrentSchool($assessmentId);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $assessment->update([
            'results_published_at' => now(),
            'results_published_by' => auth()->id(),
        ]);

        session()->flash('message', 'CBT results published for students.');
    }

    public function unpublishResults($assessmentId): void
    {
        $assessment = $this->getAssessmentForCurrentSchool($assessmentId);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $assessment->update([
            'results_published_at' => null,
            'results_published_by' => null,
        ]);

        session()->flash('message', 'CBT results unpublished.');
    }

    public function toggleAssessmentLock($assessmentId): void
    {
        if (!$this->currentUserCanLockAssessments()) {
            session()->flash('error', 'Only super admin can lock, unlock, or publish CBT papers.');
            return;
        }

        $assessment = $this->getAssessmentForCurrentSchool($assessmentId);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $locking = !$assessment->is_locked;
        $wasPublishedToStudents = $assessment->exam_published_at !== null;
        $attributes = [
            'is_locked' => $locking,
        ];

        if (!$locking && $wasPublishedToStudents) {
            $attributes['exam_published_at'] = null;
            $attributes['exam_published_by'] = null;
        }

        $assessment->update($attributes);

        session()->flash(
            'message',
            $locking
                ? 'CBT paper locked. Only super admin can open the question bank. Publish it when students should write.'
                : ($wasPublishedToStudents
                    ? 'CBT paper unlocked and withdrawn from students. You can edit the questions again.'
                    : 'CBT paper unlocked. You can edit the questions again.')
        );
    }

    public function publishExam($assessmentId): void
    {
        if (!$this->currentUserCanLockAssessments()) {
            session()->flash('error', 'Only super admin can lock, unlock, or publish CBT papers.');
            return;
        }

        $assessment = $this->getAssessmentForCurrentSchool($assessmentId);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        if (!$assessment->is_locked) {
            session()->flash('error', 'Lock and vet this CBT paper before publishing it to students.');
            return;
        }

        if (!$assessment->questions()->exists()) {
            session()->flash('error', 'Add at least one question before publishing this CBT paper.');
            return;
        }

        $assessment->update([
            'exam_published_at' => now(),
            'exam_published_by' => auth()->id(),
        ]);

        session()->flash('message', 'CBT paper published. It is now available to students.');
    }

    public function unpublishExam($assessmentId): void
    {
        if (!$this->currentUserCanLockAssessments()) {
            session()->flash('error', 'Only super admin can lock, unlock, or publish CBT papers.');
            return;
        }

        $assessment = $this->getAssessmentForCurrentSchool($assessmentId);
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $assessment->update([
            'exam_published_at' => null,
            'exam_published_by' => null,
        ]);

        session()->flash('message', 'CBT paper withdrawn from students.');
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

        if (!$this->currentUserCanAccessQuestionBank($this->selectedAssessment)) {
            $this->selectedAssessment = null;
            session()->flash('error', 'This CBT paper has been locked after vetting. Only super admin can view the questions.');
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

        if (!$this->currentUserCanEditQuestionBank($assessment)) {
            session()->flash('error', 'This CBT paper is locked. Unlock it before editing the questions.');
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

        if (!$this->currentUserCanEditQuestionBank($assessment)) {
            session()->flash('error', 'This CBT paper is locked. Unlock it before editing the questions.');
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

        if (!$this->currentUserCanEditQuestionBank($assessment)) {
            session()->flash('error', 'This CBT paper is locked. Unlock it before editing the questions.');
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

        if (!$this->currentUserCanEditQuestionBank($assessment)) {
            session()->flash('error', 'This CBT paper is locked. Unlock it before editing the questions.');
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

        if (!$this->currentUserCanEditQuestionBank($assessment)) {
            session()->flash('error', 'This CBT paper is locked. Unlock it before editing the questions.');
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

        $this->selectedAssessment = $this->assessmentParticipantsQuery()
            ->find($assessmentId);

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

        $answersByUser = $this->selectedAssessment->studentAnswers
            ->filter(fn ($answer) => $answer->user !== null)
            ->groupBy('user_id');
        $eligibleStudents = $this->eligibleStudentsForAssessment($this->selectedAssessment)->keyBy('id');
        $eligibleUserIds = $eligibleStudents->keys()
            ->map(fn ($id) => (int) $id)
            ->flip();
        $attemptedStudents = $this->selectedAssessment->studentAnswers
            ->filter(fn ($answer) => $answer->user !== null)
            ->pluck('user')
            ->keyBy('id');
        $students = $eligibleStudents
            ->merge($attemptedStudents)
            ->keyBy('id');

        $lockedUserIds = $this->selectedAssessment->studentLocks
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        $participants = $students->map(function ($user) use ($answersByUser, $lockedUserIds, $eligibleUserIds) {
                $answers = $answersByUser->get($user->id, collect());
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
                $isLocked = $lockedUserIds->has((int) $user->id);

                return [
                    'user' => $user,
                    'user_id' => (int) $user->id,
                    'attempts' => $attempts,
                    'best_attempt' => $bestAttempt,
                    'total_attempts' => $attempts->count(),
                    'is_locked' => $isLocked,
                    'is_eligible' => !$isLocked,
                    'eligible_for_exam' => $eligibleUserIds->has((int) $user->id),
                ];
            })
            ->sort(function (array $left, array $right) {
                if ($left['is_eligible'] !== $right['is_eligible']) {
                    return $left['is_eligible'] ? 1 : -1;
                }

                if ($left['total_attempts'] !== $right['total_attempts']) {
                    return $right['total_attempts'] <=> $left['total_attempts'];
                }

                return strcasecmp($left['user']->name, $right['user']->name);
            })
            ->values();

        return $participants;
    }

    public function toggleStudentEligibility($userId): void
    {
        if (!$this->currentUserCanLockAssessments()) {
            session()->flash('error', 'Only super admin can change CBT participant eligibility.');
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

        $existingLock = AssessmentStudentLock::query()
            ->where('assessment_id', $assessment->id)
            ->where('user_id', (int) $userId)
            ->first();

        $student = User::students()
            ->where('school_id', $this->currentSchoolId())
            ->find((int) $userId);

        if (!$student) {
            session()->flash('error', 'Student not found.');
            return;
        }

        $isEligibleStudent = $this->eligibleStudentsForAssessment($assessment)
            ->contains(fn ($eligibleStudent) => (int) $eligibleStudent->id === (int) $userId);

        if (!$existingLock && !$isEligibleStudent) {
            session()->flash('error', 'Only students currently assigned to this class can be marked eligible or ineligible.');
            return;
        }

        if ($existingLock) {
            $existingLock->delete();
            session()->flash('message', "{$student->name} has been marked eligible for this CBT paper.");
        } else {
            AssessmentStudentLock::create([
                'assessment_id' => $assessment->id,
                'user_id' => (int) $userId,
                'school_id' => (int) $this->currentSchoolId(),
                'locked_by' => auth()->id(),
            ]);

            session()->flash('message', "{$student->name} has been marked ineligible for this CBT paper.");
        }

        $this->selectedAssessment = $this->assessmentParticipantsQuery()
            ->find($assessment->id);
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
            $this->selectedAssessment = $this->assessmentParticipantsQuery()
                ->find($assessment->id);
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
            $this->selectedAssessment = $this->assessmentParticipantsQuery()
                ->find($assessment->id);
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
        $this->lesson_id = null;
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
        return $this->accessibleCbtAssessmentsQuery();
    }

    protected function classesForCurrentSchool(): Builder
    {
        return $this->accessibleCbtClassesQuery();
    }

    protected function getAssessmentForCurrentSchool($assessmentId): ?Assessment
    {
        if (!$assessmentId) {
            return null;
        }

        return $this->assessmentsForCurrentSchool()->find($assessmentId);
    }

    protected function availableSubjectsForSelectedClass(): Collection
    {
        $classId = $this->course_id ? (int) $this->course_id : null;

        if (!$classId || !$this->currentUserCanManageCbtClass($classId)) {
            return collect();
        }

        return $this->accessibleCbtSubjectsQuery($classId)
            ->get(['subjects.id', 'subjects.name', 'subjects.short_name']);
    }

    protected function currentUserCanLockAssessments(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'super_admin']) === true;
    }

    protected function currentUserCanAccessQuestionBank(Assessment $assessment): bool
    {
        return !$assessment->is_locked || $this->currentUserCanLockAssessments();
    }

    protected function currentUserCanEditQuestionBank(Assessment $assessment): bool
    {
        return !$assessment->is_locked;
    }

    protected function eligibleStudentsForAssessment(Assessment $assessment): Collection
    {
        $schoolId = $this->currentSchoolId();
        $academicYearId = auth()->user()?->school?->academic_year_id;

        if (!$assessment->course_id || !$schoolId || !$academicYearId) {
            return collect();
        }

        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('my_class_id', $assessment->course_id)
            ->where('academic_year_id', $academicYearId)
            ->pluck('student_record_id');

        if ($studentRecordIds->isEmpty()) {
            return collect();
        }

        return User::students()
            ->where('school_id', $schoolId)
            ->whereHas('studentRecord', function ($query) use ($studentRecordIds) {
                $query->whereIn('student_records.id', $studentRecordIds)
                    ->where('is_graduated', false);
            })
            ->with('studentRecord')
            ->orderBy('name')
            ->get();
    }

    protected function assessmentParticipantsQuery(): Builder
    {
        return $this->assessmentsForCurrentSchool()->with([
            'course',
            'lesson',
            'studentLocks',
            'studentAnswers' => function ($query) {
                $query->whereNotNull('submitted_at')
                    ->whereHas('user', function ($userQuery) {
                        $userQuery->where('school_id', $this->currentSchoolId());
                    })
                    ->with('user', 'question')
                    ->orderBy('user_id')
                    ->orderBy('attempt_number', 'desc');
            },
        ]);
    }
}
