<?php

namespace App\Livewire\Syllabi;

use Livewire\Component;

class ListSyllabiTable extends Component
{
    protected $queryString = ['class'];

    public $class;

    public $classes;

    public function mount()
    {
        $this->setErrorBag(session()->get('errors', new \Illuminate\Support\MessageBag())->getMessages());

        if (auth()->user()->hasRole('student')) {
            return $this->class = auth()->user()->studentRecord->myClass->id;
        }

        $this->classes = auth()->user()->school
            ->myClasses()
            ->with('sections')
            ->orderBy('name')
            ->get();
        if ($this->classes->isNotEmpty()) {
            $this->updatedClass();
        }
    }

    public function updatedClass()
    {
        if ($this->classes->find($this->class) == null) {
            $this->class = $this->classes?->first()->id;
        }

        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.syllabi.list-syllabi-table');
    }
}
