<?php

namespace App\Livewire\Exams\Tabulation;

use Livewire\Component;

class MarkTabulation extends Component
{
    public $tabulatedRecords;

    public $totalMarksAttainableInEachSubject;

    public $subjects;

    public $title = '';

    public function render()
    {
        return view('livewire.exams.tabulation.mark-tabulation');
    }
}
