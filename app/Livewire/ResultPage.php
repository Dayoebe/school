<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{
    StudentRecord, Subject, AcademicYear, Semester, Result, Section, User
};

class ResultPage extends Component
{
    use WithPagination;

    public $selectedClass;
    public $selectedSection;
    public $studentSearch = '';
    public $perPage = 10;
    public $showStudents = false;

    public $mode = 'index'; // index | upload | view
    public $currentStudentId;

    public $subjects = [];
    public $results = [];

    public $studentRecord;
    public $academicYearId;
    public $semesterId;

    protected $paginationTheme = 'tailwind';

    public function updated($property)
    {
        if (in_array($property, ['selectedClass', 'selectedSection', 'studentSearch'])) {
            $this->resetPage();
        }

        if ($property === 'studentSearch') {
            $this->showStudents = false;
        }
    }

    public function showFilteredStudents()
    {
        $this->showStudents = true;
    }

    public function getFilteredStudentsProperty()
    {
        if (!$this->selectedClass && !$this->selectedSection && !$this->studentSearch) {
            return collect();
        }

        $query = StudentRecord::query()
            ->with('user')
            ->where('is_graduated', false);

        if ($this->selectedClass) {
            $query->where('my_class_id', $this->selectedClass);
        }

        if ($this->selectedSection) {
            $query->whereHas('section', fn ($q) =>
                $q->where('name', $this->selectedSection)
            );
        }

        if ($this->studentSearch) {
            $query->whereHas('user', fn ($q) =>
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($this->studentSearch) . '%'])
            );
        }

        return $query
            ->join('users', 'student_records.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('student_records.*')
            ->paginate($this->perPage);
    }

    public function goToUpload($studentId)
    {
        $this->mode = 'upload';
        $this->currentStudentId = $studentId;

        $this->studentRecord = StudentRecord::where('student_id', $studentId)->firstOrFail();
        $this->subjects = Subject::where('class_id', $this->studentRecord->class_id)->get();
        $this->academicYearId = AcademicYear::latest()->first()?->id;
        $this->semesterId = Semester::latest()->first()?->id;

        foreach ($this->subjects as $subject) {
            $existing = Result::where([
                'student_record_id' => $this->studentRecord->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
            ])->first();

            $this->results[$subject->id] = [
                'test_score' => $existing?->test_score,
                'exam_score' => $existing?->exam_score,
                'comment' => $existing?->teacher_comment ?? '',
            ];
        }
    }

    public function goToView($studentId)
    {
        $this->mode = 'view';
        $this->currentStudentId = $studentId;

        // TODO: Load result view content here
    }

    public function goBack()
    {
        $this->mode = 'index';
        $this->currentStudentId = null;
        $this->results = [];
    }

    public function saveResults()
    {
        foreach ($this->results as $subjectId => $entry) {
            $total = ($entry['test_score'] ?? 0) + ($entry['exam_score'] ?? 0);

            Result::updateOrCreate([
                'student_record_id' => $this->studentRecord->id,
                'subject_id' => $subjectId,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
            ], [
                'test_score' => $entry['test_score'],
                'exam_score' => $entry['exam_score'],
                'total_score' => $total,
                'teacher_comment' => $entry['comment'],
                'approved' => false,
            ]);
        }

        $this->dispatch('notify', 'Results uploaded successfully.');
        $this->goBack();
    }
    public function render()
    {
        $view = match ($this->mode) {
            'upload' => 'pages.result.upload-content',
            'view' => 'pages.result.view-content',
            default => 'pages.result.index-content',
        };
    
        return view($view, [
            'filteredStudents' => $this->filteredStudents,
            'subjects' => Subject::all(),
            'sections' => Section::all()->unique('name'),
        ])->with([
            'breadcrumbs' => [
                ['href' => route('dashboard'), 'text' => 'Dashboard'],
                ['href' => route('result'), 'text' => 'Results', 'active' => true],
            ],
            'title' => 'Results',
            'page_heading' => 'Manage Student Results',
        ]);
    }
    
}
