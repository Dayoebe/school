<?php

namespace App\Livewire\Result;

use Livewire\Component;
use App\Models\{AcademicYear, Semester};

class Index extends Component
{
    public $activeTab = 'dashboard';

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $currentYear = auth()->user()->school->academicYear ?? $academicYears->first();
        
        return view('livewire.result.index', [
            'academicYears' => $academicYears,
            'currentYear' => $currentYear,
        ])->layout('layouts.new', [
            'title' => 'Results Management',
            'page_heading' => 'Results Management System'
        ]);
    }
}