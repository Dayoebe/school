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
        $this->academicYears = AcademicYear::query()
            ->where('school_id', auth()->user()->school_id)
            ->orderBy('start_year', 'desc')
            ->get();

        $this->syncWithActiveSchoolPeriod();
        $this->savePeriod();
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
                ->where('school_id', auth()->user()->school_id)
                ->orderBy('name')
                ->get() 
            : collect();
    }

    protected function syncWithActiveSchoolPeriod(): void
    {
        $school = auth()->user()?->school?->fresh();

        $this->academicYearId = $school?->academic_year_id;

        if ($this->academicYearId && !$this->academicYears->contains('id', $this->academicYearId)) {
            $this->academicYearId = null;
        }

        $this->loadSemesters();

        $this->semesterId = null;

        if ($school?->semester_id && $this->semesters->contains('id', $school->semester_id)) {
            $this->semesterId = $school->semester_id;
        }
    }

    protected function savePeriod()
    {
        if (!$this->academicYearId || !$this->academicYears->contains('id', $this->academicYearId)) {
            $this->academicYearId = null;
            $this->semesters = collect();
            $this->semesterId = null;
        } else {
            $this->loadSemesters();

            if ($this->semesterId && !$this->semesters->contains('id', $this->semesterId)) {
                $this->semesterId = null;
            }
        }

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
