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

    public $preparedResults;

    public $status;

    public $studentName;

    public $selectedClass;

    //rules
    public $rules = [
        'academicYear' => 'integer|exists:academic_years,id',
        'semester'     => 'required|integer|exists:semesters_id',
    ];

    public function mount()
    {
        $school = auth()->user()->school;
        $this->academicYears = $school->academicYears;
        $this->academicYear = $school->academicYear?->id;

        if ($this->academicYear) {
            $this->updatedAcademicYear();
        }

        if (auth()->user()->hasAnyRole(['super-admin', 'admin', 'teacher'])) {
            $this->classes = $school
                ->myClasses()
                ->with(['classGroup', 'sections', 'subjects'])
                ->orderBy('name')
                ->get();

            if ($this->classes->isEmpty()) {
                return;
            }
            $this->class = $this->classes[0]->id;
            $this->updatedClass();
        } elseif (auth()->user()->hasRole('student')) {
            if ($school->academicYear) {
                $this->checkResult($school->academicYear, $school->semester, auth()->user());
            }
        } elseif (auth()->user()->hasRole('parent')) {
            //get parent's children
            $this->students = auth()->user()->parentRecord->Students;
            //set student if the fetched records aren't empty
            $this->students->count() ? $this->student = $this->students[0]->id : $this->student = null;
        }
    }

    //updated academic year
    public function updatedAcademicYear()
    {
        $academicYear = AcademicYear::find($this->academicYear);
        if (!$academicYear) {
            $this->semesters = collect();
            $this->semester = null;
            return;
        }
        //get semesters in academic year
        $this->semesters = $academicYear->semesters;
        $this->semester = null;

        if ($this->semesters->isEmpty()) {
            return;
        }

        $this->semester = ($this->semesters->find(auth()->user()->school->semester_id) ?? $this->semesters[0])->id;
    }

    public function updatedClass()
    {
        //get instance of class
        $class = MyClass::with('sections')->find($this->class);
        if (!$class) {
            $this->sections = collect();
            $this->students = null;
            $this->section = null;
            return;
        }

        //get sections in class
        $this->sections = $class->sections;

        //set section if the fetched records aren't empty
        if ($this->sections->isEmpty()) {
            $this->students = null;

            return;
        }
        $this->section = $this->sections[0]->id;

        $this->updatedSection();
    }

    public function updatedSection()
    {
        //get instance of section
        $section = Section::find($this->section);
        if (!$section) {
            $this->students = collect();
            $this->student = null;
            return;
        }

        //get students in section
        $this->students = $section->studentsForAcademicYear($this->academicYear);

        //set student if the fetched records aren't empty
        $this->students->count() ? $this->student = $this->students[0]->id : $this->student = null;
    }

    public function checkResult(AcademicYear $academicYear, $semester, User $student)
    {
        $semester = $this->semesters->find($semester);

        // make sure user student isn't another role
        if (!$student->hasRole('student')) {
            abort(404, 'Student not found.');
        }
        //set name that would be used in view
        $this->studentName = $student->name;
        // fetch all exams, subjects and exam records for user in semester

        if ($semester != null && $semester->exists()) {
            $this->exams = $semester->exams()->where('publish_result', true)->get()->load('examSlots');
            //fetch all students exam records in semester
            $examSlotIds = $this->exams
                ->flatMap(fn($exam) => $exam->examSlots->pluck('id'))
                ->unique()
                ->values();
            $this->examRecords = $examSlotIds->isEmpty()
                ? collect()
                : ExamRecord::where('user_id', $student->id)->whereIn('exam_slot_id', $examSlotIds)->get();
        } else {
            $this->exams = $academicYear->exams()->where('publish_result', true)->get()->load('examSlots');
            $examSlotIds = $this->exams
                ->flatMap(fn($exam) => $exam->examSlots->pluck('id'))
                ->unique()
                ->values();
            $this->examRecords = $examSlotIds->isEmpty()
                ? collect()
                : ExamRecord::where('user_id', $student->id)->whereIn('exam_slot_id', $examSlotIds)->get();
        }

        if ($this->exams->isEmpty()) {
            $this->status = 'There are no exams with published results for now';

            return $this->preparedResults = false;
        }

        $studentRecord = $student->studentRecord;
        if (!$studentRecord) {
            $this->status = 'No records this academic year, make sure user has been promoted this year or has not been graduated';
            $this->preparedResults = false;

            return;
        }

        $pivotRecord = DB::table('academic_year_student_record')
            ->where('student_record_id', $studentRecord->id)
            ->where('academic_year_id', $this->academicYear)
            ->first();

        if (!$pivotRecord) {
            $this->status = 'No records this academic year, make sure user has been promoted this year or has not been graduated';
            $this->preparedResults = false;

            return;
        }

        $this->selectedClass = MyClass::with(['subjects', 'classGroup'])->find($pivotRecord->my_class_id);
        if (!$this->selectedClass) {
            $this->status = 'Class record not found for this academic year';
            $this->preparedResults = false;

            return;
        }

        $this->subjects = $this->selectedClass->subjects;

        if ($this->subjects->isEmpty()) {
            $this->status = 'Subjects not present';
            $this->preparedResults = false;

            return;
        }

        $this->preparedResults = true;
    }

    public function render()
    {
        return view('livewire.exams.result-checker');
    }
}
