<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MyClass;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ListStudentsTable extends Component
{
    use WithPagination;
    
    // Filter properties
    public $search = '';
    public $selectedClass = '';
    public $appliedClass = '';
    public $selectedStatus = '';
    public $appliedStatus = '';
    
    // Sorting properties
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // Pagination
    public $perPage = 10;
    
    // Data properties
    public $classes = [];
    
    public function mount()
    {
        $this->setErrorBag(session()->get('errors', new \Illuminate\Support\MessageBag())->getMessages());
        $this->classes = MyClass::orderBy('name')->get();
    }

    public function applyFilters()
    {
        $this->appliedClass = $this->selectedClass;
        $this->appliedStatus = $this->selectedStatus;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->selectedClass = '';
        $this->appliedClass = '';
        $this->selectedStatus = '';
        $this->appliedStatus = '';
        $this->resetPage();
    }

    public function sortBy($field)
    {
        // Handle relationship sorting
        $sortMap = [
            'email' => 'email',
            'admission_number' => 'student_records.admission_number',
            'class' => 'my_classes.name',
            'section' => 'sections.name'
        ];
        
        $actualField = $sortMap[$field] ?? $field;
        
        if ($this->sortField === $actualField) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $actualField;
    }

    public function deleteStudent($studentId)
    {
        $student = User::findOrFail($studentId);
        
        if (Gate::allows('delete student', $student)) {
            $student->delete();
            session()->flash('success', 'Student deleted successfully.');
            $this->resetPage();
        } else {
            session()->flash('error', 'You are not authorized to delete this student.');
        }
    }

    public function render()
    {
        $query = User::role('student')
            ->select('users.*')
            ->with([
                'studentRecord' => function ($query) {
                    $query->with(['myClass', 'section']);
                }
            ])
            ->join('student_records', 'student_records.user_id', '=', 'users.id')
            ->leftJoin('my_classes', 'student_records.my_class_id', '=', 'my_classes.id')
            ->leftJoin('sections', 'student_records.section_id', '=', 'sections.id')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('users.name', 'like', '%' . $this->search . '%')
                      ->orWhere('users.email', 'like', '%' . $this->search . '%')
                      ->orWhere('student_records.admission_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->appliedClass, function ($query) {
                $query->where('student_records.my_class_id', $this->appliedClass);
            })
            ->when($this->appliedStatus !== '', function ($query) {
                $query->where('users.locked', $this->appliedStatus);
            });
        
        $query->orderBy($this->sortField, $this->sortDirection);
        
        $students = $query->paginate($this->perPage);
        
        return view('livewire.list-students-table', [
            'classes' => $this->classes,
            'students' => $students,
        ]);
    }
}