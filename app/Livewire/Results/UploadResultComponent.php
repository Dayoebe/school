<?php
namespace App\Livewire\Results;

use Livewire\Component;
use App\Models\Subject;
use App\Models\StudentRecord;


class UploadResultComponent extends Component
{
    public $selectedClass;
    public $selectedSection;
    public $selectedSubject;
    public $students;
    public $scores = [];

    // app/Livewire/Results/UploadResultComponent.php
public function render()
{
    return view('livewire.results.upload-result-component', [
        'subjects' => Subject::all(),
    ]);
}


    public function updatedSelectedSubject()
    {
        $this->students = StudentRecord::where('class_id', $this->selectedClass)
            ->where('section_id', $this->selectedSection)
            ->get();
    }

    public function save()
    {
        // save logic here
        session()->flash('message', 'Results uploaded successfully.');
    }
}


// namespace App\Http\Livewire\Results;

// use App\Models\Result;
// use App\Models\StudentRecord;
// use App\Models\Subject;
// use App\Models\Term;
// use App\Models\AcademicYear;
// use Livewire\Component;
// use Illuminate\Support\Facades\Auth;

// class UploadResultComponent extends Component
// {
//     public $selectedClass;
//     public $selectedSection;
//     public $selectedSubject;
//     public $academicYearId;
//     public $termId;
    
//     public $students = [];
//     public $scores = [];

//     public function mount()
//     {
//         $this->academicYearId = Auth::user()->school->academicYear->id ?? null;
//         $this->termId = session('current_term_id') ?? null; // You can set this dynamically or hardcode for now.
//     }

//     public function updatedSelectedSubject()
//     {
//         $this->loadStudents();
//     }

//     public function loadStudents()
//     {
//         if ($this->selectedClass && $this->selectedSection && $this->selectedSubject) {
//             $this->students = StudentRecord::where('my_class_id', $this->selectedClass)
//                 ->where('section_id', $this->selectedSection)
//                 ->with('user')
//                 ->get();
//         }
//     }

//     public function save()
//     {
//         foreach ($this->scores as $studentRecordId => $score) {
//             if (isset($score['test_score']) || isset($score['exam_score'])) {
//                 $test = (int) ($score['test_score'] ?? 0);
//                 $exam = (int) ($score['exam_score'] ?? 0);
//                 $total = $test + $exam;

//                 Result::updateOrCreate(
//                     [
//                         'student_record_id' => $studentRecordId,
//                         'subject_id' => $this->selectedSubject,
//                         'academic_year_id' => $this->academicYearId,
//                         'term_id' => $this->termId,
//                     ],
//                     [
//                         'test_score' => $test,
//                         'exam_score' => $exam,
//                         'total_score' => $total,
//                         'approved' => false, // Needs admin approval
//                     ]
//                 );
//             }
//         }

//         session()->flash('message', 'Results uploaded successfully and pending approval.');
//     }

//     public function render()
//     {
//         return view('livewire.results.upload-result-component', [
//             'subjects' => Subject::all(), // You might want to filter by class teacher
//         ]);
//     }
// } -->
