<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Section;
use App\Models\Subject;

class ShowSection extends Component
{
    public Section $section;
    public $students;
    public $availableSubjects; // Add this property

    public function mount(Section $section)
    {
        $this->section = $section->load('studentRecords', 'studentRecords.user');
        $this->students = $this->section->students();
        
        // Calculate available subjects
        $this->availableSubjects = Subject::where('my_class_id', $this->section->my_class_id)
            ->whereDoesntHave('sections', function ($query) {
                $query->where('sections.id', $this->section->id);
            })
            ->get();
    }

    public function render()
    {
        return view('livewire.show-section');
    }
}