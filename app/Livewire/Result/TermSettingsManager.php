<?php

namespace App\Livewire\Result;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{TermSettings, MyClass};

class TermSettingsManager extends Component
{
    public $academicYearId;
    public $semesterId;
    public $selectedClassId;
    public $generalAnnouncement;
    public $resumptionDate;
    public $isGlobal = true;
    
    public $classes;
    
    public $showSuccess = false;
    public $successMessage = '';

    public function mount()
    {
        $this->classes = MyClass::orderBy('name')->get();
        
        // Get from session (set by AcademicPeriodSelector)
        $this->academicYearId = session('result_academic_year_id');
        $this->semesterId = session('result_semester_id');
        
        $this->loadSettings();
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];
        $this->loadSettings();
    }

    public function updatedSelectedClassId()
    {
        $this->loadSettings();
    }

    public function updatedIsGlobal()
    {
        if ($this->isGlobal) {
            $this->selectedClassId = null;
        }
        $this->loadSettings();
    }

    protected function loadSettings()
    {
        if (!$this->academicYearId || !$this->semesterId) {
            return;
        }

        $classId = $this->isGlobal ? null : $this->selectedClassId;
        
        $settings = TermSettings::getForTermAndClass(
            $this->academicYearId, 
            $this->semesterId, 
            $classId
        );

        if ($settings) {
            $this->generalAnnouncement = $settings->general_announcement;
            $this->resumptionDate = $settings->resumption_date?->format('Y-m-d');
        } else {
            $this->generalAnnouncement = '';
            $this->resumptionDate = null;
        }
    }

    public function save()
    {
        $this->validate([
            'academicYearId' => 'required|exists:academic_years,id',
            'semesterId' => 'required|exists:semesters,id',
            'generalAnnouncement' => 'nullable|string|max:1000',
            'resumptionDate' => 'nullable|date',
            'selectedClassId' => $this->isGlobal ? 'nullable' : 'required|exists:my_classes,id',
        ]);

        $data = [
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
            'my_class_id' => $this->isGlobal ? null : $this->selectedClassId,
            'general_announcement' => $this->generalAnnouncement,
            'resumption_date' => $this->resumptionDate,
            'is_global' => $this->isGlobal,
        ];

        TermSettings::updateOrCreate(
            [
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
                'my_class_id' => $this->isGlobal ? null : $this->selectedClassId,
            ],
            $data
        );

        $this->showSuccess = true;
        $this->successMessage = $this->isGlobal 
            ? 'Global term settings saved successfully!' 
            : 'Class-specific settings saved successfully!';
        
        $this->dispatch('term-settings-updated');
    }

    public function render()
    {
        return view('livewire.result.term-settings-manager');
    }
}