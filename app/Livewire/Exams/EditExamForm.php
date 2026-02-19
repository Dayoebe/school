<?php

namespace App\Livewire\Exams;

use App\Models\Exam;
use App\Models\Semester;
use Livewire\Component;

class EditExamForm extends Component
{
    public Exam $exam;

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
        return view('livewire.exams.edit-exam-form');
    }
}
