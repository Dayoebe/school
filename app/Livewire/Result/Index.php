<?php

namespace App\Livewire\Result;

use Livewire\Component;
use App\Models\AcademicYear;
use App\Traits\RestrictsTeacherResultViewing;

class Index extends Component
{
    use RestrictsTeacherResultViewing;

    public $activeTab = 'dashboard';

    public function setActiveTab($tab)
    {
        $allowedTabs = $this->availableResultTabs();

        $this->activeTab = in_array($tab, $allowedTabs, true)
            ? $tab
            : ($allowedTabs[0] ?? 'dashboard');
    }

    protected function availableViewTabs(): array
    {
        $tabs = [];

        if ($this->currentUserCanAccessClassOnlyResultTools()) {
            $tabs[] = 'class';
        }

        if ($this->currentUserCanAccessSubjectResultTools()) {
            $tabs[] = 'subject';
        }

        if ($this->currentUserCanAccessClassOnlyResultTools()) {
            $tabs[] = 'student';
        }

        return $tabs;
    }

    protected function availableResultTabs(): array
    {
        $tabs = ['dashboard', 'individual', 'bulk'];

        if ($this->availableViewTabs() !== []) {
            $tabs[] = 'view';
        }

        if ($this->currentUserCanManageTermResultSettings()) {
            $tabs[] = 'settings';
        }

        if ($this->currentUserCanAccessClassOnlyResultTools()) {
            $tabs[] = 'history';
            $tabs[] = 'spreadsheet';
            $tabs[] = 'awards';
            $tabs[] = 'analytics';
        }

        return $tabs;
    }

    public function render()
    {
        $academicYears = AcademicYear::query()
            ->orderBy('start_year', 'desc')
            ->get();
        $currentYear = auth()->user()->school?->academicYear ?? $academicYears->first();
        $availableViewTabs = $this->availableViewTabs();
        $availableTabs = $this->availableResultTabs();
        $canManageTermSettings = $this->currentUserCanManageTermResultSettings();
        $canAccessClassOnlyTools = $this->currentUserCanAccessClassOnlyResultTools();

        if (!in_array($this->activeTab, $availableTabs, true)) {
            $this->activeTab = $availableTabs[0] ?? 'dashboard';
        }
        
        return view('livewire.result.index', [
            'academicYears' => $academicYears,
            'currentYear' => $currentYear,
            'availableTabs' => $availableTabs,
            'availableViewTabs' => $availableViewTabs,
            'canManageTermSettings' => $canManageTermSettings,
            'canAccessClassOnlyTools' => $canAccessClassOnlyTools,
        ])->layout('layouts.result', [
            'title' => 'Results Management',
            'page_heading' => 'Results Management System'
        ]);
    }
}
