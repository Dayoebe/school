<?php

namespace App\Livewire;

use App\Models\Subject;
use App\Models\User;
use Livewire\Component;

class EditSubjectForm extends Component
{
    public Subject $subject;
    public $name;
    public $short_name;
    public $search = '';
    public $selectedTeachers = [];
    
    protected $rules = [
        'name' => 'required|string|max:255',
        'short_name' => 'required|string|max:50',
        'selectedTeachers' => 'array',
        'selectedTeachers.*' => 'exists:users,id'
    ];

    public function mount()
    {
        $this->name = $this->subject->name;
        $this->short_name = $this->subject->short_name;
        $this->selectedTeachers = $this->subject->teachers()->pluck('users.id')->toArray();
    }

    public function getTeachersProperty()
    {
        return User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))
                 ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                 ->orderBy('name')
                 ->limit(10)
                 ->get(['id', 'name', 'email']);
    }

    public function removeTeacher($teacherId)
    {
        $this->selectedTeachers = array_filter($this->selectedTeachers, fn($id) => $id != $teacherId);
    }

    public function updateSubject()
    {
        $this->validate();
        
        $this->subject->update([
            'name' => $this->name,
            'short_name' => $this->short_name
        ]);
        
        $this->subject->teachers()->sync($this->selectedTeachers);
        
        session()->flash('success', 'Subject updated successfully');
        return redirect()->route('subjects.index');
    }

    public function render()
    {
        return view('livewire.edit-subject-form');
    }
}