<?php

namespace App\Livewire\GradeSystems;

use App\Models\ClassGroup;
use Livewire\Component;

class ListGradeSystemsTable extends Component
{
    protected $queryString = ['classGroup'];

    public $classGroups;

    public $classGroup;

    protected $rules = [
        'classGroup' => 'integer',
    ];

    public function mount()
    {
        // Get all class groups
        $this->classGroups = ClassGroup::where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();

        if (auth()->user()->hasRole('student')) {
            $this->classGroup = auth()->user()->studentRecord->myClass->ClassGroup->id;
        }

        // Get all grades for first class group if class groups is not empty
        if ($this->classGroups != null && $this->classGroups->count() > 0) {
            //class groups are present
            $this->updatedClassGroup();
        }
    }

    public function updatedClassGroup()
    {
        if ($this->classGroups->find($this->classGroup) == null) {
            $this->classGroup = $this->classGroups?->first()->id;
        }

        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.grade-systems.list-grade-systems-table');
    }
}
