<?php

namespace App\Livewire\Result;

use Livewire\Component;
use App\Models\AcademicYear;

class Index extends Component
{
    public $activeTab = 'dashboard';

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $academicYears = AcademicYear::query()
            ->orderBy('start_year', 'desc')
            ->get();
        $currentYear = auth()->user()->school?->academicYear ?? $academicYears->first();
        
        return view('livewire.result.index', [
            'academicYears' => $academicYears,
            'currentYear' => $currentYear,
        ])->layout('layouts.result', [
            'title' => 'Results Management',
            'page_heading' => 'Results Management System'
        ]);
    }
}
