<?php

namespace App\Livewire\Exams\Slots;

use App\Models\Exam;
use Livewire\Component;

class CreateExamSlotForm extends Component
{
    public Exam $exam;

    public function render()
    {
        return view('livewire.exams.slots.create-exam-slot-form');
    }
}
