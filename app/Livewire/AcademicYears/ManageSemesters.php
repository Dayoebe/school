<?php

namespace App\Livewire\AcademicYears;

use App\Models\Semester;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ManageSemesters extends Component
{
    use AuthorizesRequests;

    public $semesters;
    public $selectedSemesterId;
    public $termName = '';
    public $editMode = false;
    public $editingId = null;
    public $showForm = false;

    protected $rules = [
        'termName' => 'required|string|max:255',
    ];

    public function mount()
    {
        $this->authorize('viewAny', Semester::class);
        $this->loadSemesters();
        $this->selectedSemesterId = auth()->user()->school->semester_id;
    }

    public function loadSemesters()
    {
        $academicYear = auth()->user()->school->academicYear;

        if ($academicYear) {
            $this->semesters = Semester::where('school_id', auth()->user()->school_id)
                ->where('academic_year_id', $academicYear->id)
                ->orderBy('id')
                ->get();
        } else {
            $this->semesters = collect();
        }
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
        $this->authorize('create', Semester::class);

        $this->validate();

        Semester::create([
            'name' => $this->termName,
            'school_id' => auth()->user()->school_id,
            'academic_year_id' => auth()->user()->school->academic_year_id,
        ]);

        $this->loadSemesters();
        $this->resetForm();
        session()->flash('success', 'Custom term created successfully');
    }

    public function edit($id)
    {
        $semester = Semester::find($id);
        $this->authorize('update', $semester);
        
        $term = $this->semesters->firstWhere('id', $id);
        $this->editingId = $id;
        $this->termName = $term->name;
        $this->editMode = true;
        $this->showForm = true;
    }

    public function update()
    {
        $semester = Semester::find($this->editingId);
        $this->authorize('update', $semester);

        $this->validate();

        $semester->update([
            'name' => $this->termName,
        ]);

        $this->loadSemesters();
        $this->resetForm();
        session()->flash('success', 'Term updated successfully');
    }

    public function delete($id)
    {
        $semester = Semester::find($id);
        $this->authorize('delete', $semester);

        // Prevent deleting current semester
        if ($semester->id == auth()->user()->school->semester_id) {
            session()->flash('danger', 'Cannot delete the current term. Please set a different term first.');
            return;
        }

        $semester->delete();

        $this->loadSemesters();
        session()->flash('success', 'Term deleted successfully');
    }

    public function setSemester()
    {
        $this->authorize('setSemester', Semester::class);
        
        $this->validate([
            'selectedSemesterId' => 'required|exists:semesters,id',
        ]);

        $semester = Semester::findOrFail($this->selectedSemesterId);

        if ($semester->academic_year_id !== auth()->user()->school->academic_year_id) {
            session()->flash('danger', 'Semester not in current academic year');
            return;
        }

        auth()->user()->school->update([
            'semester_id' => $semester->id,
        ]);

        auth()->user()->unsetRelation('school');
        $this->loadSemesters();
        session()->flash('success', 'Successfully set current term');
        $this->dispatch('$refresh');
    }

    public function resetForm()
    {
        $this->termName = '';
        $this->editMode = false;
        $this->editingId = null;
        $this->showForm = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.academic-years.manage-semesters')
            ->layout('layouts.new');
    }
}
