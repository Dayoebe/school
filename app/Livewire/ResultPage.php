<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{
    StudentRecord,
    Subject,
    AcademicYear,
    Semester,
    Result,
    Section,
    TermReport,
    MyClass
};

class ResultPage extends Component
{
    use WithPagination;

    public $selectedClass;
    public $selectedSection;
    public $studentSearch = '';
    public $perPage = 10;
    public $showStudents = false;
    public $mode = 'index';
    public $currentStudentId;
    public $subjects = [];
    public $results = [];
    public $selectedSubject = '';
    public $studentRecord;
    public $academicYearId;
    public $showResults = false;
    public $subjectResults = [];
    public $classResults = [];
    public $academicYears;
    public $semesters;
    public $classes;
    public $semesterId;
    public $positions = [];
    public $grandTotal = 0;
    public $grandTotalTest = 0;
    public $grandTotalExam = 0;
    public $totalPossibleMarks = 0;
    public $percentage = 0;
    public $recentActivities = [];
    public $bulkEditMode = false;
    public $selectedSubjectForBulkEdit = null;
    public $bulkResults = [];
    public $bulkStudents = [];
    // CHANGE 1: Set default principalComment to empty string
    public $principalComment = ''; 
    public $overallTeacherComment = ''; // Also set teacher comment to empty string
    public $isSaving = false;
    public $showSubjectModal = false; // Added for subject modal
    protected $paginationTheme = 'tailwind';

    public function rules()
    {
        $rules = [];
        foreach ($this->subjects as $subject) {
            $id = $subject->id;
            $rules["results.$id.ca1_score"] = 'nullable|numeric|min:0|max:10';
            $rules["results.$id.ca2_score"] = 'nullable|numeric|min:0|max:10';
            $rules["results.$id.ca3_score"] = 'nullable|numeric|min:0|max:10';
            $rules["results.$id.ca4_score"] = 'nullable|numeric|min:0|max:10';
            $rules["results.$id.exam_score"] = 'nullable|numeric|min:0|max:60';
            $rules["results.$id.comment"] = 'nullable|string|max:255';
        }
        return $rules;
    }


    // Fix for Selection Reset Problem
    public function goToAcademicOverview()
    {
        $this->validate([
            'academicYearId' => 'required|exists:academic_years,id',
            'semesterId' => 'required|exists:semesters,id',
            'selectedClass' => 'required|exists:my_classes,id',
        ]);

        // Store the selections in session
        session([
            'results_academic_year_id' => $this->academicYearId,
            'results_semester_id' => $this->semesterId,
            'results_selected_class' => $this->selectedClass,
        ]);

        $this->showStudents = true;
        $this->dispatch('showSuccess', 'Academic period selection confirmed!');
    }

    public function saveBulkResults()
{
    $this->validate([
        'bulkResults.*.ca1_score' => 'nullable|numeric|min:0|max:10',
        'bulkResults.*.ca2_score' => 'nullable|numeric|min:0|max:10',
        'bulkResults.*.ca3_score' => 'nullable|numeric|min:0|max:10',
        'bulkResults.*.ca4_score' => 'nullable|numeric|min:0|max:10',
        'bulkResults.*.exam_score' => 'nullable|numeric|min:0|max:60',
        'bulkResults.*.comment' => 'nullable|string|max:255',
    ]);

    $this->isSaving = true;
    try {
        DB::beginTransaction();

        $savedCount = 0;
        foreach ($this->bulkResults as $studentId => $data) {
            // Skip if no scores provided
            if (
                empty($data['ca1_score']) && empty($data['ca2_score']) &&
                empty($data['ca3_score']) && empty($data['ca4_score']) &&
                empty($data['exam_score'])
            ) {
                continue;
            }

            $ca1 = isset($data['ca1_score']) ? (float)$data['ca1_score'] : 0;
            $ca2 = isset($data['ca2_score']) ? (float)$data['ca2_score'] : 0;
            $ca3 = isset($data['ca3_score']) ? (float)$data['ca3_score'] : 0;
            $ca4 = isset($data['ca4_score']) ? (float)$data['ca4_score'] : 0;
            $exam = isset($data['exam_score']) ? (float)$data['exam_score'] : 0; 

            $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
            $comment = $data['comment'] ?? $this->getDefaultComment($total);

            Result::updateOrCreate(
                [
                    'student_record_id' => $studentId,
                    'subject_id' => $this->selectedSubjectForBulkEdit,
                    'academic_year_id' => $this->academicYearId,
                    'semester_id' => $this->semesterId,
                ],
                [
                    'ca1_score' => $ca1,
                    'ca2_score' => $ca2,
                    'ca3_score' => $ca3,
                    'ca4_score' => $ca4,
                    'exam_score' => $exam,
                    'teacher_comment' => $comment,
                    'total_score' => $total,
                    'approved' => false,
                ]
            );

            $savedCount++;
        }

        DB::commit();

        $this->dispatch('showSuccess', "Successfully saved results for {$savedCount} students!");
        $this->bulkEditMode = false;
    } catch (\Exception $e) {
        DB::rollBack();
        $this->dispatch('showSuccess', 'Error saving results: ' . $e->getMessage());
        logger()->error('Bulk result save error: ' . $e->getMessage());
    } finally {
        $this->isSaving = false;
    }
}


