<?php

namespace App\Livewire;

use App\Models\ClassGroup;
use App\Models\MyClass;
use App\Models\Subject;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ManageClasses extends Component
{
    use WithPagination;

    // View states
    public $view = 'list'; // list, create-group, edit-group, view-group, create-class, edit-class, view-class
    
    // Form fields
    public $name = '';
    public $class_group_id = null;
    
    // Query parameters for direct navigation from menu
    public $action = null;
    
    // Selected records
    public $selectedClassGroup = null;
    public $selectedClass = null;
    
    // Search and filters
    public $search = '';
    public $showStudents = false;

    protected function rules()
    {
        $rules = ['name' => 'required|max:255'];
        
        if ($this->view === 'create-group' || $this->view === 'edit-group') {
            $rules['name'] = [
                'required',
                'max:255',
                function ($attribute, $value, $fail) {
                    $query = ClassGroup::where('school_id', auth()->user()->school_id)
                        ->where('name', $value);
                    
                    if ($this->view === 'edit-group' && $this->selectedClassGroup) {
                        $query->where('id', '!=', $this->selectedClassGroup->id);
                    }
                    
                    if ($query->exists()) {
                        $fail('This class group name already exists.');
                    }
                },
            ];
        }
        
        if ($this->view === 'create-class' || $this->view === 'edit-class') {
            $rules['class_group_id'] = 'required|exists:class_groups,id';
            $rules['name'] = [
                'required',
                'max:255',
                function ($attribute, $value, $fail) {
                    $query = MyClass::where('class_group_id', $this->class_group_id)
                        ->where('name', $value);
                    
                    if ($this->view === 'edit-class' && $this->selectedClass) {
                        $query->where('id', '!=', $this->selectedClass->id);
                    }
                    
                    if ($query->exists()) {
                        $fail('This class name already exists in the selected group.');
                    }
                },
            ];
        }
        
        return $rules;
    }

    // Handle query parameters from menu
    public function mount()
    {
        // Check for action in query string
        $action = request()->query('action');
        
        if ($action === 'create-class') {
            $this->showCreateClass();
        } elseif ($action === 'create-group') {
            $this->showCreateGroup();
        }
    }

    // View navigation methods
    public function showList()
    {
        $this->reset(['view', 'name', 'class_group_id', 'selectedClassGroup', 'selectedClass']);
        $this->view = 'list';
    }

    public function showCreateGroup()
    {
        $this->reset(['name', 'class_group_id']);
        $this->view = 'create-group';
    }

    public function showEditGroup($id)
    {
        $this->selectedClassGroup = ClassGroup::findOrFail($id);
        $this->authorize('update', $this->selectedClassGroup);
        $this->name = $this->selectedClassGroup->name;
        $this->view = 'edit-group';
    }

    public function showViewGroup($id)
    {
        $this->selectedClassGroup = ClassGroup::with('classes')->findOrFail($id);
        $this->authorize('view', $this->selectedClassGroup);
        $this->view = 'view-group';
    }

    public function showCreateClass()
    {
        $this->reset(['name', 'class_group_id']);
        $this->view = 'create-class';
    }

    public function showEditClass($id)
    {
        $this->selectedClass = MyClass::with('classGroup')->findOrFail($id);
        $this->authorize('update', $this->selectedClass);
        $this->name = $this->selectedClass->name;
        $this->class_group_id = $this->selectedClass->class_group_id;
        $this->view = 'edit-class';
    }

    public function showViewClass($id)
    {
        $this->selectedClass = MyClass::with(['classGroup', 'sections', 'subjects.teachers'])
            ->findOrFail($id);
        $this->authorize('view', $this->selectedClass);
        $this->showStudents = false;
        $this->view = 'view-class';
    }

    // CRUD operations
    public function createClassGroup()
    {
        $this->authorize('create', ClassGroup::class);
        $this->validate();

        ClassGroup::create([
            'name' => $this->name,
            'school_id' => auth()->user()->school_id,
        ]);

        session()->flash('success', 'Class Group created successfully!');
        $this->showList();
    }

    public function updateClassGroup()
    {
        $this->authorize('update', $this->selectedClassGroup);
        $this->validate();

        $this->selectedClassGroup->update(['name' => $this->name]);

        session()->flash('success', 'Class Group updated successfully!');
        $this->showList();
    }

    public function deleteClassGroup($id)
    {
        $classGroup = ClassGroup::findOrFail($id);
        $this->authorize('delete', $classGroup);

        if ($classGroup->classes()->count() > 0) {
            session()->flash('error', 'Cannot delete class group that contains classes.');
            return;
        }

        $classGroup->delete();
        session()->flash('success', 'Class Group deleted successfully!');
    }

    public function createClass()
    {
        $this->authorize('create', MyClass::class);
        $this->validate();

        MyClass::create([
            'name' => $this->name,
            'class_group_id' => $this->class_group_id,
        ]);

        session()->flash('success', 'Class created successfully!');
        $this->showList();
    }

    public function updateClass()
    {
        $this->authorize('update', $this->selectedClass);
        $this->validate();

        $this->selectedClass->update([
            'name' => $this->name,
            'class_group_id' => $this->class_group_id,
        ]);

        session()->flash('success', 'Class updated successfully!');
        $this->showList();
    }

    public function deleteClass($id)
    {
        $class = MyClass::findOrFail($id);
        $this->authorize('delete', $class);

        $currentAcademicYearId = auth()->user()->school->academic_year_id;
        $hasStudents = DB::table('academic_year_student_record')
            ->where('my_class_id', $class->id)
            ->where('academic_year_id', $currentAcademicYearId)
            ->exists();

        if ($hasStudents) {
            session()->flash('error', 'Cannot delete class that contains students.');
            return;
        }

        $class->delete();
        session()->flash('success', 'Class deleted successfully!');
    }

    public function assignSubjects()
    {
        $this->authorize('update', $this->selectedClass);
        
        $subjects = Subject::where('my_class_id', $this->selectedClass->id)->get();
        $students = $this->getStudentsForClass($this->selectedClass->id);

        foreach ($students as $student) {
            $syncData = [];
            foreach ($subjects as $subject) {
                $syncData[$subject->id] = [
                    'my_class_id' => $this->selectedClass->id,
                    'section_id' => $student->section_id,
                ];
            }
            $student->studentSubjects()->syncWithoutDetaching($syncData);
        }

        session()->flash('success', 'Subjects assigned to students successfully!');
        $this->showViewClass($this->selectedClass->id);
    }

    private function getStudentsForClass($classId)
    {
        $currentAcademicYearId = auth()->user()->school->academic_year_id;
        
        if (!$currentAcademicYearId) {
            return collect();
        }

        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('my_class_id', $classId)
            ->where('academic_year_id', $currentAcademicYearId)
            ->pluck('student_record_id');

        if ($studentRecordIds->isEmpty()) {
            return collect();
        }

        return StudentRecord::whereIn('id', $studentRecordIds)
            ->with(['user', 'studentSubjects', 'myClass', 'section'])
            ->get();
    }

    public function render()
    {
        $classGroups = ClassGroup::where('school_id', auth()->user()->school_id)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->withCount('classes')
            ->paginate(10);

        $classes = MyClass::whereHas('classGroup', fn($q) => 
                $q->where('school_id', auth()->user()->school_id)
            )
            ->with('classGroup')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->paginate(10);

        $allClassGroups = ClassGroup::where('school_id', auth()->user()->school_id)->get();
        
        $students = null;
        if ($this->view === 'view-class' && $this->showStudents) {
            $students = $this->getStudentsForClass($this->selectedClass->id);
        }

        return view('livewire.manage-classes', [
            'classGroups' => $classGroups,
            'classes' => $classes,
            'allClassGroups' => $allClassGroups,
            'students' => $students,
        ])
        ->layout('layouts.new');
    }
}