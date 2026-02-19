<?php

namespace App\Livewire\AcademicYears;

use App\Models\AcademicYear;
use App\Models\Semester;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShowAcademicYear extends Component
{
    use AuthorizesRequests;

    public AcademicYear $academicYear;
    public $semesters;
    
    // Academic Year Edit
    public $editingAcademicYear = false;
    public $startYear;
    public $stopYear;
    
    // Semester Edit/Create
    public $editingSemesterId = null;
    public $semesterName = '';
    public $showSemesterForm = false;

    protected $rules = [
        'startYear' => 'required|digits:4|integer|min:1900|max:2100',
        'stopYear' => 'required|digits:4|integer|min:1900|max:2100|gt:startYear',
        'semesterName' => 'required|string|max:255',
    ];

    protected $messages = [
        'stopYear.gt' => 'Stop year must be greater than start year.',
    ];

    public function mount($academicYear)
    {
        $schoolId = auth()->user()->school_id;

        // If it's an ID, load the model; if it's already a model, use it
        if (is_numeric($academicYear)) {
            $this->academicYear = AcademicYear::query()->findOrFail($academicYear);
        } else {
            $this->academicYear = $academicYear;
        }

        if ($this->academicYear->school_id !== $schoolId) {
            abort(403);
        }
        
        $this->authorize('view', $this->academicYear);
        
        $this->academicYear->load(['semesters', 'school']);
        $this->semesters = $this->academicYear->semesters;
        $this->startYear = $this->academicYear->start_year;
        $this->stopYear = $this->academicYear->stop_year;
    }

    // Academic Year Methods
    public function toggleEditAcademicYear()
    {
        $this->editingAcademicYear = !$this->editingAcademicYear;
        if (!$this->editingAcademicYear) {
            $this->startYear = $this->academicYear->start_year;
            $this->stopYear = $this->academicYear->stop_year;
            $this->resetValidation(['startYear', 'stopYear']);
        }
    }

    public function updateAcademicYear()
    {
        $this->authorize('update', $this->academicYear);

        $this->validate([
            'startYear' => 'required|digits:4|integer|min:1900|max:2100',
            'stopYear' => 'required|digits:4|integer|min:1900|max:2100|gt:startYear',
        ]);

        $this->academicYear->update([
            'start_year' => $this->startYear,
            'stop_year' => $this->stopYear,
        ]);
        $this->academicYear->refresh();

        $this->editingAcademicYear = false;
        session()->flash('success', 'Academic year updated successfully');
    }

    public function setAsCurrentAcademicYear()
    {
        $this->authorize('setAcademicYear', AcademicYear::class);

        $school = auth()->user()->school;
        $semesterId = $this->academicYear->semesters()->value('id');

        if (!$semesterId) {
            $semesterId = $this->academicYear->semesters()->create([
                'name' => 'First Term',
                'school_id' => $school->id,
            ])->id;
        }

        $school->update([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $semesterId,
        ]);

        session()->flash('success', 'Academic year set as current');
        $this->dispatch('$refresh');
    }

    // Semester Methods
    public function toggleSemesterForm()
    {
        $this->showSemesterForm = !$this->showSemesterForm;
        if (!$this->showSemesterForm) {
            $this->resetSemesterForm();
        }
    }

    public function createSemester()
    {
        $this->authorize('create', Semester::class);

        $this->validate(['semesterName' => 'required|string|max:255']);

        Semester::create([
            'name' => $this->semesterName,
            'academic_year_id' => $this->academicYear->id,
            'school_id' => $this->academicYear->school_id,
        ]);

        $this->academicYear->load('semesters');
        $this->semesters = $this->academicYear->semesters;
        $this->resetSemesterForm();
        session()->flash('success', 'Term created successfully');
    }

    public function editSemester($semesterId)
    {
        $semester = Semester::query()
            ->where('academic_year_id', $this->academicYear->id)
            ->find($semesterId);

        if (!$semester) {
            abort(404);
        }

        $this->authorize('update', $semester);
        
        $this->editingSemesterId = $semesterId;
        $this->semesterName = $semester->name;
        $this->showSemesterForm = true;
    }

    public function updateSemester()
    {
        $semester = Semester::query()
            ->where('academic_year_id', $this->academicYear->id)
            ->find($this->editingSemesterId);

        if (!$semester) {
            abort(404);
        }

        $this->authorize('update', $semester);

        $this->validate(['semesterName' => 'required|string|max:255']);

        $semester->update(['name' => $this->semesterName]);

        $this->academicYear->load('semesters');
        $this->semesters = $this->academicYear->semesters;
        $this->resetSemesterForm();
        session()->flash('success', 'Term updated successfully');
    }

    public function deleteSemester($semesterId)
    {
        $semester = Semester::query()
            ->where('academic_year_id', $this->academicYear->id)
            ->find($semesterId);

        if (!$semester) {
            abort(404);
        }

        $this->authorize('delete', $semester);

        if ($semester->id == auth()->user()->school->semester_id) {
            session()->flash('danger', 'Cannot delete the current term');
            return;
        }

        $semester->delete();

        $this->academicYear->load('semesters');
        $this->semesters = $this->academicYear->semesters;
        session()->flash('success', 'Term deleted successfully');
    }

    public function setSemesterAsCurrent($semesterId)
    {
        $this->authorize('setSemester', Semester::class);

        $semester = Semester::query()
            ->where('academic_year_id', $this->academicYear->id)
            ->findOrFail($semesterId);

        if ($semester->academic_year_id !== auth()->user()->school->academic_year_id) {
            session()->flash('danger', 'Semester not in current academic year');
            return;
        }

        auth()->user()->school->update([
            'semester_id' => $semester->id,
        ]);

        session()->flash('success', 'Term set as current');
        $this->dispatch('$refresh');
    }

    public function resetSemesterForm()
    {
        $this->semesterName = '';
        $this->editingSemesterId = null;
        $this->showSemesterForm = false;
        $this->resetValidation(['semesterName']);
    }

    public function render()
    {
        return view('livewire.academic-years.show-academic-year')
            ->layout('layouts.dashboard', [
                'title' => "View {$this->academicYear->name}",
                'page_heading' => "Academic Year Details: {$this->academicYear->name}",
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('academic-years.index'), 'text' => 'Academic years'],
                    ['href' => route('academic-years.show', $this->academicYear->id), 'text' => "View {$this->academicYear->name}", 'active' => true],
                ]
            ]);
    }
}
