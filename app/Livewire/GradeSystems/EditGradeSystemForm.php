<?php

namespace App\Livewire\GradeSystems;

use App\Models\ClassGroup;
use App\Models\GradeSystem;
use Livewire\Component;

class EditGradeSystemForm extends Component
{
    public GradeSystem $grade;

    public $classGroups;

    public $classGroup;

    public function mount()
    {
        $this->classGroups = ClassGroup::query()
            ->orderBy('name')
            ->get();
        $this->classGroup = $this->grade->class_group_id;
    }

    public function render()
    {
        return view('livewire.grade-systems.edit-grade-system-form');
    }
}
