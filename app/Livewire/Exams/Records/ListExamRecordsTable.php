<?php

namespace App\Livewire\Exams\Records;

use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\MyClass;
use App\Models\Section;
use App\Traits\RestrictsTeacherExamPaperManagement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ListExamRecordsTable extends Component
{
    use WithPagination;
    use RestrictsTeacherExamPaperManagement;

    protected $queryString = ['sectionSelectedId', 'examSelectedId', 'subjectSelectedId', 'search'];

    public $semester;
    public $search = null;
    public Collection $exams;
    public $examSlots;
    public $exam;
    public Collection $classes;
    public $class;
    public Collection $subjects;
    public $subject;
    public Collection $sections;
    public $section;
    public $examRecords;
    public $classSelected;
    public $subjectSelected;
    public $sectionSelected;
    public $examSelected;
    public $error;
    public $sectionSelectedId;
    public $examSelectedId;
    public $subjectSelectedId;

    public function mount(): void
    {
        $this->semester = auth()->user()->school->semester;
        $this->exams = $this->semester
            ? Exam::query()
                ->where('semester_id', $this->semester->id)
                ->orderBy('start_date')
                ->orderBy('name')
                ->get()
            : collect();

        $this->classes = $this->accessibleExamPaperClassesQuery()
            ->with(['sections', 'classGroup'])
            ->orderBy('name')
            ->get();

        $this->subjects = collect();
        $this->sections = collect();
        $this->examRecords = collect();
        $this->examSlots = null;
        $this->error = null;

        $this->exam = $this->resolveInitialExamId();
        $this->class = $this->resolveInitialClassId();

        if ($this->classes->isEmpty() && $this->isRestrictedTeacherExamPaperManager()) {
            $this->error = 'No class is currently assigned to you for exam management.';
        }

        if ($this->class) {
            $this->updatedClass();
        }

        if ($this->subjectSelectedId) {
            $this->subject = $this->subjects->firstWhere('id', (int) $this->subjectSelectedId)?->id;
        }

        if ($this->sectionSelectedId) {
            $this->section = $this->sections->firstWhere('id', (int) $this->sectionSelectedId)?->id;
        }

        if ($this->exam && $this->subject && $this->section) {
            $this->fetchExamRecords();
        }
    }

    public function updatedClass(): void
    {
        $this->resetSelectionState();

        if (!$this->class) {
            return;
        }

        $class = MyClass::with('sections')->find($this->class);

        if (!$class) {
            $this->error = 'Selected class could not be found.';

            return;
        }

        if (!$this->currentUserCanManageExamPaperClass((int) $class->id)) {
            $this->error = 'You cannot manage exam records for the selected class.';

            return;
        }

        $this->subjects = $this->accessibleExamPaperSubjectsQuery((int) $class->id)
            ->get(['subjects.id', 'subjects.name']);
        $this->sections = $class->sections->sortBy('name')->values();
        $this->subject = $this->subjects->firstWhere('id', (int) $this->subjectSelectedId)?->id
            ?? $this->subjects->first()?->id;
        $this->section = $this->sections->firstWhere('id', (int) $this->sectionSelectedId)?->id
            ?? $this->sections->first()?->id;

        if ($this->subjects->isEmpty()) {
            $this->error = 'No subjects found for the selected class.';

            return;
        }

        if ($this->sections->isEmpty()) {
            $this->error = 'No sections found for the selected class. Add a section before uploading exam records.';
        }
    }

    public function fetchExamRecords(): void
    {
        $this->resetPage();
        $this->error = null;
        $this->search = null;
        $this->examSlots = null;
        $this->examRecords = collect();
        $this->classSelected = null;
        $this->subjectSelected = null;
        $this->sectionSelected = null;
        $this->examSelected = null;

        $class = $this->class ? MyClass::with('sections')->find($this->class) : null;
        $exam = $this->exam ? Exam::find($this->exam) : null;
        $subject = $this->subject
            ? $this->subjects->firstWhere('id', (int) $this->subject)
            : null;
        $section = $this->section ? Section::find($this->section) : null;

        if (!$class) {
            $this->error = 'Select a valid class before viewing records.';

            return;
        }

        if (!$exam) {
            $this->error = 'Select an exam before viewing records.';

            return;
        }

        if (!$subject) {
            $this->error = 'Select a subject before viewing records.';

            return;
        }

        if (!$this->currentUserCanManageExamPaperClass((int) $class->id)) {
            $this->error = 'You cannot manage exam records for the selected class.';

            return;
        }

        if (!$this->currentUserCanManageExamPaperSubject((int) $subject->id, (int) $class->id)) {
            $this->error = 'You cannot manage exam records for the selected subject.';

            return;
        }

        if (!$section) {
            $this->error = $class->sections->isEmpty()
                ? 'Selected class has no sections. Add a section before uploading exam records.'
                : 'Select a section before viewing records.';

            return;
        }

        if ((int) $section->my_class_id !== (int) $class->id) {
            $this->error = 'Selected section does not belong to the selected class.';

            return;
        }

        $this->examSlots = $exam->examSlots;

        if ($this->examSlots->isEmpty()) {
            $this->error = 'No exam slots found for the selected exam.';
            $this->examSlots = null;

            return;
        }

        $this->examRecords = ExamRecord::query()
            ->inSubject($subject->id)
            ->inSection($section->id)
            ->whereIn('exam_slot_id', $this->examSlots->pluck('id'))
            ->get();

        $this->examSelected = $exam;
        $this->examSelectedId = $exam->id;
        $this->sectionSelected = $section;
        $this->sectionSelectedId = $section->id;
        $this->classSelected = $class;
        $this->subjectSelected = $subject;
        $this->subjectSelectedId = $subject->id;
    }

    protected function resolveInitialExamId(): ?int
    {
        if ($this->examSelectedId) {
            return $this->exams->firstWhere('id', (int) $this->examSelectedId)?->id;
        }

        return $this->exams->first()?->id;
    }

    protected function resolveInitialClassId(): ?int
    {
        if ($this->sectionSelectedId) {
            return Section::query()->find((int) $this->sectionSelectedId)?->my_class_id;
        }

        return $this->classes->first(function (MyClass $class) {
            return $class->sections->isNotEmpty()
                && $this->accessibleExamPaperSubjectsQuery((int) $class->id)->exists();
        })?->id
            ?? $this->classes->first(fn (MyClass $class) => $class->sections->isNotEmpty())?->id
            ?? $this->classes->first()?->id;
    }

    protected function resetSelectionState(): void
    {
        $this->subjects = collect();
        $this->sections = collect();
        $this->subject = null;
        $this->section = null;
        $this->examSlots = null;
        $this->examRecords = collect();
        $this->error = null;
        $this->classSelected = null;
        $this->subjectSelected = null;
        $this->sectionSelected = null;
        $this->examSelected = null;
    }

    protected function studentsPaginator(): ?LengthAwarePaginator
    {
        if (!$this->sectionSelected || !$this->sectionSelected->exists()) {
            return null;
        }

        $students = $this->sectionSelected
            ->studentsForAcademicYear(auth()->user()->school->academic_year_id)
            ->sortBy('name')
            ->values();

        if ($this->search) {
            $search = mb_strtolower(trim((string) $this->search));
            $students = $students
                ->filter(fn ($student) => str_contains(mb_strtolower((string) $student->name), $search))
                ->values();
        }

        $page = Paginator::resolveCurrentPage($this->getPageName());
        $perPage = 10;
        $items = $students->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $students->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $this->getPageName(),
            ]
        );
    }

    public function paginationView(): string
    {
        return 'components.datatable-pagination-links-view';
    }

    public function render()
    {
        $students = $this->studentsPaginator();

        return view('livewire.exams.records.list-exam-records-table', [
            'students' => $students,
        ]);
    }
}
