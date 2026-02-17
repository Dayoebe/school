<?php

namespace App\Livewire\Exams\Slots;

use App\Models\Exam;
use Livewire\Component;

class ListExamSlotsTable extends Component
{
    public Exam $exam;

    public function render()
    {
        return view('livewire.exams.slots.list-exam-slots-table');
    }
}
