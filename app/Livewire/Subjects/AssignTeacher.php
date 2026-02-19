<?php

namespace App\Livewire\Subjects;

use App\Models\Subject;
use App\Models\MyClass;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class AssignTeacher extends Component
{
    public $selectedSubject = null;
    public $selectedTeacher = null;
    public $selectedClass = null;
    public $isGeneralAssignment = true;
    
    public $subjects = [];
    public $teachers = [];
    public $classes = [];
    
    public $searchSubject = '';
    public $searchTeacher = '';
    
    public $bulkMode = false;
    public $bulkSubjects = [];
    public $bulkTeacher = null;
    public $bulkClass = null;
    public $bulkIsGeneral = true;

    public function mount()
    {
        if (!auth()->user()->can('update subject')) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->loadData();
    }
    
    public function loadData()
    {
        // FIX: Eager load classes and teachers to prevent N+1
        $this->subjects = Subject::query()
            ->active()
            ->when($this->searchSubject, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->searchSubject . '%')
                      ->orWhere('short_name', 'like', '%' . $this->searchSubject . '%');
                });
            })
            ->with(['classes.classGroup', 'teachers'])
            ->orderBy('name')
            ->get();
            
        $this->teachers = User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->when($this->searchTeacher, function($query) {
                $query->where('name', 'like', '%' . $this->searchTeacher . '%');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        
        // FIX: Eager load classGroup
        $this->classes = MyClass::whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->with('classGroup')
            ->orderBy('name')
            ->get();
    }
    
    public function assign()
    {
        if (!auth()->user()->can('update subject')) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->validate([
            'selectedSubject' => 'required|exists:subjects,id',
            'selectedTeacher' => 'required|exists:users,id',
            'selectedClass' => 'required_if:isGeneralAssignment,false|nullable|exists:my_classes,id',
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

        if (!$this->isGeneralAssignment && $this->selectedClass && !$this->classBelongsToCurrentSchool($this->selectedClass)) {
            session()->flash('error', 'Selected class is not in your current school.');
            return;
        }
        
        DB::transaction(function () use ($subject) {
            // Check if subject is assigned to the selected class (if class-specific)
            if (!$this->isGeneralAssignment && $this->selectedClass) {
                if (!$subject->classes->contains($this->selectedClass)) {
                    throw new \Exception("Subject is not assigned to the selected class");
                }
            }
            
            $subject->assignTeacher(
                $this->selectedTeacher,
                $this->isGeneralAssignment ? null : $this->selectedClass,
                $this->isGeneralAssignment
            );
        });
        
        $assignmentType = $this->isGeneralAssignment 
            ? 'all classes' 
            : $this->getClassNameForCurrentSchool($this->selectedClass);
            
        session()->flash('success', "Teacher assigned successfully for {$assignmentType}!");
        
        $this->reset(['selectedSubject', 'selectedTeacher', 'selectedClass', 'isGeneralAssignment']);
        $this->isGeneralAssignment = true;
        $this->loadData();
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
            'bulkClass' => 'required_if:bulkIsGeneral,false|nullable|exists:my_classes,id',
        ]);

        $teacherExists = User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->where('id', $this->bulkTeacher)
            ->exists();
        if (!$teacherExists) {
            session()->flash('error', 'Selected teacher is not in your current school.');
            return;
        }

        if (!$this->bulkIsGeneral && $this->bulkClass && !$this->classBelongsToCurrentSchool($this->bulkClass)) {
            session()->flash('error', 'Selected class is not in your current school.');
            return;
        }

        $assignedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use (&$assignedCount, &$skippedCount) {
            foreach ($this->bulkSubjects as $subjectId) {
                $subject = Subject::query()
                    ->with('classes')
                    ->find($subjectId);
                
                if ($subject) {
                    // Verify class assignment if class-specific
                    if (!$this->bulkIsGeneral && $this->bulkClass) {
                        if (!$subject->classes->contains($this->bulkClass)) {
                            $skippedCount++;
                            continue;
                        }
                    }
                    
                    $subject->assignTeacher(
                        $this->bulkTeacher,
                        $this->bulkIsGeneral ? null : $this->bulkClass,
                        $this->bulkIsGeneral
                    );
                    $assignedCount++;
                }
            }
        });

        $assignmentType = $this->bulkIsGeneral 
            ? 'all classes' 
            : $this->getClassNameForCurrentSchool($this->bulkClass);
            
        $message = "{$assignedCount} subject(s) assigned to teacher for {$assignmentType}!";
        
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} skipped - subject not in selected class)";
        }
        
        session()->flash('success', $message);

        $this->reset(['bulkMode', 'bulkSubjects', 'bulkTeacher', 'bulkClass', 'bulkIsGeneral']);
        $this->bulkIsGeneral = true;
        $this->loadData();
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
        $this->loadData();
    }
    
    public function toggleBulkMode()
    {
        $this->bulkMode = !$this->bulkMode;
        $this->reset(['bulkSubjects', 'bulkTeacher', 'bulkClass', 'bulkIsGeneral']);
        $this->bulkIsGeneral = true;
    }
    
    public function toggleBulkSubject($subjectId)
    {
        if (in_array($subjectId, $this->bulkSubjects)) {
            $this->bulkSubjects = array_values(array_filter($this->bulkSubjects, fn($id) => $id != $subjectId));
        } else {
            $this->bulkSubjects[] = $subjectId;
        }
    }
    
    public function updatedSearchSubject()
    {
        $this->loadData();
    }
    
    public function updatedSearchTeacher()
    {
        $this->loadData();
    }
    
    public function updatedSelectedSubject()
    {
        $this->selectedClass = null;
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

    public function render()
    {
        return view('livewire.subjects.assign-teacher')
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
