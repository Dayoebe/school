<?php

namespace App\Livewire\Syllabi;

use App\Models\MyClass;
use Livewire\Component;

class CreateSyllabusForm extends Component
{
    public $class;

    public $classes;

    public $subject;

    public $subjects;

    public function mount()
    {
        $this->classes = auth()->user()->school
            ->myClasses()
            ->with('subjects')
            ->orderBy('name')
            ->get();
    }

    public function updatedClass()
    {
        $class = MyClass::with('subjects')->find($this->class);
        $this->subjects = $class ? collect($class->subjects) : collect();
    }

    public function loadInitialSubjects()
    {
        if (empty($this->classes) || $this->classes->isEmpty()) {
            $this->subjects = collect();
            return;
        }

        $this->class = $this->classes[0]['id'];
        $this->subjects = collect($this->classes[0]->subjects);
    }

    public function render()
    {
        return view('livewire.syllabi.create-syllabus-form');
    }
}
