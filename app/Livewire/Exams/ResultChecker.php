<?php

namespace App\Livewire\Exams;

use App\Models\AcademicYear;
use App\Models\ExamRecord;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ResultChecker extends Component
{
    public $section;
    public $sections;
    public $classes;
    public $class;
    public $students;
    public $student;
    public $academicYears;
    public $academicYear;
    public $semesters;
    public $semester;
    public $exams;
    public $examRecords;
    public $subjects;
    public $preparedResults = false;
    public $status;
    public $studentName;
    public $selectedClass;

    public $rules = [
        'academicYear' => 'nullable|integer|exists:academic_years,id',
        'semester' => 'nullable|integer|exists:semesters,id',
        'class' => 'nullable|integer|exists:my_classes,id',
        'section' => 'nullable|integer|exists:sections,id',
        'student' => 'nullable|integer|exists:users,id',
    ];

    public function mount(): void
    {
        $school = auth()->user()->school;

        $this->academicYears = $school?->academicYears ?? collect();
        $this->semesters = collect();
        $this->classes = collect();
        $this->sections = collect();
        $this->students = collect();
        $this->exams = collect();
        $this->examRecords = collect();
        $this->subjects = collect();

        $this->academicYear = $school?->academicYear?->id;

        if ($this->academicYear) {
            $this->updatedAcademicYear();
        }

        if ($this->isStaffViewer()) {
            $this->classes = $school
                ->myClasses()
                ->with(['classGroup', 'sections', 'subjects'])
                ->orderBy('name')
                ->get();

            if ($this->classes->isNotEmpty()) {
                $this->class = $this->classes->first()->id;
                $this->updatedClass();
            }

            return;
        }

        if (auth()->user()->hasRole('student')) {
            $this->student = auth()->id();
            $this->checkResult();

            return;
        }

        if (auth()->user()->hasRole('parent')) {
            $this->students = auth()->user()
                ->children()
                ->where('users.school_id', auth()->user()->school_id)
                ->with(['studentRecord.myClass', 'studentRecord.section'])
                ->orderBy('users.name')
                ->get();

            $this->student = $this->students->first()?->id;
        }
    }

    public function updatedAcademicYear(): void
    {
        $this->semesters = collect();
        $this->semester = null;

        $academicYear = $this->resolveAcademicYear();

        if (!$academicYear) {
            return;
        }

        $this->semesters = $academicYear->semesters()->orderBy('id')->get();
        $preferredSemesterId = auth()->user()->school?->semester_id;

        $this->semester = $this->semesters->firstWhere('id', $preferredSemesterId)?->id
            ?? $this->semesters->first()?->id;

        if ($this->class) {
            $this->updatedClass();
        }
    }

    public function updatedClass(): void
    {
        $this->sections = collect();
        $this->students = collect();
        $this->section = null;
        $this->student = null;

        if (!$this->class) {
            return;
        }

        $class = MyClass::with('sections')->find($this->class);

        if (!$class) {
            return;
        }

        $this->sections = $class->sections;

        if ($this->sections->isEmpty()) {
            $this->students = $class->studentsForAcademicYear($this->academicYear);
            $this->student = $this->students->first()?->id;

            return;
        }

        $this->section = $this->sections->first()->id;
        $this->updatedSection();
    }

    public function updatedSection(): void
    {
        $this->students = collect();
        $this->student = null;

        if (!$this->section) {
            return;
        }

        $section = Section::find($this->section);

        if (!$section) {
            return;
        }

        $this->students = $section->studentsForAcademicYear($this->academicYear);
        $this->student = $this->students->first()?->id;
    }

    public function checkResult(): void
    {
        $this->resetResultState();

        $academicYear = $this->resolveAcademicYear();
        $student = $this->resolveStudent();
        $semester = $this->resolveSemester();

        if (!$academicYear) {
            $this->status = 'Select a valid academic year.';

            return;
        }

        if (!$student) {
            $this->status = 'Select a valid student.';

            return;
        }

        $this->studentName = $student->name;

        $examQuery = $semester
            ? $semester->exams()->where('publish_result', true)
            : $academicYear->exams()->where('publish_result', true);

        $this->exams = $examQuery->get()->load('examSlots');

        $examSlotIds = $this->exams
            ->flatMap(fn ($exam) => $exam->examSlots->pluck('id'))
            ->unique()
            ->values();

        $this->examRecords = $examSlotIds->isEmpty()
            ? collect()
            : ExamRecord::query()
                ->where('user_id', $student->id)
                ->whereIn('exam_slot_id', $examSlotIds)
                ->get();

        if ($this->exams->isEmpty()) {
            $this->status = 'There are no exams with published results for now.';

            return;
        }

        $studentRecord = $student->studentRecord;

        if (!$studentRecord) {
            $this->status = 'No records this academic year. Make sure the student has been promoted this year and is not graduated.';

            return;
        }

        $pivotRecord = DB::table('academic_year_student_record')
            ->where('student_record_id', $studentRecord->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        if (!$pivotRecord) {
            $this->status = 'No records this academic year. Make sure the student has been promoted this year and is not graduated.';

            return;
        }

        $this->selectedClass = MyClass::with(['subjects', 'classGroup'])->find($pivotRecord->my_class_id);

        if (!$this->selectedClass) {
            $this->status = 'Class record not found for this academic year.';

            return;
        }

        $this->subjects = $this->selectedClass->subjects;

        if ($this->subjects->isEmpty()) {
            $this->status = 'Subjects not present.';

            return;
        }

        $this->preparedResults = true;
        $this->status = null;
    }

    protected function resetResultState(): void
    {
        $this->preparedResults = false;
        $this->status = null;
        $this->studentName = null;
        $this->selectedClass = null;
        $this->exams = collect();
        $this->examRecords = collect();
        $this->subjects = collect();
    }

    protected function resolveAcademicYear(): ?AcademicYear
    {
        if (!$this->academicYear) {
            return null;
        }

        return $this->academicYears->firstWhere('id', (int) $this->academicYear)
            ?? AcademicYear::query()
                ->where('school_id', auth()->user()->school_id)
                ->find((int) $this->academicYear);
    }

    protected function resolveSemester()
    {
        if (!$this->semester) {
            return null;
        }

        return $this->semesters->firstWhere('id', (int) $this->semester)
            ?? $this->resolveAcademicYear()?->semesters()->find((int) $this->semester);
    }

    protected function resolveStudent(): ?User
    {
        if (!$this->student) {
            return null;
        }

        $studentId = (int) $this->student;
        $query = User::query()
            ->role('student')
            ->where('school_id', auth()->user()->school_id)
            ->with('studentRecord');

        if (auth()->user()->hasRole('student')) {
            return $studentId === (int) auth()->id() ? $query->find($studentId) : null;
        }

        if (auth()->user()->hasRole('parent')) {
            $isAccessible = auth()->user()
                ->children()
                ->where('users.id', $studentId)
                ->where('users.school_id', auth()->user()->school_id)
                ->exists();

            return $isAccessible ? $query->find($studentId) : null;
        }

        return $query->find($studentId);
    }

    protected function isStaffViewer(): bool
    {
        return auth()->user()->hasAnyRole(['super-admin', 'super_admin', 'principal', 'admin', 'teacher']);
    }

    public function render()
    {
        return view('livewire.exams.result-checker');
    }
}
