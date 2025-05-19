<?php

namespace App\Livewire;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{
    StudentRecord,
    Subject,
    AcademicYear,
    Semester,
    Result,
    Section,
    User
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
    public $positions = [];

    protected $paginationTheme = 'tailwind';

    public function rules()
    {
        $rules = [];

        foreach ($this->subjects as $subject) {
            $id = $subject->id;
            $rules["results.$id.test_score"] = 'nullable|numeric|min:0|max:40';
            $rules["results.$id.exam_score"] = 'nullable|numeric|min:0|max:60';
            $rules["results.$id.comment"] = 'nullable|string|max:255';
        }

        return $rules;
    }

    public function mount($studentId = null)
    {
        $this->setDefaultAcademicYearAndSemester();
        if ($studentId) {
            $this->studentRecord = StudentRecord::findOrFail($studentId);

            $this->results = $this->initializeResults();

            $this->studentRecord = StudentRecord::findOrFail($studentId);
            $this->subjects = Subject::where('my_class_id', $this->studentRecord->my_class_id)->get();
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
                    'test_score' => $existing?->test_score ?? '',
                    'exam_score' => $existing?->exam_score ?? '',
                    'comment' => $existing?->teacher_comment ?? '',
                ];
            }
        }
    }
    public function updated($property)
    {
        if (in_array($property, ['selectedClass', 'selectedSection', 'studentSearch'])) {
            $this->resetPage();
        }

        if ($property === 'studentSearch') {
            $this->showStudents = false;
        }
    }
    public function setDefaultAcademicYearAndSemester()
    {
        $this->academicYearId = AcademicYear::orderByDesc('start_year')->first()?->id;
        $this->semesterId = Semester::where('academic_year_id', $this->academicYearId)->first()?->id;
    }
    public function goToAcademicOverview()
    {
        $year = AcademicYear::find($this->academicYearId)?->name ?? 'Unknown Year';
        $semester = Semester::find($this->semesterId)?->name ?? 'Unknown Term';

        $message = "You selected:<br>
Academic Year: <strong>$year</strong><br>
Semester: <strong>$semester</strong>";

        $this->dispatch('show-overview-alert', message: $message);
    }


    public function updatedAcademicYearId()
    {
        $this->semesterId = Semester::where('academic_year_id', $this->academicYearId)->first()?->id;
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
            $query->where('section_id', $this->selectedSection);
        }
        
        if ($this->studentSearch) {
            $query->whereHas(
                'user',
                fn($q) =>
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

        $this->studentRecord = StudentRecord::findOrFail($studentId);


        $this->subjects = Subject::where('my_class_id', $this->studentRecord->my_class_id)->get();

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
        $this->currentStudentId = $studentId;

        $this->studentRecord = StudentRecord::findOrFail($studentId);

        $this->subjects = Subject::where('my_class_id', $this->studentRecord->my_class_id)->get();

        foreach ($this->subjects as $subject) {
            $existing = Result::where([
                'student_record_id' => $this->studentRecord->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
            ])->first();

            $test = (int) ($existing?->test_score ?? 0);
            $exam = (int) ($existing?->exam_score ?? 0);

            $this->results[$subject->id] = [
                'test_score' => $test,
                'exam_score' => $exam,
                'total_score' => $test + $exam,
                'comment' => $existing?->teacher_comment ?? '',
            ];
        }

        $this->mode = 'view';
    }

    public function goBack()
    {
        $this->mode = 'index';
        $this->currentStudentId = null;
        $this->results = [];
    }
    public function updatedResults($value, $key)
    {
        [$subjectId, $field] = explode('.', $key);

        // Force integer values
        $this->results[$subjectId][$field] = (int) $value;


        try {
            $this->validateOnly("results.$key");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return; // Don't proceed if invalid
        }

        [$subjectId, $field] = explode('.', $key);
        $subjectId = (int) $subjectId;

        $entry = $this->results[$subjectId];
        $test = (int) ($entry['test_score'] ?? 0);
        $exam = (int) ($entry['exam_score'] ?? 0);
        $total = (int) $test + (int) $exam;
        $grade = $this->calculateGrade($total);

        $grade = match (true) {
            $total >= 70 => 'A',
            $total >= 60 => 'B',
            $total >= 50 => 'C',
            $total >= 45 => 'D',
            $total >= 40 => 'E',
            default => 'F',
        };

        $comment = match ($grade) {
            'A' => 'Excellent performance.',
            'B' => 'Very good work.',
            'C' => 'Good effort, keep pushing.',
            'D' => 'Fair. Needs improvement.',
            'E' => 'Weak. Serious improvement needed.',
            'F' => 'Poor. Immediate attention required.',
            default => '',
        };

        $this->results[$subjectId]['grade'] = $grade;
        $this->results[$subjectId]['comment'] = $comment;

        Result::updateOrCreate([
            'student_record_id' => $this->studentRecord->id,
            'subject_id' => $subjectId,
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
        ], [
            'test_score' => $test,
            'exam_score' => $exam,
            'total_score' => $total,
            'teacher_comment' => $entry['comment'] ?? '',
            'approved' => false,
        ]);
    }
    public function saveResults()
    {
        $this->validate([
            'results.*.test_score' => 'required|numeric|min:0|max:40',
            'results.*.exam_score' => 'required|numeric|min:0|max:60',
            'results.*.comment' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () {
                foreach ($this->results as $subjectId => $data) {
                    $test = (float) ($data['test_score'] ?? 0);
                    $exam = (float) ($data['exam_score'] ?? 0);
                    $total = $test + $exam;

                    Result::updateOrCreate(
                        [
                            'student_record_id' => $this->studentRecord->id,
                            'subject_id' => $subjectId,
                            'academic_year_id' => $this->academicYearId,
                            'semester_id' => $this->semesterId,
                        ],
                        [
                            'test_score' => $test,
                            'exam_score' => $exam,
                            'total_score' => $total,
                            'teacher_comment' => $data['comment'] ?? null,
                            'approved' => false,
                        ]
                    );
                }
            });

            session()->flash('success', 'Results saved successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while saving results.');
        }
    }
    public function calculateGrade($total)
    {
        if ($total >= 70) return 'A';
        if ($total >= 60) return 'B';
        if ($total >= 50) return 'C';
        if ($total >= 45) return 'D';
        if ($total >= 40) return 'E';
        return 'F';
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
            'subjects' => $this->subjects,  // use loaded subjects
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
