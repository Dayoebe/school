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
    
    // Filters
    public $search = '';
    public $selectedClass = '';
    public $appliedClass = '';
    
    // Sorting & Pagination
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 15;
    
    // Data
    public $classes = [];
    
    // Subject form
    public $subjectId = null;
    public $name = '';
    public $short_name = '';
    public $selectedClasses = []; // Multiple classes
    public $selectedTeachers = [];
    public $teacherAssignments = []; // ['teacher_id' => ['class_id' => X, 'is_general' => false]]
    public $teacherSearch = '';

    protected $queryString = [
        'mode' => ['except' => 'list'],
        'search' => ['except' => ''],
        'appliedClass' => ['except' => ''],
    ];

    public function mount()
    {
        $this->classes = $this->getClassesForCurrentSchool();
        
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

    protected function getClassesForCurrentSchool()
    {
        $columns = \Schema::getColumnListing('my_classes');
        
        $query = MyClass::with('classGroup')->orderBy('name');
        
        if (in_array('school_id', $columns)) {
            $query->where('school_id', auth()->user()->school_id);
        }
        
        return $query->get();
    }

    public function switchMode($mode, $subjectId = null)
    {
        $this->mode = $mode;
        $this->subjectId = $subjectId;
        $this->resetValidation();
        
        if ($mode === 'edit' && $subjectId) {
            $this->loadSubjectForEdit();
        } elseif ($mode === 'create') {
            $this->resetForm();
        }
    }

    public function loadSubjectForEdit()
    {
        $subject = Subject::with(['teachers', 'classes'])->findOrFail($this->subjectId);
        
        if (!auth()->user()->can('update subject')) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->fill([
            'name' => $subject->name,
            'short_name' => $subject->short_name,
            'selectedClasses' => $subject->classes->pluck('id')->toArray(),
        ]);

        // Load teacher assignments with class specificity
        $this->teacherAssignments = [];
        foreach ($subject->teachers as $teacher) {
            $this->teacherAssignments[$teacher->id] = [
                'class_id' => $teacher->pivot->my_class_id,
                'is_general' => $teacher->pivot->is_general,
            ];
        }
        
        $this->selectedTeachers = array_keys($this->teacherAssignments);
    }

    public function toggleClass($classId)
    {
        if (in_array($classId, $this->selectedClasses)) {
            // Remove class
            $this->selectedClasses = array_values(array_filter($this->selectedClasses, fn($id) => $id != $classId));
            
            // Remove any teacher assignments specific to this class
            foreach ($this->teacherAssignments as $teacherId => $assignment) {
                if (!$assignment['is_general'] && $assignment['class_id'] == $classId) {
                    // Convert to general or remove
                    $this->teacherAssignments[$teacherId] = [
                        'class_id' => null,
                        'is_general' => true,
                    ];
                }
            }
        } else {
            $this->selectedClasses[] = $classId;
        }
    }

    public function toggleTeacher($teacherId)
    {
        if (in_array($teacherId, $this->selectedTeachers)) {
            $this->removeTeacher($teacherId);
        } else {
            $this->selectedTeachers[] = $teacherId;
            // Default to general assignment
            $this->teacherAssignments[$teacherId] = [
                'class_id' => null,
                'is_general' => true,
            ];
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

        $this->teacherAssignments[$teacherId] = [
            'class_id' => $classId,
            'is_general' => false,
        ];
    }

    public function setTeacherAsGeneral($teacherId)
    {
        if (!in_array($teacherId, $this->selectedTeachers)) {
            return;
        }

        $this->teacherAssignments[$teacherId] = [
            'class_id' => null,
            'is_general' => true,
        ];
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

    public function createSubject()
    {
        if (!auth()->user()->can('create subject')) {
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

        DB::transaction(function () {
            $subject = Subject::create([
                'name' => $this->name,
                'short_name' => $this->short_name,
                'school_id' => auth()->user()->school_id,
                'is_general' => true,
                'my_class_id' => null,
            ]);

            // Assign to classes
            foreach ($this->selectedClasses as $classId) {
                $subject->assignToClass($classId);
            }

            // Assign teachers
            foreach ($this->selectedTeachers as $teacherId) {
                $assignment = $this->teacherAssignments[$teacherId] ?? ['class_id' => null, 'is_general' => true];
                
                // Validate class assignment
                if (!$assignment['is_general'] && $assignment['class_id']) {
                    if (!in_array($assignment['class_id'], $this->selectedClasses)) {
                        continue; // Skip invalid assignment
                    }
                }
                
                $subject->assignTeacher($teacherId, $assignment['class_id'], $assignment['is_general']);
            }
        });

        session()->flash('success', 'Subject created successfully');
        $this->switchMode('list');
    }

    public function updateSubject()
    {
        $subject = Subject::findOrFail($this->subjectId);
        
        if (!auth()->user()->can('update subject')) {
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

        DB::transaction(function () use ($subject) {
            $subject->update([
                'name' => $this->name,
                'short_name' => $this->short_name,
            ]);

            // Sync classes with school_id in pivot (handle empty array)
            if (count($this->selectedClasses) > 0) {
                $syncData = [];
                foreach ($this->selectedClasses as $classId) {
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
            
            foreach ($this->selectedTeachers as $teacherId) {
                $assignment = $this->teacherAssignments[$teacherId] ?? ['class_id' => null, 'is_general' => true];
                
                // If no classes are selected, force general assignment
                if (count($this->selectedClasses) === 0) {
                    $assignment['class_id'] = null;
                    $assignment['is_general'] = true;
                }
                // Validate class assignment if classes exist
                elseif (!$assignment['is_general'] && $assignment['class_id']) {
                    if (!in_array($assignment['class_id'], $this->selectedClasses)) {
                        // Convert to general if class no longer selected
                        $assignment['class_id'] = null;
                        $assignment['is_general'] = true;
                    }
                }
                
                $subject->assignTeacher($teacherId, $assignment['class_id'], $assignment['is_general']);
            }
        });

        session()->flash('success', 'Subject updated successfully');
        $this->switchMode('list');
    }
    public function deleteSubject($subjectId)
    {
        $subject = Subject::findOrFail($subjectId);
        
        if (!auth()->user()->can('delete subject')) {
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
        return Subject::where('school_id', auth()->user()->school_id)
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
        
        if ($this->mode === 'list') {
            // FIX: Eager load classes with classGroup to prevent N+1
            $subjects = $this->getSubjectsQuery()
                ->with(['classes.classGroup', 'teachers'])
                ->withCount('teachers')
                ->paginate($this->perPage);
        }
    
        return view('livewire.subjects.manage-subjects', [
            'subjects' => $subjects,
            'classes' => $this->classes,
        ])
            ->layout('layouts.new', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('subjects.index'), 'text' => 'Subjects', 'active' => true]
                ]
            ])
            ->title('Manage Subjects');
    }
}