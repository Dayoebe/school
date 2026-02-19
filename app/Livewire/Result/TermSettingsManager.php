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
        $this->classes = MyClass::whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->orderBy('name')
            ->get();
        
        // Get from session (set by AcademicPeriodSelector)
        $this->academicYearId = session('result_academic_year_id') ?? auth()->user()->school?->academic_year_id;
        $this->semesterId = session('result_semester_id') ?? auth()->user()->school?->semester_id;
        
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

        $yearValid = \App\Models\AcademicYear::where('id', $this->academicYearId)
            ->where('school_id', auth()->user()->school_id)
            ->exists();
        $semesterValid = \App\Models\Semester::where('id', $this->semesterId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('school_id', auth()->user()->school_id)
            ->exists();

        if (!$yearValid || !$semesterValid) {
            $this->generalAnnouncement = '';
            $this->resumptionDate = null;
            return;
        }

        $classId = $this->isGlobal ? null : $this->selectedClassId;

        if ($classId) {
            $classValid = MyClass::where('id', $classId)
                ->whereHas('classGroup', function ($query) {
                    $query->where('school_id', auth()->user()->school_id);
                })
                ->exists();

            if (!$classValid) {
                $this->generalAnnouncement = '';
                $this->resumptionDate = null;
                return;
            }
        }
        
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

        $yearValid = \App\Models\AcademicYear::where('id', $this->academicYearId)
            ->where('school_id', auth()->user()->school_id)
            ->exists();
        $semesterValid = \App\Models\Semester::where('id', $this->semesterId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('school_id', auth()->user()->school_id)
            ->exists();

        if (!$yearValid || !$semesterValid) {
            session()->flash('error', 'Academic year/term is not in your current school.');
            return;
        }

        if (!$this->isGlobal) {
            $classValid = MyClass::where('id', $this->selectedClassId)
                ->whereHas('classGroup', function ($query) {
                    $query->where('school_id', auth()->user()->school_id);
                })
                ->exists();

            if (!$classValid) {
                session()->flash('error', 'Selected class is not in your current school.');
                return;
            }
        }

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
