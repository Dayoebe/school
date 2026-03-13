<?php

namespace App\Livewire\Teachers;

use App\Models\User;
use App\Models\Subject;
use App\Support\TeacherResponsibilityBuilder;
use Illuminate\Support\Collection;
use Livewire\Component;

class TeacherDetail extends Component
{
    public User $teacher;
    public $activeTab = 'profile';
    public $availableSubjects = [];
    public $subjectSearch = '';
    public $assignedSubjects = [];
    public $teacherSubjects = [];
    public $subjectAssignmentGroups = [];
    public $teacherSummary = [
        'assigned_subjects' => 0,
        'teaching_classes' => 0,
        'teaching_assignments' => 0,
    ];

    public function mount($teacherId)
    {
        $this->loadTeacher((int) $teacherId);
    }

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function getAvailableSubjects()
    {
        return Subject::query()
            ->when($this->subjectSearch, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->subjectSearch . '%')
                          ->orWhere('short_name', 'like', '%' . $this->subjectSearch . '%');
                });
            })
            ->whereNotIn('id', $this->teacherSubjects)
            ->with('myClass')
            ->limit(10)
            ->get();
    }

    public function assignSubject($subjectId)
    {
        $subject = Subject::query()
            ->findOrFail($subjectId);
        
        if (!in_array($subjectId, $this->teacherSubjects)) {
            $this->teacher->subjects()->syncWithoutDetaching([
                $subjectId => [
                    'school_id' => auth()->user()->school_id,
                    'is_general' => true,
                ],
            ]);
            $this->loadTeacher((int) $this->teacher->id);
            
            session()->flash('success', 'Subject assigned successfully');
        }
    }

    public function removeSubject($subjectId)
    {
        $this->teacher->subjects()->detach($subjectId);
        $this->loadTeacher((int) $this->teacher->id);
        
        session()->flash('success', 'Subject removed successfully');
    }

    public function render()
    {
        $availableSubjects = $this->getAvailableSubjects();

        return view('livewire.teachers.teacher-detail', [
            'teacher' => $this->teacher,
            'availableSubjects' => $availableSubjects,
            'subjectAssignmentGroups' => $this->subjectAssignmentGroups,
            'teacherSummary' => $this->teacherSummary,
        ])
        ->layout('layouts.dashboard', [
            'breadcrumbs' => [
                ['href' => route('dashboard'), 'text' => 'Dashboard'],
                ['href' => route('teachers.index'), 'text' => 'Teachers'],
                ['href' => route('teachers.show', $this->teacher->id), 'text' => $this->teacher->name, 'active' => true],
            ]
        ])
        ->title($this->teacher->name . "'s Profile");
    }

    protected function loadTeacher(int $teacherId): void
    {
        $this->teacher = User::with(['subjects', 'subjects.myClass', 'school'])
            ->role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($teacherId);

        $this->teacherSubjects = $this->teacher->subjects()
            ->pluck('subjects.id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $this->refreshTeacherAssignments();
    }

    protected function refreshTeacherAssignments(): void
    {
        $overview = app(TeacherResponsibilityBuilder::class)->build($this->teacher);

        $assignments = collect($overview['subject_assignments'] ?? [])
            ->map(fn (array $assignment): array => [
                'subject_id' => (int) ($assignment['subject_id'] ?? 0),
                'subject_name' => $assignment['subject_name'] ?? 'Unknown subject',
                'subject_short_name' => $assignment['subject_short_name'] ?? null,
                'class_id' => (int) ($assignment['class_id'] ?? 0),
                'class_name' => $assignment['class_name'] ?? 'Unassigned class',
                'class_group' => $assignment['class_group'] ?? null,
                'assignment_scope' => $assignment['assignment_scope'] ?? 'Subject assignment',
                'is_managed_class' => (bool) ($assignment['is_managed_class'] ?? false),
            ])
            ->filter(fn (array $assignment): bool => $assignment['subject_id'] > 0 && $assignment['class_id'] > 0)
            ->values();

        $this->subjectAssignmentGroups = $assignments
            ->groupBy('subject_id')
            ->map(function (Collection $subjectAssignments): array {
                $firstAssignment = $subjectAssignments->first();

                return [
                    'subject_id' => $firstAssignment['subject_id'],
                    'subject_name' => $firstAssignment['subject_name'],
                    'subject_short_name' => $firstAssignment['subject_short_name'],
                    'class_count' => $subjectAssignments->pluck('class_id')->unique()->count(),
                    'assignment_scopes' => $subjectAssignments->pluck('assignment_scope')->unique()->values()->all(),
                    'classes' => $subjectAssignments
                        ->sortBy(fn (array $assignment): string => strtolower($assignment['class_name']))
                        ->map(fn (array $assignment): array => [
                            'class_id' => $assignment['class_id'],
                            'class_name' => $assignment['class_name'],
                            'class_group' => $assignment['class_group'],
                            'assignment_scope' => $assignment['assignment_scope'],
                            'is_managed_class' => $assignment['is_managed_class'],
                        ])
                        ->unique('class_id')
                        ->values()
                        ->all(),
                ];
            })
            ->sortBy(fn (array $assignment): string => strtolower($assignment['subject_name']))
            ->values()
            ->all();

        $this->teacherSummary = [
            'assigned_subjects' => (int) ($overview['assigned_subjects'] ?? 0),
            'teaching_classes' => (int) ($overview['teaching_classes'] ?? 0),
            'teaching_assignments' => (int) ($overview['teaching_assignments'] ?? 0),
        ];
    }
}
