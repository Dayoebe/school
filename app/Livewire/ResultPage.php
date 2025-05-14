<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Subject;
use App\Models\Section;
use App\Models\User;
use App\Models\StudentRecord;

class ResultPage extends Component
{
    use WithPagination;

    public $selectedClass;
    public $selectedSection;
    public $studentSearch = '';
    public $selectedStudentId;
    public $showStudents = false;
    public $perPage = 10;
    public $page = 'index'; // index | upload | view

    protected $paginationTheme = 'tailwind';

    // Reset and show on selection
    public function updatedSelectedClass()
    {
        $this->resetPage();
        $this->showStudents = true;
    }

    public function updatedSelectedSection()
    {
        $this->resetPage();
        $this->showStudents = true;
    }

    public function updatedStudentSearch()
    {
        $this->resetPage();
        $this->showStudents = false; // Only show search dropdown, not table
    }

    public function showFilteredStudents()
    {
        $this->showStudents = true;
    }

    public function getFilteredStudentsProperty()
    {
        if (!$this->selectedClass && !$this->selectedSection && !$this->studentSearch) {
            return collect(); // Empty by default
        }

        $query = StudentRecord::query()->with('user')->where('is_graduated', false);

        if ($this->selectedClass) {
            $query->where('my_class_id', $this->selectedClass);
        }

        if ($this->selectedSection) {
            $query->whereHas('section', fn($q) =>
                $q->where('name', $this->selectedSection)
            );
        }

        if ($this->studentSearch) {
            $query->whereHas('user', fn($q) =>
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($this->studentSearch) . '%'])
            );

            return $query
                ->orderBy(User::select('name')->whereColumn('users.id', 'student_records.user_id'))
                ->get();
        }

        return $query
            ->join('users', 'student_records.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('student_records.*')
            ->paginate($this->perPage);
    }

    public function goToUpload($studentId)
    {
        $this->selectedStudentId = $studentId;
        $this->page = 'upload';
    }

    public function goToView($studentId)
    {
        $this->selectedStudentId = $studentId;
        $this->page = 'view';
    }

    public function goBack()
    {
        $this->page = 'index';
        $this->selectedStudentId = null;
    }

    public function save()
    {
        session()->flash('message', 'Results uploaded successfully.');
    }

    public function render()
    {
        return view('livewire.result-page', [
            'subjects' => Subject::all(),
            'sections' => Section::all()->unique('name'),
            'filteredStudents' => $this->filteredStudents,
        ]);
    }
}