    protected function getDefaultComment($score)
    {
        return match (true) {
            $score >= 75 => 'Distinction',
            $score >= 70 => 'Very good',
            $score >= 65 => 'Good',
            $score >= 60 => 'Credit',
            $score >= 55 => 'Credit',
            $score >= 50 => 'Credit',
            $score >= 45 => 'Pass',
            $score >= 40 => 'Pass',
            default => 'Fail',
        };
    }

    // Add this method for real-time updates (optional)
    public function updatedBulkResults($value, $key)
    {
        // Parse the key to get student ID and field
        $parts = explode('.', $key);
        if (count($parts) === 3) {
            [, $studentId, $field] = $parts;

            // Ensure the value is properly formatted as a number
            if (in_array($field, ['ca1_score', 'ca2_score', 'ca3_score', 'ca4_score', 'exam_score'])) {
                $this->bulkResults[$studentId][$field] = is_numeric($value) ? (float)$value : null;
            }
        }
    }


    public function openSubjectBulkEdit($subjectId)
    {
        $this->selectedSubjectForBulkEdit = $subjectId;

        $this->bulkStudents = StudentRecord::whereHas('studentSubjects', function ($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        })
            ->whereHas('user', function ($q) {
                $q->whereNull('deleted_at'); // Hide deleted users
            })
            ->when($this->selectedClass, function ($query) {
                $query->where('my_class_id', $this->selectedClass);
            })
            ->when($this->selectedSection, function ($query) {
                $query->where('section_id', $this->selectedSection);
            })
            ->with(['user', 'myClass', 'section', 'results' => function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId)
                    ->where('academic_year_id', $this->academicYearId)
                    ->where('semester_id', $this->semesterId);
            }])
            ->join('users', 'student_records.user_id', '=', 'users.id') // Join with users table
            ->orderBy('users.name') // Order by student name
            ->select('student_records.*') // Ensure we select student records
            ->get();

        $this->bulkResults = [];
        foreach ($this->bulkStudents as $student) {
            $existing = $student->results->first();
            $this->bulkResults[$student->id] = [
                'ca1_score' => $existing?->ca1_score ?? null,
                'ca2_score' => $existing?->ca2_score ?? null,
                'ca3_score' => $existing?->ca3_score ?? null,
                'ca4_score' => $existing?->ca4_score ?? null,
                'exam_score' => $existing?->exam_score ?? null,
                'comment' => $existing?->teacher_comment ?? '',
            ];
        }

        $this->showSubjectModal = false;
        $this->dispatch('show-loading');

        $this->bulkEditMode = true;
        $this->dispatch('hide-loading');
    }



    public function mount($studentId = null)
    {
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $this->classes = MyClass::all();
        $this->setDefaultAcademicYearAndSemester();

        $this->recentActivities = [
            ['icon' => 'upload', 'action' => 'Bulk upload initiated', 'time' => now()->subMinutes(5)->diffForHumans()],
            ['icon' => 'user-edit', 'action' => 'Individual result updated', 'time' => now()->subHours(2)->diffForHumans()],
            ['icon' => 'eye', 'action' => 'Results viewed', 'time' => now()->subDays(1)->diffForHumans()],
        ];
        $this->subjects = auth()->user()->isAdmin() ? Subject::all() : auth()->user()->assignedSubjects;
        $this->setDefaultAcademicYearAndSemester();

        if ($studentId) {
            $this->studentRecord = StudentRecord::findOrFail($studentId);
            $this->results = $this->initializeResults(); // Assuming initializeResults exists and is appropriate
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
                    'ca1_score' => $existing?->ca1_score ?? '',
                    'ca2_score' => $existing?->ca2_score ?? '',
                    'ca3_score' => $existing?->ca3_score ?? '',
                    'ca4_score' => $existing?->ca4_score ?? '',
                    'exam_score' => $existing?->exam_score ?? '',
                    'comment' => $existing?->teacher_comment ?? '',
                ];
            }

            $termReport = TermReport::where('student_record_id', $this->studentRecord->id)
                ->where('academic_year_id', $this->academicYearId)
                ->where('semester_id', $this->semesterId)
                ->first();

            if ($termReport) {
                $this->overallTeacherComment = $termReport->class_teacher_comment ?? '';
                $this->principalComment = $termReport->principal_comment ?? '';
            }
        }
    }

    // Add this method if it doesn't exist, or ensure it returns an array
    private function initializeResults()
    {
        return [];
    }


    public function getFilteredStudentsProperty()
    {
        $query = StudentRecord::query()
            ->with('user')
            ->where('is_graduated', false)
            ->whereHas('user', function ($q) {
                $q->whereNull('deleted_at');
            });


        if ($this->selectedClass) {
            $query->where('my_class_id', $this->selectedClass);
        }

        if ($this->selectedSection) {
            $query->where('section_id', $this->selectedSection);
        }

        if ($this->studentSearch) {
            $query->whereHas('user', function ($q) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($this->studentSearch) . '%']);
            });
        }

        if ($this->selectedSubject) {
            $query->whereHas('studentSubjects', function ($q) {
                $q->where('subject_id', $this->selectedSubject);
            });
        }

        return $query
            ->join('users', 'student_records.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('student_records.*')
            ->paginate($this->perPage);
    }

    public function updatedSelectedSubject()
    {
        if ($this->selectedSubject) {
            $this->showStudents = true;
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

    public function updatedAcademicYearId()
    {
        $this->semesterId = Semester::where('academic_year_id', $this->academicYearId)->first()?->id;
        $this->semesters = Semester::where('academic_year_id', $this->academicYearId)->get();
        $this->semesterId = $this->semesters->first()?->id;
        $this->reset(['selectedClass', 'selectedSubject', 'showResults']);
    }

    public function showFilteredStudents()
    {
        $this->showStudents = true;
    }

    public function getSubjectsProperty()
    {
        if (!$this->selectedClass) {
            return collect();
        }

        return Subject::where('my_class_id', $this->selectedClass)
            ->when($this->selectedSection, fn($query) => $query->where('section_id', $this->selectedSection))
            ->get();
    }

    public function updatedSelectedClass()
    {
        $this->reset(['selectedSubject', 'showStudents']);
        $this->subjects = $this->getSubjectsProperty();
    }

    public function goToUpload($studentId)
    {
        $this->mode = 'upload';
        $this->currentStudentId = $studentId;
        $this->studentRecord = StudentRecord::findOrFail($studentId);

        $this->subjects = $this->selectedSubject
            ? Subject::where('id', $this->selectedSubject)->where('my_class_id', $this->studentRecord->my_class_id)->get()
            : Subject::where('my_class_id', $this->studentRecord->my_class_id)->get();

        foreach ($this->subjects as $subject) {
            $existing = Result::where([
                'student_record_id' => $this->studentRecord->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
            ])->first();

            $this->results[$subject->id] = [
                'ca1_score' => $existing?->ca1_score,
                'ca2_score' => $existing?->ca2_score,
                'ca3_score' => $existing?->ca3_score,
                'ca4_score' => $existing?->ca4_score,
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

        $this->grandTotalTest = 0;
        $this->grandTotalExam = 0;

        foreach ($this->subjects as $subject) {
            $existing = Result::where([
                'student_record_id' => $this->studentRecord->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
            ])->first();

            $ca1 = (int) ($existing?->ca1_score ?? 0);
            $ca2 = (int) ($existing?->ca2_score ?? 0);
            $ca3 = (int) ($existing?->ca3_score ?? 0);
            $ca4 = (int) ($existing?->ca4_score ?? 0);
            $exam = (int) ($existing?->exam_score ?? 0);
            $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;

            $this->results[$subject->id] = [
                'ca1_score' => $ca1,
                'ca2_score' => $ca2,
                'ca3_score' => $ca3,
                'ca4_score' => $ca4,
                'exam_score' => $exam,
                'total_score' => $total,
                'comment' => $existing?->teacher_comment ?? '',
            ];

            $this->grandTotalTest += $ca1 + $ca2 + $ca3 + $ca4;
            $this->grandTotalExam += $exam;
            $this->grandTotal += $total;
        }

        $termReport = TermReport::where([
            'student_record_id' => $this->studentRecord->id,
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
        ])->first();

        if ($termReport) {
            $this->overallTeacherComment = $termReport->class_teacher_comment ?? '';
            $this->principalComment = $termReport->principal_comment ?? '';
        }

        $this->totalPossibleMarks = count($this->subjects) * 100;
        $this->percentage = $this->totalPossibleMarks > 0 ? round(($this->grandTotal / $this->totalPossibleMarks) * 100, 2) : 0;

        $this->mode = 'view';
    }

    public function goBack()
    {
        $this->mode = 'index';
        $this->currentStudentId = null;
        $this->results = [];
    }

    public function calculateGrade($total)
    {
        return match (true) {
            $total >= 75 => 'A1',
            $total >= 70 => 'B2',
            $total >= 65 => 'B3',
            $total >= 60 => 'C4',
            $total >= 55 => 'C5',
            $total >= 50 => 'C6',
            $total >= 45 => 'D7',
            $total >= 40 => 'E8',
            default => 'F9',
        };
    }

    public function updatedResults($value, $key)
    {
        [$subjectId, $field] = explode('.', $key);
        $this->results[$subjectId][$field] = (int) $value;

        try {
            $this->validateOnly("results.$key");
        } catch (\Illuminate\Validation\ValidationException) {
            // Ignore validation errors for real-time updates
        }

        $entry = $this->results[$subjectId];

        $ca1 = (int) ($entry['ca1_score'] ?? 0);
        $ca2 = (int) ($entry['ca2_score'] ?? 0);
        $ca3 = (int) ($entry['ca3_score'] ?? 0);
        $ca4 = (int) ($entry['ca4_score'] ?? 0);
        $exam = (int) ($entry['exam_score'] ?? 0);
        $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
        $grade = $this->calculateGrade($total);

        $comment = match ($grade) {
            'A1' => 'Distinction',
            'B2' => 'Very good',
            'B3' => 'Good',
            'C4' => 'Credit',
            'C5' => 'Credit',
            'C6' => 'Credit',
            'D7' => 'Pass',
            'E8' => 'Pass',
            'F9' => 'Fail',
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
            'ca1_score' => $ca1,
            'ca2_score' => $ca2,
            'ca3_score' => $ca3,
            'ca4_score' => $ca4,
            'exam_score' => $exam,
            'total_score' => $total,
            'teacher_comment' => $entry['comment'] ?? '',
            'approved' => false,
        ]);
    }

    public function saveResults()
    {
        $this->validate([
            'results.*.ca1_score' => 'nullable|numeric|min:0|max:10',
            'results.*.ca2_score' => 'nullable|numeric|min:0|max:10',
            'results.*.ca3_score' => 'nullable|numeric|min:0|max:10',
            'results.*.ca4_score' => 'nullable|numeric|min:0|max:10',
            'results.*.exam_score' => 'nullable|numeric|min:0|max:60',
            'results.*.comment' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () {
                foreach ($this->results as $subjectId => $data) {
                    $ca1 = isset($data['ca1_score']) ? (int) $data['ca1_score'] : null;
                    $ca2 = isset($data['ca2_score']) ? (int) $data['ca2_score'] : null;
                    $ca3 = isset($data['ca3_score']) ? (int) $data['ca3_score'] : null;
                    $ca4 = isset($data['ca4_score']) ? (int) $data['ca4_score'] : null;
                    $exam = isset($data['exam_score']) ? (int) $data['exam_score'] : null;
                    $total = ($ca1 ?? 0) + ($ca2 ?? 0) + ($ca3 ?? 0) + ($ca4 ?? 0) + ($exam ?? 0);
                    $comment = $data['comment'] ?? null;

                    if ($ca1 === null && $ca2 === null && $ca3 === null && $ca4 === null && $exam === null) {
                        continue;
                    }

                    Result::updateOrCreate(
                        [
                            'student_record_id' => $this->studentRecord->id,
                            'subject_id' => $subjectId,
                            'academic_year_id' => $this->academicYearId,
                            'semester_id' => $this->semesterId,
                        ],
                        [
                            'ca1_score' => $ca1,
                            'ca2_score' => $ca2,
                            'ca3_score' => $ca3,
                            'ca4_score' => $ca4,
                            'exam_score' => $exam,
                            'total_score' => $total,
                            'teacher_comment' => $comment,
                            'approved' => false,
                        ]
                    );
                }

                // CHANGE 2: Conditionally set principal_comment to null if it's the default empty string
                $principalCommentToSave = empty($this->principalComment) ? null : $this->principalComment;
                $teacherCommentToSave = empty($this->overallTeacherComment) ? null : $this->overallTeacherComment;


                TermReport::updateOrCreate(
                    [
                        'student_record_id' => $this->studentRecord->id,
                        'academic_year_id' => $this->academicYearId,
                        'semester_id' => $this->semesterId,
                    ],
                    [
                        'class_teacher_comment' => $teacherCommentToSave,
                        'principal_comment' => $principalCommentToSave,
                    ]
                );
            });

            session()->flash('success', 'Results saved successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save results. Error: ' . $e->getMessage());
        }
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
            'subjects' => $this->subjects,
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

    public function deleteResult($subjectId)
    {
        try {
            Result::where([
                'student_record_id' => $this->studentRecord->id,
                'subject_id' => $subjectId,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
            ])->delete();

            // Reset to empty values instead of zeros
            $this->results[$subjectId] = [
                'ca1_score' => null,
                'ca2_score' => null,
                'ca3_score' => null,
                'ca4_score' => null,
                'exam_score' => null,
                'comment' => '',
                'grade' => '',
            ];

            session()->flash('success', 'Result deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete result: ' . $e->getMessage());
        }
    }

    public function deleteBulkResult($studentId)
    {
        try {
            Result::where([
                'student_record_id' => $studentId,
                'subject_id' => $this->selectedSubjectForBulkEdit,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
            ])->delete();

            // Reset the bulk result fields for this student
            $this->bulkResults[$studentId] = [
                'ca1_score' => null,
                'ca2_score' => null,
                'ca3_score' => null,
                'ca4_score' => null,
                'exam_score' => null,
                'comment' => '',
            ];

            $this->dispatch('showSuccess', 'Result deleted successfully!');
        } catch (\Exception $e) {
            $this->dispatch('showSuccess', 'Failed to delete result: ' . $e->getMessage());
        }
    }

    public function clearFilters()
    {
        $this->reset(['selectedClass', 'selectedSection', 'studentSearch', 'selectedSubject']);
        $this->showStudents = false;
    }

    public function updatedSelectedSection()
    {
        $this->resetPage();
    }

    public function updatedStudentSearch()
    {
        $this->resetPage();
    }
}
