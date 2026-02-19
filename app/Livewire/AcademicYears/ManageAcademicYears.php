<?php

namespace App\Livewire\AcademicYears;

use App\Models\AcademicYear;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ManageAcademicYears extends Component
{
    use AuthorizesRequests;

    public $academicYears;
    public $selectedAcademicYearId;
    public $startYear = '';
    public $stopYear = '';
    public $editMode = false;
    public $editingId = null;
    public $showForm = false;

    protected $rules = [
        'startYear' => 'required|digits:4|integer|min:1900|max:2100',
        'stopYear' => 'required|digits:4|integer|min:1900|max:2100|gt:startYear',
    ];

    protected $messages = [
        'stopYear.gt' => 'Stop year must be greater than start year.',
    ];

    public function mount()
    {
        $this->authorize('viewAny', AcademicYear::class);
        $this->loadAcademicYears();
        $this->selectedAcademicYearId = auth()->user()->school->academic_year_id;
    }

    public function loadAcademicYears()
    {
        $this->academicYears = AcademicYear::with('semesters')
            ->where('school_id', auth()->user()->school_id)
            ->orderByDesc('start_year')
            ->get();
    }

    public function toggleForm()
    {
        $this->showForm = !$this->showForm;
        if (!$this->showForm) {
            $this->resetForm();
        }
    }

    public function create()
    {
        $this->authorize('create', AcademicYear::class);

        $this->validate();

        $school = auth()->user()->school;

        $academicYear = AcademicYear::create([
            'start_year' => $this->startYear,
            'stop_year' => $this->stopYear,
            'school_id' => $school->id,
        ]);

        $academicYear->semesters()->createMany([
            ['name' => 'First Term', 'school_id' => $school->id],
            ['name' => 'Second Term', 'school_id' => $school->id],
            ['name' => 'Third Term', 'school_id' => $school->id],
        ]);

        $this->loadAcademicYears();
        $this->resetForm();
        session()->flash('success', 'Academic year created successfully with 3 terms');
    }

    public function edit($id)
    {
        $academicYear = $this->getAcademicYearForCurrentSchool($id);
        $this->authorize('update', $academicYear);

        $this->editingId = $id;
        $this->startYear = $academicYear->start_year;
        $this->stopYear = $academicYear->stop_year;
        $this->editMode = true;
        $this->showForm = true;
    }

    public function update()
    {
        $academicYear = $this->getAcademicYearForCurrentSchool($this->editingId);
        $this->authorize('update', $academicYear);

        $this->validate();

        $academicYear->update([
            'start_year' => $this->startYear,
            'stop_year' => $this->stopYear,
        ]);

        $this->loadAcademicYears();
        $this->resetForm();
        session()->flash('success', 'Academic year updated successfully');
    }

    public function delete($id)
    {
        $academicYear = $this->getAcademicYearForCurrentSchool($id);
        $this->authorize('delete', $academicYear);

        // Prevent deleting current academic year
        if ($academicYear->id == auth()->user()->school->academic_year_id) {
            session()->flash('danger', 'Cannot delete the current academic year. Please set a different academic year first.');
            return;
        }

        $academicYear->delete();

        $this->loadAcademicYears();
        session()->flash('success', 'Academic year deleted successfully');
    }

    public function setAcademicYear()
    {
        $this->authorize('setAcademicYear', AcademicYear::class);
        
        $this->validate([
            'selectedAcademicYearId' => 'required|exists:academic_years,id',
        ]);

        $school = auth()->user()->school;
        $academicYear = AcademicYear::query()->findOrFail($this->selectedAcademicYearId);

        $semesterId = $academicYear->semesters()->value('id');
        if (!$semesterId) {
            $semesterId = $academicYear->semesters()->create([
                'name' => 'First Term',
                'school_id' => $school->id,
            ])->id;
        }

        $school->update([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semesterId,
        ]);

        auth()->user()->unsetRelation('school');
        $this->loadAcademicYears();
        session()->flash('success', 'Academic year set for ' . auth()->user()->school->name . ' successfully');
        $this->dispatch('$refresh');
    }

    protected function getAcademicYearForCurrentSchool($id): AcademicYear
    {
        return AcademicYear::query()
            ->findOrFail($id);
    }

    public function resetForm()
    {
        $this->startYear = '';
        $this->stopYear = '';
        $this->editMode = false;
        $this->editingId = null;
        $this->showForm = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.academic-years.manage-academic-years')
            ->layout('layouts.dashboard');
    }
}
