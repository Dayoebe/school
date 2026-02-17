<?php

namespace App\Livewire\Exams\Tabulation;

use App\Models\MyClass;
use App\Models\Section;
use App\Traits\MarkTabulationTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class SemesterResultTabulation extends Component
{
    use MarkTabulationTrait;

    public $section;

    public $sections;

    public $classes;

    public $class;

    public $semester;

    public $tabulatedRecords;

    public $createdTabulation;

    public $title;

    protected $listeners = ['print'];

    public function mount()
    {
        //get semester and use it to fetch all exams in semester
        $this->semester = auth()->user()->school->semester;
        $this->classes = auth()->user()->school
            ->myClasses()
            ->with(['classGroup', 'sections', 'subjects'])
            ->orderBy('name')
            ->get();

        //sets subjects etc if class isn't empty
        if (!$this->classes->isEmpty()) {
            $this->class = $this->classes[0]->id;
            $this->sections = $this->classes[0]->sections;
            $this->updatedClass();
        }
    }

    public function updatedClass()
    {
        //get instance of class
        $class = MyClass::with('sections')->find($this->class);
        if (!$class) {
            $this->sections = collect();
            $this->section = null;
            return;
        }

        //get sections in class
        $this->sections = $class->sections;

        //set section if the fetched records aren't empty
        $this->sections->count() ? $this->section = $this->sections[0]->id : $this->section = null;
    }

    public function tabulate(MyClass $myClass, $section)
    {
        $section = Section::find($section);

        if ($section == null) {
            $subjects = $myClass->subjects;

            //get all students in class
            $students = $myClass->studentsForAcademicYear(auth()->user()->school->academic_year_id);

            $classGroup = $myClass->classGroup;

            $titleFor = $myClass->name;
        } else {
            //get all subjects in section
            $subjects = $section->myClass->subjects;

            //get all students in section
            $students = $section->studentsForAcademicYear(auth()->user()->school->academic_year_id);

            $classGroup = $section->myClass->classGroup;

            $titleFor = $section->name;
        }

        if ($subjects->isEmpty()) {
            $this->createdTabulation = false;

            return;
        }

        $this->title = "Exam Marks For $titleFor in whole semester ".auth()->user()->school->semester->name.' in academic year '.auth()->user()->school->academicYear->name;

        if (!$this->semester) {
            $this->createdTabulation = false;
            return;
        }

        $examSlots = $this->semester->load('examSlots')->examSlots;

        $this->tabulatedRecords = $this->tabulateMarks($classGroup, $subjects, $students, $examSlots);

        $this->createdTabulation = true;
    }

    //print function

    public function print()
    {
        //used pdf class directly
        $pdf = Pdf::loadView('pages.exam.print-exam-tabulation', ['tabulatedRecords' => $this->tabulatedRecords, 'totalMarksAttainableInEachSubject' => $this->totalMarksAttainableInEachSubject, 'subjects' => $this->subjects])->output();

        //save as pdf
        return response()->streamDownload(
            fn () => print($pdf),
            'result-tabiulation.pdf'
        );
    }

    public function render()
    {
        return view('livewire.exams.tabulation.semester-result-tabulation');
    }
}
