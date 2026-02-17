<?php

namespace App\Livewire\Syllabi;

use App\Models\Syllabus;
use Livewire\Component;

class ShowSyllabus extends Component
{
    public Syllabus $syllabus;

    public function render()
    {
        return view('livewire.syllabi.show-syllabus');
    }
}
