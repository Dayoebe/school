<?php

namespace App\Livewire\GradeSystems;

use App\Models\ClassGroup;
use Livewire\Component;

class CreateGradeSystemForm extends Component
{
    public $classGroups;

    public function mount()
    {
        $this->classGroups = ClassGroup::query()
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.grade-systems.create-grade-system-form');
    }
}
