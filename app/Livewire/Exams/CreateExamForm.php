<?php

namespace App\Livewire\Exams;

use App\Models\Semester;
use Livewire\Component;

class CreateExamForm extends Component
{
    public $semesters;

    public function mount()
    {
        $this->semesters = Semester::query()
            ->where('academic_year_id', auth()->user()->school->academic_year_id)
            ->orderBy('id')
            ->get();
    }

    public function render()
    {
        return view('livewire.exams.create-exam-form');
    }
}
