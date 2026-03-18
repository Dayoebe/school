<?php

namespace App\Livewire\Cbt;

use Livewire\Component;
use App\Models\Assessment\Assessment;
use App\Traits\ResolvesAccessibleStudents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard', ['title' => 'View CBT Exam', 'description' => 'View CBT exams', 'icon' => 'fas fa-microphone-alt'])]
class CbtViewer extends Component
{
    use WithPagination;
    use ResolvesAccessibleStudents;

    public $selectedAssessment = null;
    public $selectedAttempt = null;
    public $viewDetails = false;
    public $selectedStudentId = '';
    public array $availableStudents = [];

    public function mount()
    {
        if ($this->isParentStudentPortalViewer()) {
            $this->loadAvailableStudents();
            $this->selectedStudentId = (string) ($this->availableStudents[0]['id'] ?? '');
            return;
        }

        if ($this->isStudentStudentPortalViewer()) {
            $this->selectedStudentId = (string) Auth::id();
        }
    }

    public function updatedSelectedStudentId()
    {
        $this->closeDetails();
        $this->resetPage();
    }

    public function render()
    {
        $canViewUnpublished = $this->canViewUnpublishedResults();
        $isParentViewer = $this->isParentStudentPortalViewer();
        $selectedStudentProfile = $this->selectedStudentProfile();
        $targetStudentId = $this->currentTargetStudentId();

        $userAssessments = Assessment::query()
            ->whereRaw('1 = 0')
            ->paginate(10);

        if ($targetStudentId !== null) {
            $userAssessments = $this->assessmentsForCurrentSchool($targetStudentId)
                ->when(!$canViewUnpublished, fn ($query) => $query->whereNotNull('results_published_at'))
                ->with([
                    'course:id,name',
                    'studentAnswers' => function($query) use ($targetStudentId) {
                        $query->where('user_id', $targetStudentId)
                            ->whereNotNull('submitted_at')
                            ->with('question');
                    },
                    'questions'
                ])
                ->paginate(10);
        }

        return view('livewire.cbt.cbt-viewer', compact(
            'userAssessments',
            'isParentViewer',
            'selectedStudentProfile'
        ));
    }

    public function viewAssessmentDetails($assessmentId)
    {
        $targetStudentId = $this->currentTargetStudentId();

        if ($targetStudentId === null) {
            session()->flash('error', 'No linked student is available for CBT results.');
            return;
        }

        $this->selectedAssessment = $this->assessmentsForCurrentSchool($targetStudentId)
            ->when(!$this->canViewUnpublishedResults(), fn ($query) => $query->whereNotNull('results_published_at'))
            ->with([
            'course:id,name',
            'studentAnswers' => function($query) use ($targetStudentId) {
                $query->where('user_id', $targetStudentId)
                    ->whereNotNull('submitted_at')
                    ->with('question')
                    ->orderBy('attempt_number', 'desc');
            },
            'questions'
        ])->find($assessmentId);

        if (!$this->selectedAssessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $this->viewDetails = true;
    }

    public function viewAttemptDetails($attemptNumber)
    {
        $targetStudentId = $this->currentTargetStudentId();

        if ($targetStudentId === null || !$this->selectedAssessment) {
            session()->flash('error', 'No linked student is available for CBT results.');
            return;
        }

        $this->selectedAttempt = $this->selectedAssessment->getStudentResults($targetStudentId, $attemptNumber);

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
        $targetStudentId = $this->currentTargetStudentId();

        if ($targetStudentId === null) {
            return collect();
        }

        $attempts = $assessment->studentAnswers
            ->where('user_id', $targetStudentId)
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

    protected function assessmentsForCurrentSchool(int $studentId): Builder
    {
        return Assessment::query()
            ->standaloneCBT()
            ->forSchool(auth()->user()?->school_id)
            ->whereHas('studentAnswers', function (Builder $query) use ($studentId) {
                $query->where('user_id', $studentId)
                    ->whereNotNull('submitted_at');
            });
    }

    protected function canViewUnpublishedResults(): bool
    {
        return (bool) auth()->user()?->can('manage cbt');
    }

    protected function currentTargetStudentId(): ?int
    {
        if ($this->isStudentStudentPortalViewer()) {
            return Auth::id() ? (int) Auth::id() : null;
        }

        if (!$this->isParentStudentPortalViewer()) {
            return null;
        }

        $studentId = (int) $this->selectedStudentId;

        if ($studentId <= 0) {
            return null;
        }

        $isAccessible = collect($this->availableStudents)
            ->contains(fn (array $student): bool => (int) $student['id'] === $studentId);

        return $isAccessible ? $studentId : null;
    }

    protected function loadAvailableStudents(): void
    {
        $this->availableStudents = $this->portalAccessibleStudentsQuery()
            ->with(['studentRecord.myClass', 'studentRecord.section'])
            ->orderBy('name')
            ->get()
            ->map(function ($student): array {
                $record = $student->studentRecord;

                return [
                    'id' => (int) $student->id,
                    'name' => (string) $student->name,
                    'admission_number' => (string) ($record?->admission_number ?: 'N/A'),
                    'class_name' => (string) ($record?->myClass?->name ?? 'Not assigned'),
                    'section_name' => (string) ($record?->section?->name ?? 'Not assigned'),
                ];
            })
            ->values()
            ->all();
    }

    protected function selectedStudentProfile(): ?array
    {
        if (!$this->isParentStudentPortalViewer()) {
            return null;
        }

        return collect($this->availableStudents)
            ->first(fn (array $student): bool => (int) $student['id'] === (int) $this->selectedStudentId);
    }
}
