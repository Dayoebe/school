<?php

namespace App\Livewire\Parents;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class AssignStudentsToParent extends Component
{
    use WithPagination;

    public User $parent;
    public $search = '';
    public $selectedClass = '';
    public $selectedSection = '';
    public $classes = [];
    public $sections; // Will be a collection
    
    protected $queryString = ['search'];

    public function mount($parent)
    {
        $parentId = $parent instanceof User ? $parent->id : $parent;

        $this->parent = User::role('parent')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($parentId);
        
        // Verify parent role
        if (!$this->parent->hasRole('parent')) {
            abort(404);
        }
        
        // Check school access
        if ($this->parent->school_id !== auth()->user()->school_id) {
            abort(403);
        }
        
        // Load classes for filtering
        $this->classes = \App\Models\MyClass::whereHas('classGroup', function($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->orderBy('name')->get();
        
        // Initialize sections as empty collection
        $this->sections = collect();
    }

    public function updatedSelectedClass()
    {
        if ($this->selectedClass) {
            $classInSchool = \App\Models\MyClass::where('id', $this->selectedClass)
                ->whereHas('classGroup', function ($q) {
                    $q->where('school_id', auth()->user()->school_id);
                })
                ->exists();

            if (!$classInSchool) {
                $this->sections = collect();
                $this->selectedSection = '';
                $this->resetPage();
                return;
            }

            $this->sections = \App\Models\Section::where('my_class_id', $this->selectedClass)
                ->whereHas('myClass.classGroup', function ($q) {
                    $q->where('school_id', auth()->user()->school_id);
                })
                ->orderBy('name')->get();
        } else {
            $this->sections = collect(); // Empty collection
        }
        $this->selectedSection = '';
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function assignStudent($studentId)
    {
        $student = User::role('student')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($studentId);
        
        // Verify student role
        if (!$student->hasRole('student')) {
            session()->flash('error', 'Invalid student');
            return;
        }
        
        // Check if already assigned
        if ($this->parent->children()->where('student_id', $studentId)->exists()) {
            session()->flash('error', 'Student is already assigned to this parent');
            return;
        }
        
        // Assign student to parent
        DB::table('parent_records')->insert([
            'user_id' => $this->parent->id,
            'student_id' => $studentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        session()->flash('success', 'Student assigned successfully');
        $this->dispatch('student-assigned');
    }

    public function removeStudent($studentId)
    {
        DB::table('parent_records')
            ->where('user_id', $this->parent->id)
            ->where('student_id', $studentId)
            ->delete();
        
        session()->flash('success', 'Student removed successfully');
        $this->dispatch('student-removed');
    }

    public function render()
    {
        $currentAcademicYearId = auth()->user()->school->academic_year_id;
        
        // Get assigned students
        $assignedStudents = $this->parent->children()
            ->with(['studentRecord.myClass', 'studentRecord.section'])
            ->whereHas('studentRecord') // Only get students that have a student record
            ->get();
        
        // Get available students to assign
        $availableStudentsQuery = User::role('student')
            ->where('school_id', auth()->user()->school_id)
            ->whereHas('studentRecord') // Only get students with student records
            ->whereNotIn('id', $assignedStudents->pluck('id'))
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                          ->orWhereHas('studentRecord', function($q) {
                              $q->where('admission_number', 'like', '%' . $this->search . '%');
                          });
                });
            });
        
        // Apply class filter
        if ($this->selectedClass) {
            $availableStudentsQuery->whereHas('studentRecord', function($q) use ($currentAcademicYearId) {
                $q->whereHas('academicYears', function($query) use ($currentAcademicYearId) {
                    $query->where('academic_year_id', $currentAcademicYearId)
                          ->where('my_class_id', $this->selectedClass);
                });
            });
        }
        
        // Apply section filter
        if ($this->selectedSection) {
            $availableStudentsQuery->whereHas('studentRecord', function($q) use ($currentAcademicYearId) {
                $q->whereHas('academicYears', function($query) use ($currentAcademicYearId) {
                    $query->where('academic_year_id', $currentAcademicYearId)
                          ->where('section_id', $this->selectedSection);
                });
            });
        }
        
        $availableStudents = $availableStudentsQuery
            ->with(['studentRecord.myClass', 'studentRecord.section'])
            ->paginate(10);
        
        return view('livewire.parents.assign-students-to-parent', [
            'assignedStudents' => $assignedStudents,
            'availableStudents' => $availableStudents,
        ])
        ->layout('layouts.dashboard', [
            'breadcrumbs' => [
                ['href' => route('dashboard'), 'text' => 'Dashboard'],
                ['href' => route('parents.index'), 'text' => 'Parents'],
                ['href' => route('parents.show', $this->parent->id), 'text' => $this->parent->name],
                ['href' => route('parents.assign-student', $this->parent->id), 'text' => 'Assign Students', 'active' => true],
            ]
        ])
        ->title('Assign Students to ' . $this->parent->name);
    }
}
