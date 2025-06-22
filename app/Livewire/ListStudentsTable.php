<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MyClass;
use App\Models\User;
use Livewire\WithPagination; 

class ListStudentsTable extends Component
{
    use WithPagination; 
    
    public $selectedClass = '';
    public $appliedClass = '';
    public $filterKey = 0;
    public $classes = [];
    public $perPage = 10; 
    
    public function mount()
    {
        $this->setErrorBag(session()->get('errors', new \Illuminate\Support\MessageBag())->getMessages());
        $this->classes = MyClass::orderBy('name')->get();
    }

    public function loadStudents()
    {
        $this->resetPage(); 
        $this->filterKey = rand();
    }

    public function applyFilter()
    {
        $this->appliedClass = $this->selectedClass;
        $this->loadStudents();
    }

    public function clearFilter()
    {
        $this->selectedClass = '';
        $this->appliedClass = '';
        $this->loadStudents();
    }

    public function render()
    {
        $query = User::role('student')
            ->with('studentRecord', 'studentRecord.myClass', 'studentRecord.section')
            ->orderBy('name');
            
        if ($this->appliedClass) {
            $query->whereHas('studentRecord', function ($q) {
                $q->where('my_class_id', $this->appliedClass);
            });
        }
        
        
        $students = $query->paginate($this->perPage);
        
        return view('livewire.list-students-table', [
            'classes' => $this->classes,
            'students' => $students, 
        ]);
    }
}