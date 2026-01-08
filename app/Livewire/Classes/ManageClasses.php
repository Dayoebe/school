<?php

namespace App\Livewire\Classes;

use App\Models\ClassGroup;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\StudentRecord;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ManageClasses extends Component
{
    use WithPagination, AuthorizesRequests;

    // ============================================
    // VIEW STATE
    // ============================================
    public $view = 'list';
    public $search = '';
    public $selectedClass = null;

    // ============================================
    // CLASS FORM
    // ============================================
    public $name = '';
    public $class_group_id = null;

    // ============================================
    // STUDENT MANAGEMENT
    // ============================================
    public $selectedStudents = [];
    public $selectAll = false;
    public $studentsCount = 0;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // ============================================
    // TEACHER MANAGEMENT
    // ============================================
    public $showTeacherModal = false;
    public $selectedTeachers = [];
    public $classTeachers = [];

    // ============================================
    // SECTION MANAGEMENT
    // ============================================
    public $showSectionModal = false;
    public $sectionName = '';
    public $editingSectionId = null;
    public $editingStudentSection = null;
    public $editingStudentSectionId = null;

    // ============================================
    // SUBJECT MANAGEMENT (UPDATED)
    // ============================================
    public $showSubjectModal = false;
    public $availableSubjects = [];
    public $subjectSearch = '';
    public $selectedSubjectIds = [];

    // ============================================
    // BULK OPERATIONS
    // ============================================
    public $targetClassId = null;
    public $targetSectionId = null;

    // ============================================
    // VALIDATION RULES
    // ============================================
    protected function rules()
    {
        return [
            'name' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (!$this->class_group_id) return;

                    $query = MyClass::where('class_group_id', $this->class_group_id)
                        ->where('name', $value);

                    if ($this->view === 'edit' && $this->selectedClass) {
                        $query->where('id', '!=', $this->selectedClass->id);
                    }

                    if ($query->exists()) {
                        $fail('This class name already exists in the selected group.');
                    }
                },
            ],
            'class_group_id' => 'required|exists:class_groups,id',
        ];
    }

    // ============================================
    // LIFECYCLE HOOKS
    // ============================================
    public function mount()
    {
        $action = request()->query('action');
        $viewId = request()->query('view');

        if ($action === 'create') {
            $this->showCreate();
        } elseif ($viewId) {
            $this->showView($viewId);
        }
    }

    // ============================================
    // VIEW NAVIGATION
    // ============================================
    public function showList()
    {
        $this->reset(['view', 'name', 'class_group_id', 'selectedClass', 'selectedStudents', 'selectAll']);
        $this->view = 'list';
    }

    public function showCreate()
    {
        $this->authorize('create', MyClass::class);
        $this->reset(['name', 'class_group_id']);
        $this->view = 'create';
    }

    public function showEdit($id)
    {
        $this->selectedClass = MyClass::with('classGroup')->findOrFail($id);
        $this->authorize('update', $this->selectedClass);
        $this->name = $this->selectedClass->name;
        $this->class_group_id = $this->selectedClass->class_group_id;
        $this->view = 'edit';
    }

    public function showView($id)
    {
        $this->selectedClass = MyClass::with([
            'classGroup', 
            'sections', 
            'subjects.teachers',
            'subjects.classes.classGroup' // Also load the classes for each subject
        ])->findOrFail($id);
        
        $this->authorize('view', $this->selectedClass);
        
        $this->selectedStudents = [];
        $this->selectAll = false;
        $this->loadClassTeachers();
        $this->studentsCount = $this->selectedClass->studentsCount();
        
        $this->view = 'view';
    }
    
    
    // ============================================
    // CLASS CRUD OPERATIONS
    // ============================================
    public function create()
    {
        $this->authorize('create', MyClass::class);
        $this->validate();

        $class = MyClass::create([
            'name' => $this->name,
            'class_group_id' => $this->class_group_id,
        ]);

        session()->flash('success', 'Class created successfully!');
        $this->showList();
    }

    public function update()
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

    public function delete($id)
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

    // ============================================
    // STUDENT OPERATIONS
    // ============================================
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedStudents = $this->getStudentsForClass($this->selectedClass->id)
                ->pluck('id')->toArray();
        } else {
            $this->selectedStudents = [];
        }
    }

    public function updatedSelectedStudents()
    {
        $totalStudents = $this->getStudentsForClass($this->selectedClass->id)->count();
        $this->selectAll = count($this->selectedStudents) === $totalStudents;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    private function getStudentsForClass($classId)
    {
        $currentAcademicYearId = auth()->user()->school->academic_year_id;
        
        if (!$currentAcademicYearId) {
            return collect();
        }
    
        $pivotData = DB::table('academic_year_student_record')
            ->where('my_class_id', $classId)
            ->where('academic_year_id', $currentAcademicYearId)
            ->select('student_record_id', 'my_class_id', 'section_id')
            ->get()
            ->keyBy('student_record_id');
    
        $studentRecordIds = $pivotData->pluck('student_record_id');
    
        if ($studentRecordIds->isEmpty()) {
            return collect();
        }
    
        $classIds = $pivotData->pluck('my_class_id')->unique();
        $sectionIds = $pivotData->pluck('section_id')->filter()->unique();
        
        $classes = MyClass::whereIn('id', $classIds)->get()->keyBy('id');
        $sections = Section::whereIn('id', $sectionIds)->get()->keyBy('id');
    
        $query = StudentRecord::whereIn('student_records.id', $studentRecordIds)
            ->with(['user', 'studentSubjects'])
            ->select('student_records.*')
            ->join('users', 'student_records.user_id', '=', 'users.id')
            ->leftJoin('sections', 'student_records.section_id', '=', 'sections.id')
            ->whereNull('users.deleted_at');
    
        switch ($this->sortField) {
            case 'name':
                $query->orderBy('users.name', $this->sortDirection);
                break;
            case 'section':
                if ($this->sortDirection === 'asc') {
                    $query->orderByRaw('sections.name IS NULL, sections.name ASC');
                } else {
                    $query->orderByRaw('sections.name IS NOT NULL, sections.name DESC');
                }
                break;
            case 'email':
                $query->orderBy('users.email', $this->sortDirection);
                break;
            default:
                $query->orderBy('users.name', 'asc');
        }
    
        $students = $query->get();
    
        $students->each(function($student) use ($pivotData, $classes, $sections) {
            if (isset($pivotData[$student->id])) {
                $pivot = $pivotData[$student->id];
                $student->setRelation('myClass', $classes->get($pivot->my_class_id));
                $student->setRelation('section', $pivot->section_id ? $sections->get($pivot->section_id) : null);
            }
        });
    
        return $students;
    }
    
    public function showEditStudentSection($studentId)
    {
        $student = StudentRecord::findOrFail($studentId);
        $this->editingStudentSectionId = $student->id;
        $this->editingStudentSection = $student->section_id;
        $this->dispatch('open-modal', id: 'student-section-modal');
    }

    public function updateStudentSection()
    {
        if (!$this->editingStudentSectionId) return;

        $student = StudentRecord::findOrFail($this->editingStudentSectionId);
        $currentAcademicYearId = auth()->user()->school->academic_year_id;

        $isInClass = DB::table('academic_year_student_record')
            ->where('student_record_id', $student->id)
            ->where('my_class_id', $this->selectedClass->id)
            ->where('academic_year_id', $currentAcademicYearId)
            ->exists();

        if (!$isInClass) {
            session()->flash('error', 'Student is not in this class.');
            return;
        }

        DB::table('academic_year_student_record')
            ->where('student_record_id', $student->id)
            ->where('academic_year_id', $currentAcademicYearId)
            ->update(['section_id' => $this->editingStudentSection]);

        $student->update(['section_id' => $this->editingStudentSection]);
        $student->assignSubjectsAutomatically();

        session()->flash('success', 'Student section updated successfully!');

        $this->reset(['editingStudentSection', 'editingStudentSectionId']);
        $this->dispatch('close-modal', id: 'student-section-modal');
        $this->showView($this->selectedClass->id);
    }

    // ============================================
    // BULK STUDENT OPERATIONS
    // ============================================
    public function updateMultipleStudentsSection()
    {
        if (empty($this->selectedStudents) || !$this->targetSectionId) {
            session()->flash('error', 'Please select students and a target section.');
            return;
        }

        $currentAcademicYearId = auth()->user()->school->academic_year_id;
        $updatedCount = 0;

        foreach ($this->selectedStudents as $studentRecordId) {
            $student = StudentRecord::find($studentRecordId);
            if (!$student) continue;

            DB::table('academic_year_student_record')
                ->where('student_record_id', $studentRecordId)
                ->where('academic_year_id', $currentAcademicYearId)
                ->update(['section_id' => $this->targetSectionId]);

            $student->update(['section_id' => $this->targetSectionId]);
            $student->assignSubjectsAutomatically();
            $updatedCount++;
        }

        session()->flash('success', "{$updatedCount} students moved to new section successfully!");
        $this->reset(['selectedStudents', 'selectAll', 'targetSectionId']);
        $this->showView($this->selectedClass->id);
    }

    public function moveStudents()
    {
        if (empty($this->selectedStudents) || !$this->targetClassId) {
            session()->flash('error', 'Please select students and target class.');
            return;
        }

        $currentAcademicYearId = auth()->user()->school->academic_year_id;

        foreach ($this->selectedStudents as $studentRecordId) {
            DB::table('academic_year_student_record')
                ->where('student_record_id', $studentRecordId)
                ->where('academic_year_id', $currentAcademicYearId)
                ->update([
                    'my_class_id' => $this->targetClassId,
                    'section_id' => $this->targetSectionId,
                ]);

            StudentRecord::where('id', $studentRecordId)->update([
                'my_class_id' => $this->targetClassId,
                'section_id' => $this->targetSectionId,
            ]);

            $student = StudentRecord::find($studentRecordId);
            if ($student) {
                $student->assignSubjectsAutomatically();
            }
        }

        session()->flash('success', count($this->selectedStudents) . ' students moved successfully!');
        $this->reset(['selectedStudents', 'selectAll', 'targetClassId', 'targetSectionId']);
        $this->showView($this->selectedClass->id);
    }

    public function deleteSelectedStudents()
    {
        if (empty($this->selectedStudents)) {
            session()->flash('error', 'Please select students to delete.');
            return;
        }

        foreach ($this->selectedStudents as $studentRecordId) {
            $studentRecord = StudentRecord::find($studentRecordId);
            if ($studentRecord) {
                $user = $studentRecord->user;
                $studentRecord->delete();
                $user->delete();
            }
        }

        session()->flash('success', count($this->selectedStudents) . ' students deleted successfully!');
        $this->reset(['selectedStudents', 'selectAll']);
        $this->showView($this->selectedClass->id);
    }

    // ============================================
    // SUBJECT OPERATIONS (UPDATED)
    // ============================================
    public function showAddSubjects()
    {
        $this->authorize('update', $this->selectedClass);
        
        // Get subjects not already in this class
        $currentSubjectIds = $this->selectedClass->subjects->pluck('id')->toArray();
        
        $this->availableSubjects = Subject::where('school_id', auth()->user()->school_id)
            ->active()
            ->whereNotIn('id', $currentSubjectIds)
            ->with('classes')
            ->orderBy('name')
            ->get();
        
        $this->selectedSubjectIds = [];
        $this->subjectSearch = '';
        $this->showSubjectModal = true;
    }

    public function toggleSubjectSelection($subjectId)
    {
        if (in_array($subjectId, $this->selectedSubjectIds)) {
            $this->selectedSubjectIds = array_values(array_filter($this->selectedSubjectIds, fn($id) => $id != $subjectId));
        } else {
            $this->selectedSubjectIds[] = $subjectId;
        }
    }

    public function addSelectedSubjects()
    {
        $this->authorize('update', $this->selectedClass);

        if (empty($this->selectedSubjectIds)) {
            session()->flash('error', 'Please select at least one subject.');
            return;
        }

        $addedCount = 0;
        
        DB::transaction(function() use (&$addedCount) {
            foreach ($this->selectedSubjectIds as $subjectId) {
                $subject = Subject::find($subjectId);
                
                if ($subject) {
                    // Add this class to the subject's classes
                    $subject->assignToClass($this->selectedClass->id);
                    $addedCount++;
                }
            }
        });

        session()->flash('success', "{$addedCount} subject(s) added to class successfully!");
        
        $this->reset(['showSubjectModal', 'selectedSubjectIds', 'subjectSearch', 'availableSubjects']);
        
        // Reload the class with fresh subjects data
        $this->selectedClass->refresh();
        $this->selectedClass->load(['classGroup', 'sections', 'subjects.teachers', 'subjects.classes']);
        
        $this->showView($this->selectedClass->id);
    }

    public function removeSubjectFromClass($subjectId)
    {
        $this->authorize('update', $this->selectedClass);
        
        $subject = Subject::findOrFail($subjectId);
        
        // Remove this class from the subject
        $subject->removeFromClass($this->selectedClass->id);
        
        session()->flash('success', 'Subject removed from class successfully!');
        $this->showView($this->selectedClass->id);
    }

    public function getFilteredAvailableSubjectsProperty()
    {
        if (empty($this->subjectSearch)) {
            return $this->availableSubjects;
        }

        return $this->availableSubjects->filter(function($subject) {
            return stripos($subject->name, $this->subjectSearch) !== false || 
                   stripos($subject->short_name, $this->subjectSearch) !== false;
        });
    }

    // ============================================
    // SECTION OPERATIONS
    // ============================================
    public function showCreateSection()
    {
        $this->reset(['sectionName', 'editingSectionId']);
        $this->showSectionModal = true;
    }

    public function showEditSection($sectionId)
    {
        $section = Section::findOrFail($sectionId);
        $this->editingSectionId = $section->id;
        $this->sectionName = $section->name;
        $this->showSectionModal = true;
    }

    public function saveSection()
    {
        $this->validate([
            'sectionName' => 'required|max:255',
        ]);

        if ($this->editingSectionId) {
            $section = Section::findOrFail($this->editingSectionId);
            $section->update(['name' => $this->sectionName]);
            session()->flash('success', 'Section updated successfully!');
        } else {
            Section::create([
                'name' => $this->sectionName,
                'my_class_id' => $this->selectedClass->id,
            ]);
            session()->flash('success', 'Section created successfully!');
        }

        $this->showSectionModal = false;
        $this->showView($this->selectedClass->id);
    }

    public function deleteSection($sectionId)
    {
        $section = Section::findOrFail($sectionId);

        if ($section->studentsCount() > 0) {
            session()->flash('error', 'Cannot delete section with students.');
            return;
        }

        $section->delete();
        session()->flash('success', 'Section deleted successfully!');
        $this->showView($this->selectedClass->id);
    }

    // ============================================
    // TEACHER OPERATIONS
    // ============================================
    public function toggleTeacherModal()
    {
        $this->showTeacherModal = !$this->showTeacherModal;
        if ($this->showTeacherModal) {
            $this->loadClassTeachers();
            $this->selectedTeachers = collect($this->classTeachers)->pluck('id')->toArray();
        }
    }

    protected function loadClassTeachers()
    {
        $this->classTeachers = DB::table('class_teacher')
            ->where('class_id', $this->selectedClass->id)
            ->join('users', 'class_teacher.teacher_id', '=', 'users.id')
            ->select('users.*')
            ->get()
            ->toArray();
    }

    public function updateClassTeachers()
    {
        $this->authorize('update', $this->selectedClass);

        DB::table('class_teacher')->where('class_id', $this->selectedClass->id)->delete();

        foreach ($this->selectedTeachers as $teacherId) {
            DB::table('class_teacher')->insert([
                'class_id' => $this->selectedClass->id,
                'teacher_id' => $teacherId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->loadClassTeachers();
        $this->showTeacherModal = false;
        session()->flash('success', 'Class teachers updated successfully!');
    }

    // ============================================
    // RENDER
    // ============================================
    public function render()
    {
        $classes = MyClass::whereHas('classGroup', fn($q) => 
                $q->where('school_id', auth()->user()->school_id)
            )
            ->with(['classGroup', 'subjects'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->paginate(12);
    
        $classGroups = ClassGroup::where('school_id', auth()->user()->school_id)->get();
        
        $students = null;
        if ($this->view === 'view') {
            $students = $this->getStudentsForClass($this->selectedClass->id);
        }
    
        $allClasses = MyClass::whereHas('classGroup', fn($q) => 
            $q->where('school_id', auth()->user()->school_id)
        )->get();
    
        $teachers = User::role('teacher')
            ->where('school_id', auth()->user()->school_id)
            ->get();
    
        return view('livewire.classes.manage-classes', [
            'classes' => $classes,
            'classGroups' => $classGroups,
            'students' => $students,
            'allClasses' => $allClasses,
            'teachers' => $teachers,
        ])->layout('layouts.new');
    }
}