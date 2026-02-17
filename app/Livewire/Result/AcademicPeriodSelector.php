<?php

namespace App\Livewire\Result;

use Livewire\Component;
use App\Models\AcademicYear;
use App\Models\Semester;

class AcademicPeriodSelector extends Component
{
    public $academicYearId;
    public $semesterId;
    public $academicYears;
    public $semesters;

    public function mount()
    {
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        
        // Set defaults: first check session, then fall back to school's current settings
        $this->academicYearId = session('result_academic_year_id') 
            ?? auth()->user()->school->academic_year_id
            ?? $this->academicYears->first()?->id;
        
        $this->loadSemesters();
        
        // For semester, check session first, then get current semester
        $this->semesterId = session('result_semester_id');
        
        if (!$this->semesterId && $this->academicYearId) {
            // If no session value, try to get the "current" semester (you might have a flag for this)
            // or just get the first semester
            $this->semesterId = $this->semesters->first()?->id;
        }
        
        // Save the defaults to session if not already set
        if ($this->academicYearId && $this->semesterId) {
            $this->savePeriod();
        }
    }

    public function updatedAcademicYearId()
    {
        $this->loadSemesters();
        $this->semesterId = $this->semesters->first()?->id;
        $this->savePeriod();
    }

    public function updatedSemesterId()
    {
        $this->savePeriod();
    }

    protected function loadSemesters()
    {
        $this->semesters = $this->academicYearId 
            ? Semester::where('academic_year_id', $this->academicYearId)
                ->orderBy('name')
                ->get() 
            : collect();
    }

    protected function savePeriod()
    {
        session([
            'result_academic_year_id' => $this->academicYearId,
            'result_semester_id' => $this->semesterId,
        ]);

        $this->dispatch('academic-period-changed', [
            'academicYearId' => $this->academicYearId,
            'semesterId' => $this->semesterId,
        ]);
    }

    public function render()
    {
        return view('livewire.result.academic-period-selector');
    }
}