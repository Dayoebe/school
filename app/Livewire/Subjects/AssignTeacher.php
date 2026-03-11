<?php

namespace App\Livewire\Subjects;

use App\Models\Subject;
use App\Models\MyClass;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AssignTeacher extends Component
{
    public $selectedSubject = null;
    public $selectedTeacher = null;
    public $selectedClasses = [];
    public $isGeneralAssignment = true;
    
    public $searchSubject = '';
    public $searchTeacher = '';
    
    public $bulkMode = false;
    public $bulkSubjects = [];
    public $bulkTeacher = null;
    public $bulkClasses = [];
    public $bulkIsGeneral = true;

    public function mount()
    {
        if (!auth()->user()->can('update subject')) {
            abort(403, 'Unauthorized action.');
        }
    }
    
    public function assign()
    {
        if (!auth()->user()->can('update subject')) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->validate([
            'selectedSubject' => 'required|exists:subjects,id',
            'selectedTeacher' => 'required|exists:users,id',
            'selectedClasses' => 'required_if:isGeneralAssignment,false|array|min:1',
            'selectedClasses.*' => 'exists:my_classes,id',
        ]);

        $subject = Subject::query()
            ->with('classes')
            ->find($this->selectedSubject);
        if (!$subject) {
            session()->flash('error', 'Selected subject is not in your current school.');
            return;
        }

        $teacherExists = User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->where('id', $this->selectedTeacher)
            ->exists();
        if (!$teacherExists) {
            session()->flash('error', 'Selected teacher is not in your current school.');
            return;
        }

        $selectedClassIds = $this->getValidClassIdsForCurrentSchool($this->selectedClasses);

        if (!$this->isGeneralAssignment && count($selectedClassIds) !== count($this->selectedClasses)) {
            $this->addError('selectedClasses', 'One or more selected classes are not in your current school.');
            return;
        }

        if (!$this->isGeneralAssignment) {
            $invalidClassIds = array_diff($selectedClassIds, $this->getAssignableClassIdsForSubject($subject));
            if ($invalidClassIds !== []) {
                $this->addError('selectedClasses', 'One or more selected classes are not assigned to this subject.');
                return;
            }
        }
        
        DB::transaction(function () use ($subject, $selectedClassIds) {
            if ($this->isGeneralAssignment) {
                $subject->assignTeacher(
                    $this->selectedTeacher,
                    null,
                    true
                );

                return;
            }

            foreach ($selectedClassIds as $classId) {
                if (!in_array($classId, $this->getAssignableClassIdsForSubject($subject), true)) {
                    throw new \Exception("Subject is not assigned to one or more selected classes");
                }

                $subject->assignTeacher(
                    $this->selectedTeacher,
                    $classId,
                    false
                );
            }
        });
        
        $assignmentType = $this->isGeneralAssignment 
            ? 'all classes' 
            : $this->formatClassSelectionSummary($selectedClassIds);
            
        session()->flash('success', "Teacher assigned successfully for {$assignmentType}!");
        
        $this->reset(['selectedSubject', 'selectedTeacher', 'selectedClasses', 'isGeneralAssignment']);
        $this->isGeneralAssignment = true;
    }
    
    public function bulkAssignTeacher()
    {
        if (!auth()->user()->can('update subject')) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->validate([
            'bulkSubjects' => 'required|array|min:1',
            'bulkSubjects.*' => 'exists:subjects,id',
            'bulkTeacher' => 'required|exists:users,id',
            'bulkClasses' => 'required_if:bulkIsGeneral,false|array|min:1',
            'bulkClasses.*' => 'exists:my_classes,id',
        ]);

        $teacherExists = User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->where('id', $this->bulkTeacher)
            ->exists();
        if (!$teacherExists) {
            session()->flash('error', 'Selected teacher is not in your current school.');
            return;
        }

        $bulkClassIds = $this->getValidClassIdsForCurrentSchool($this->bulkClasses);

        if (!$this->bulkIsGeneral && count($bulkClassIds) !== count($this->bulkClasses)) {
            $this->addError('bulkClasses', 'One or more selected classes are not in your current school.');
            return;
        }

        $assignedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use (&$assignedCount, &$skippedCount, $bulkClassIds) {
            foreach ($this->bulkSubjects as $subjectId) {
                $subject = Subject::query()
                    ->with(['classes', 'myClass'])
                    ->find($subjectId);
                
                if ($subject) {
                    if ($this->bulkIsGeneral) {
                        $subject->assignTeacher(
                            $this->bulkTeacher,
                            null,
                            true
                        );
                        $assignedCount++;
                        continue;
                    }

                    $assignableClassIds = $this->getAssignableClassIdsForSubject($subject);

                    foreach ($bulkClassIds as $classId) {
                        if (!in_array($classId, $assignableClassIds, true)) {
                            $skippedCount++;
                            continue;
                        }

                        $subject->assignTeacher(
                            $this->bulkTeacher,
                            $classId,
                            false
                        );
                        $assignedCount++;
                    }
                }
            }
        });

        $assignmentType = $this->bulkIsGeneral 
            ? 'all classes' 
            : $this->formatClassSelectionSummary($bulkClassIds);
            
        $message = "Teacher assignments updated successfully for {$assignmentType}. {$assignedCount} assignment(s) saved.";
        
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} skipped - subject not available in one or more selected classes)";
        }
        
        session()->flash('success', $message);

        $this->reset(['bulkMode', 'bulkSubjects', 'bulkTeacher', 'bulkClasses', 'bulkIsGeneral']);
        $this->bulkIsGeneral = true;
    }
    
    public function removeTeacher($subjectId, $teacherId, $classId = null)
    {
        if (!auth()->user()->can('update subject')) {
            abort(403, 'Unauthorized action.');
        }
        
        DB::transaction(function () use ($subjectId, $teacherId, $classId) {
            $subject = Subject::query()->find($subjectId);
            if (!$subject) {
                return;
            }

            $teacherExists = User::role('teacher')
                ->where('school_id', auth()->user()->school_id)
                ->where('id', $teacherId)
                ->exists();
            if (!$teacherExists) {
                return;
            }
            
            if ($classId) {
                if (!$this->classBelongsToCurrentSchool($classId)) {
                    return;
                }

                // Remove class-specific assignment
                $subject->teachers()->wherePivot('user_id', $teacherId)
                    ->wherePivot('my_class_id', $classId)
                    ->detach($teacherId);
            } else {
                // Remove general assignment
                $subject->teachers()->wherePivot('user_id', $teacherId)
                    ->wherePivot('is_general', true)
                    ->detach($teacherId);
            }
        });
        
        session()->flash('success', 'Teacher removed successfully!');
    }
    
    public function toggleBulkMode()
    {
        $this->bulkMode = !$this->bulkMode;
        $this->reset(['bulkSubjects', 'bulkTeacher', 'bulkClasses', 'bulkIsGeneral']);
        $this->bulkIsGeneral = true;
    }

    public function toggleSelectedClass($classId)
    {
        if (in_array($classId, $this->selectedClasses, true)) {
            $this->selectedClasses = array_values(array_filter(
                $this->selectedClasses,
                fn ($id) => (int) $id !== (int) $classId
            ));

            return;
        }

        $this->selectedClasses[] = (int) $classId;
    }

    public function toggleBulkClass($classId)
    {
        if (in_array($classId, $this->bulkClasses, true)) {
            $this->bulkClasses = array_values(array_filter(
                $this->bulkClasses,
                fn ($id) => (int) $id !== (int) $classId
            ));

            return;
        }

        $this->bulkClasses[] = (int) $classId;
    }
    
    public function toggleBulkSubject($subjectId)
    {
        if (in_array($subjectId, $this->bulkSubjects)) {
            $this->bulkSubjects = array_values(array_filter($this->bulkSubjects, fn($id) => $id != $subjectId));
        } else {
            $this->bulkSubjects[] = $subjectId;
        }
    }
    
    public function updatedSelectedSubject()
    {
        $this->selectedClasses = [];
    }

    public function getAvailableSelectedSubjectClassesProperty(): Collection
    {
        $classes = $this->classOptionsQuery()->get();

        if (!$this->selectedSubject) {
            return $classes;
        }

        $subject = Subject::query()
            ->active()
            ->with('classes')
            ->find((int) $this->selectedSubject);

        if (!$subject) {
            return collect();
        }

        $subjectClassIds = collect($subject->classes ?? [])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($subject->my_class_id && !in_array((int) $subject->my_class_id, $subjectClassIds, true)) {
            $subjectClassIds[] = (int) $subject->my_class_id;
        }

        return $classes->whereIn('id', $subjectClassIds)->values();
    }

    protected function subjectsQuery()
    {
        return Subject::query()
            ->active()
            ->when($this->searchSubject, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchSubject . '%')
                        ->orWhere('short_name', 'like', '%' . $this->searchSubject . '%');
                });
            })
            ->with(['classes', 'teachers'])
            ->orderBy('name');
    }

    protected function teachersQuery()
    {
        return User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->when($this->searchTeacher, function ($query) {
                $query->where('name', 'like', '%' . $this->searchTeacher . '%');
            })
            ->orderBy('name');
    }

    protected function classesQuery()
    {
        return MyClass::whereHas('classGroup', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })
            ->with('classGroup')
            ->orderBy('name');
    }

    protected function classOptionsQuery()
    {
        return MyClass::query()
            ->select('my_classes.*', 'class_groups.name as class_group_name')
            ->join('class_groups', 'class_groups.id', '=', 'my_classes.class_group_id')
            ->where('class_groups.school_id', auth()->user()->school_id)
            ->orderBy('my_classes.name');
    }

    protected function classBelongsToCurrentSchool($classId): bool
    {
        return MyClass::where('id', $classId)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->exists();
    }

    protected function getClassNameForCurrentSchool($classId): string
    {
        if (!$classId) {
            return 'all classes';
        }

        return MyClass::where('id', $classId)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->value('name') ?? 'selected class';
    }

    protected function getValidClassIdsForCurrentSchool(array $classIds): array
    {
        if ($classIds === []) {
            return [];
        }

        return MyClass::whereIn('id', $classIds)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function getAssignableClassIdsForSubject(Subject $subject): array
    {
        $classIds = $subject->classes->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($subject->my_class_id && !in_array((int) $subject->my_class_id, $classIds, true)) {
            $classIds[] = (int) $subject->my_class_id;
        }

        return array_values(array_unique($classIds));
    }

    protected function formatClassSelectionSummary(array $classIds): string
    {
        $classNames = MyClass::whereIn('id', $classIds)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->orderBy('name')
            ->pluck('name')
            ->all();

        if ($classNames === []) {
            return 'selected classes';
        }

        if (count($classNames) <= 3) {
            return implode(', ', $classNames);
        }

        return count($classNames) . ' classes';
    }

    public function render()
    {
        return view('livewire.subjects.assign-teacher', [
            'subjects' => $this->subjectsQuery()->get(),
            'teachers' => $this->teachersQuery()->get(['id', 'name', 'email']),
            'classes' => $this->classOptionsQuery()->get(),
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('subjects.index'), 'text' => 'Subjects'],
                    ['href' => route('subjects.assign-teacher'), 'text' => 'Assign Teacher', 'active' => true],
                ]
            ])
            ->title('Assign Teacher to Subjects');
    }
}
