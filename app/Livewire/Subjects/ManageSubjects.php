<?php

namespace App\Livewire\Subjects;

use App\Models\Subject;
use App\Models\MyClass;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ManageSubjects extends Component
{
    use WithPagination;

    public $mode = 'list';
    public $canCreateSubject = false;
    public $canUpdateSubject = false;
    public $canDeleteSubject = false;
    public $canManageIntegrityTools = false;
    
    // Filters
    public $search = '';
    public $selectedClass = '';
    public $appliedClass = '';
    
    // Sorting & Pagination
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 15;
    
    // Subject form
    public $subjectId = null;
    public $name = '';
    public $short_name = '';
    public $selectedClasses = []; // Multiple classes
    public $selectedTeachers = [];
    public $teacherAssignments = []; // ['teacher_id' => ['class_ids' => [X, Y], 'is_general' => false]]
    public $teacherSearch = '';

    protected $queryString = [
        'mode' => ['except' => 'list'],
        'search' => ['except' => ''],
        'appliedClass' => ['except' => ''],
    ];

    public function mount()
    {
        $this->setPermissionFlags();
        
        if (request()->routeIs('subjects.create')) {
            $this->mode = 'create';
            $this->resetForm();
        } elseif (request()->routeIs('subjects.edit') && request()->route('subject')) {
            $this->mode = 'edit';
            $this->subjectId = request()->route('subject');
            $this->loadSubjectForEdit();
        } else {
            $this->mode = 'list';
        }
    }

    public function hydrate()
    {
        $this->setPermissionFlags();

        if ($this->mode === 'create' && !$this->canCreateSubject) {
            $this->mode = 'list';
        }

        if ($this->mode === 'edit' && !$this->canUpdateSubject) {
            $this->mode = 'list';
            $this->subjectId = null;
        }
    }

    protected function setPermissionFlags(): void
    {
        $user = auth()->user();

        $this->canCreateSubject = $user->can('create subject');
        $this->canUpdateSubject = $user->can('update subject');
        $this->canDeleteSubject = $user->can('delete subject');
        $this->canManageIntegrityTools = $this->canUpdateSubject && $this->canDeleteSubject;
    }

    protected function getClassesForCurrentSchool()
    {
        return MyClass::whereHas('classGroup', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })
            ->with('classGroup')
            ->orderBy('name')
            ->get();
    }

    public function switchMode($mode, $subjectId = null)
    {
        $this->resetValidation();

        if ($mode === 'create') {
            if (!$this->canCreateSubject) {
                $this->denyModeChange('You do not have permission to create subjects.');
                return;
            }

            $this->mode = 'create';
            $this->subjectId = null;
            $this->resetForm();
            return;
        }

        if ($mode === 'edit' && $subjectId) {
            if (!$this->canUpdateSubject) {
                $this->denyModeChange('You do not have permission to edit subjects.');
                return;
            }

            $this->mode = 'edit';
            $this->subjectId = $subjectId;
            $this->loadSubjectForEdit();
            return;
        }

        $this->mode = 'list';
        $this->subjectId = null;
    }

    protected function denyModeChange(string $message): void
    {
        $this->mode = 'list';
        $this->subjectId = null;
        session()->flash('error', $message);
    }

    public function loadSubjectForEdit()
    {
        if (!$this->canUpdateSubject) {
            $this->denyModeChange('You do not have permission to edit subjects.');
            return;
        }

        $subject = Subject::query()
            ->with('classes')
            ->findOrFail($this->subjectId);

        $this->fill([
            'name' => $subject->name,
            'short_name' => $subject->short_name,
            'selectedClasses' => $subject->classes->pluck('id')->toArray(),
        ]);

        // Load teacher assignments with class specificity.
        $this->teacherAssignments = [];

        $assignmentRows = DB::table('subject_teacher')
            ->where('subject_id', $subject->id)
            ->select('user_id', 'my_class_id', 'is_general')
            ->orderBy('user_id')
            ->get()
            ->groupBy('user_id');

        foreach ($assignmentRows as $teacherId => $assignments) {
            $classIds = $assignments->pluck('my_class_id')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $this->teacherAssignments[(int) $teacherId] = $this->makeTeacherAssignment(
                $assignments->contains(fn ($assignment) => (bool) $assignment->is_general),
                $classIds
            );
        }
        
        $this->selectedTeachers = array_map('intval', array_keys($this->teacherAssignments));
    }

    public function toggleClass($classId)
    {
        if (!$this->classBelongsToCurrentSchool($classId)) {
            session()->flash('error', 'Selected class does not belong to your current school.');
            return;
        }

        if (in_array($classId, $this->selectedClasses)) {
            // Remove class
            $this->selectedClasses = array_values(array_filter($this->selectedClasses, fn($id) => $id != $classId));
            
            // Remove any teacher assignments specific to this class.
            foreach ($this->teacherAssignments as $teacherId => $assignment) {
                if ($assignment['is_general']) {
                    continue;
                }

                $remainingClassIds = array_values(array_filter(
                    $assignment['class_ids'] ?? [],
                    fn ($assignedClassId) => (int) $assignedClassId !== (int) $classId
                ));

                $this->teacherAssignments[$teacherId] = $this->makeTeacherAssignment(false, $remainingClassIds);
            }
        } else {
            $this->selectedClasses[] = $classId;
        }
    }

    public function toggleTeacher($teacherId)
    {
        if (!$this->teacherBelongsToCurrentSchool($teacherId)) {
            session()->flash('error', 'Selected teacher does not belong to your current school.');
            return;
        }

        if (in_array($teacherId, $this->selectedTeachers)) {
            $this->removeTeacher($teacherId);
        } else {
            $this->selectedTeachers[] = $teacherId;
            $this->teacherAssignments[$teacherId] = $this->makeTeacherAssignment();
        }
    }

    public function setTeacherClassAssignment($teacherId, $classId)
    {
        if (!in_array($teacherId, $this->selectedTeachers)) {
            return;
        }

        // Verify class is selected
        if (!in_array($classId, $this->selectedClasses)) {
            session()->flash('error', 'Cannot assign teacher to unselected class');
            return;
        }

        $assignment = $this->normalizeTeacherAssignment($this->teacherAssignments[$teacherId] ?? []);
        $assignedClassIds = $assignment['class_ids'];

        if (in_array((int) $classId, $assignedClassIds, true)) {
            $assignedClassIds = array_values(array_filter(
                $assignedClassIds,
                fn ($assignedClassId) => (int) $assignedClassId !== (int) $classId
            ));
        } else {
            $assignedClassIds[] = (int) $classId;
        }

        $this->teacherAssignments[$teacherId] = $this->makeTeacherAssignment(false, $assignedClassIds);
    }

    public function setTeacherAsGeneral($teacherId)
    {
        if (!in_array($teacherId, $this->selectedTeachers)) {
            return;
        }

        $this->teacherAssignments[$teacherId] = $this->makeTeacherAssignment();
    }

    public function getTeachersProperty()
    {
        return User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->when($this->teacherSearch, fn($q) => $q->where('name', 'like', "%{$this->teacherSearch}%"))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'email']);
    }

    public function removeTeacher($teacherId)
    {
        $this->selectedTeachers = array_values(array_filter($this->selectedTeachers, fn($id) => $id != $teacherId));
        unset($this->teacherAssignments[$teacherId]);
    }

    protected function makeTeacherAssignment(bool $isGeneral = true, array $classIds = []): array
    {
        $normalizedClassIds = collect($classIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($isGeneral || $normalizedClassIds === []) {
            return [
                'class_ids' => [],
                'is_general' => true,
            ];
        }

        return [
            'class_ids' => $normalizedClassIds,
            'is_general' => false,
        ];
    }

    protected function normalizeTeacherAssignment(array $assignment): array
    {
        return $this->makeTeacherAssignment(
            (bool) ($assignment['is_general'] ?? true),
            $assignment['class_ids'] ?? []
        );
    }

    public function createSubject()
    {
        if (!$this->canCreateSubject) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'selectedClasses' => 'required|array|min:1',
            'selectedClasses.*' => 'exists:my_classes,id',
            'selectedTeachers' => 'array',
            'selectedTeachers.*' => 'exists:users,id'
        ]);

        $validClassIds = $this->getValidClassIdsForCurrentSchool($this->selectedClasses);
        if (count($validClassIds) !== count($this->selectedClasses)) {
            $this->addError('selectedClasses', 'One or more selected classes are not in your current school.');
            return;
        }

        $validTeacherIds = $this->getValidTeacherIdsForCurrentSchool($this->selectedTeachers);
        if (count($validTeacherIds) !== count($this->selectedTeachers)) {
            $this->addError('selectedTeachers', 'One or more selected teachers are not in your current school.');
            return;
        }

        DB::transaction(function () use ($validClassIds, $validTeacherIds) {
            $subject = Subject::create([
                'name' => $this->name,
                'short_name' => $this->short_name,
                'school_id' => auth()->user()->school_id,
                'is_general' => true,
                'my_class_id' => null,
            ]);

            // Assign to classes
            foreach ($validClassIds as $classId) {
                $subject->assignToClass($classId);
            }

            // Assign teachers
            foreach ($validTeacherIds as $teacherId) {
                $assignment = $this->normalizeTeacherAssignment($this->teacherAssignments[$teacherId] ?? []);

                if ($assignment['is_general']) {
                    $subject->assignTeacher($teacherId, null, true);
                    continue;
                }

                $classIds = array_values(array_intersect($assignment['class_ids'], $validClassIds));

                foreach ($classIds as $classId) {
                    $subject->assignTeacher($teacherId, $classId, false);
                }
            }
        });

        session()->flash('success', 'Subject created successfully');
        $this->switchMode('list');
    }

    public function updateSubject()
    {
        $subject = Subject::query()
            ->findOrFail($this->subjectId);
        
        if (!$this->canUpdateSubject) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'selectedClasses' => 'array', // REMOVED: required|min:1 - Classes are now optional
            'selectedClasses.*' => 'exists:my_classes,id',
            'selectedTeachers' => 'array',
            'selectedTeachers.*' => 'exists:users,id'
        ]);

        $validClassIds = $this->getValidClassIdsForCurrentSchool($this->selectedClasses);
        if (count($validClassIds) !== count($this->selectedClasses)) {
            $this->addError('selectedClasses', 'One or more selected classes are not in your current school.');
            return;
        }

        $validTeacherIds = $this->getValidTeacherIdsForCurrentSchool($this->selectedTeachers);
        if (count($validTeacherIds) !== count($this->selectedTeachers)) {
            $this->addError('selectedTeachers', 'One or more selected teachers are not in your current school.');
            return;
        }

        DB::transaction(function () use ($subject, $validClassIds, $validTeacherIds) {
            $subject->update([
                'name' => $this->name,
                'short_name' => $this->short_name,
            ]);

            // Sync classes with school_id in pivot (handle empty array)
            if (count($this->selectedClasses) > 0) {
                $syncData = [];
                foreach ($validClassIds as $classId) {
                    $syncData[$classId] = ['school_id' => auth()->user()->school_id];
                }
                $subject->classes()->sync($syncData);
                
                // Re-assign to students in new classes
                $subject->autoAssignToClassStudents();
            } else {
                // If no classes selected, detach all classes
                $subject->classes()->detach();
                
                // Also remove all student-subject assignments since there are no classes
                DB::table('student_subject')
                    ->where('subject_id', $subject->id)
                    ->delete();
            }

            // Clear old teacher assignments and add new ones
            $subject->teachers()->detach();
            
            foreach ($validTeacherIds as $teacherId) {
                $assignment = $this->normalizeTeacherAssignment($this->teacherAssignments[$teacherId] ?? []);

                // If no classes are selected, force general assignment
                if (count($this->selectedClasses) === 0) {
                    $assignment = $this->makeTeacherAssignment();
                }

                if ($assignment['is_general']) {
                    $subject->assignTeacher($teacherId, null, true);
                    continue;
                }

                $classIds = array_values(array_intersect($assignment['class_ids'], $validClassIds));

                foreach ($classIds as $classId) {
                    $subject->assignTeacher($teacherId, $classId, false);
                }
            }
        });

        session()->flash('success', 'Subject updated successfully');
        $this->switchMode('list');
    }
    public function deleteSubject($subjectId)
    {
        $subject = Subject::query()->findOrFail($subjectId);
        
        if (!$this->canDeleteSubject) {
            abort(403, 'Unauthorized action.');
        }
        
        DB::transaction(function () use ($subject) {
            $subject->teachers()->detach();
            $subject->classes()->detach();
            if (method_exists($subject, 'timetableRecord') && $subject->timetableRecord()->exists()) {
                $subject->timetableRecord()->delete();
            }
            $subject->delete();
        });
        
        session()->flash('success', 'Subject deleted successfully');
    }

    protected function classBelongsToCurrentSchool($classId): bool
    {
        return MyClass::where('id', $classId)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->exists();
    }

    protected function teacherBelongsToCurrentSchool($teacherId): bool
    {
        return User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->where('id', $teacherId)
            ->exists();
    }

    protected function getValidClassIdsForCurrentSchool(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return MyClass::whereIn('id', $ids)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->pluck('id')
            ->toArray();
    }

    protected function getValidTeacherIdsForCurrentSchool(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->toArray();
    }

    public function applyFilters()
    {
        $this->appliedClass = $this->selectedClass;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'selectedClass', 'appliedClass']);
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetForm()
    {
        $this->reset([
            'subjectId', 'name', 'short_name', 'selectedClasses', 
            'selectedTeachers', 'teacherAssignments', 'teacherSearch'
        ]);
    }

    protected function getSubjectsQuery()
    {
        return Subject::query()
            ->active()
            ->when($this->appliedClass, function($q) {
                $q->whereHas('classes', function($query) {
                    $query->where('my_classes.id', $this->appliedClass);
                });
            })
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('short_name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $subjects = collect();
        $classes = $this->getClassesForCurrentSchool();
        
        if ($this->mode === 'list') {
            $subjects = $this->getSubjectsQuery()
                ->with(['classes.classGroup', 'teachers'])
                ->withCount('teachers')
                ->paginate($this->perPage);
        }
    
        return view('livewire.subjects.manage-subjects', [
            'subjects' => $subjects,
            'classes' => $classes,
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('subjects.index'), 'text' => 'Subjects', 'active' => true]
                ]
            ])
            ->title('Manage Subjects');
    }
}
