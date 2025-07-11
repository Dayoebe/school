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
    public $recentActivities = []; // This property is not used in the current Blade, but kept for consistency
    public $bulkEditMode = false;
    public $selectedSubjectForBulkEdit = null;
    public $bulkResults = [];
    public $bulkStudents = [];
    public $principalComment = '';
    public $overallTeacherComment = '';
    public $isSaving = false;
    public $showSubjectModal = false;

    // New properties for attendance and detailed extra-curricular activities
    public $presentDays = null;
    public $absentDays = null;
    public $psychomotorScores = [
        'Handwriting' => null,
        'Verbal Fluency' => null,
        'Game/Sports' => null,
        'Handling Tools' => null,
    ];
    public $affectiveScores = [
        'Punctuality' => null,
        'Neatness' => null,
        'Politeness' => null,
        'Leadership' => null,
    ];
    public $coCurricularScores = [
        'Athletics' => null,
        'Football' => null,
        'Volley Ball' => null,
        'Table Tennis' => null,
    ];

    protected $paginationTheme = 'tailwind';

    public function rules()
    {
        $rules = [];
        
        // Subject results validation
        foreach ($this->subjects as $subject) {
            $id = $subject->id;
            $rules["results.$id.ca1_score"] = 'nullable|numeric|min:0|max:10';
            $rules["results.$id.ca2_score"] = 'nullable|numeric|min:0|max:10';
            $rules["results.$id.ca3_score"] = 'nullable|numeric|min:0|max:10';
            $rules["results.$id.ca4_score"] = 'nullable|numeric|min:0|max:10';
            $rules["results.$id.exam_score"] = 'nullable|numeric|min:0|max:60';
            $rules["results.$id.comment"] = 'nullable|string|max:255';
        }

        // Attendance validation
        $rules['presentDays'] = 'nullable|integer|min:0';
        $rules['absentDays'] = 'nullable|integer|min:0';
        
        // Extra-curricular activities validation
        foreach ($this->psychomotorScores as $trait => $value) {
            $rules["psychomotorScores.$trait"] = 'nullable|integer|min:0|max:5';
        }
        
        foreach ($this->affectiveScores as $trait => $value) {
            $rules["affectiveScores.$trait"] = 'nullable|integer|min:0|max:5';
        }
        
        foreach ($this->coCurricularScores as $activity => $value) {
            $rules["coCurricularScores.$activity"] = 'nullable|integer|min:0|max:5';
        }

        // Comments validation
        $rules['overallTeacherComment'] = 'nullable|string|max:1000';
        $rules['principalComment'] = 'nullable|string|max:1000';

        return $rules;
    }
    public function goToAcademicOverview()
    {
        $this->validate([
            'academicYearId' => 'required|exists:academic_years,id',
            'semesterId' => 'required|exists:semesters,id',
            'selectedClass' => 'required|exists:my_classes,id',
        ]);

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
                // Convert to integer, default to 0 if null or empty string
                $ca1 = filter_var($data['ca1_score'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
                $ca2 = filter_var($data['ca2_score'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
                $ca3 = filter_var($data['ca3_score'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
                $ca4 = filter_var($data['ca4_score'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
                $exam = filter_var($data['exam_score'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;

                // If all scores are empty, consider it a deletion or no entry
                if ($ca1 === 0 && $ca2 === 0 && $ca3 === 0 && $ca4 === 0 && $exam === 0 && empty($data['comment'])) {
                    Result::where([
                        'student_record_id' => $studentId,
                        'subject_id' => $this->selectedSubjectForBulkEdit,
                        'academic_year_id' => $this->academicYearId,
                        'semester_id' => $this->semesterId,
                    ])->delete();
                    // Also clear from local bulkResults array to reflect deletion
                    $this->bulkResults[$studentId] = [
                        'ca1_score' => null, 'ca2_score' => null, 'ca3_score' => null,
                        'ca4_score' => null, 'exam_score' => null, 'comment' => ''
                    ];
                    continue;
                }

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
            $this->dispatch('showError', 'Error saving results: ' . $e->getMessage());
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

    public function updatedBulkResults($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 3) {
            [$prefix, $studentId, $field] = $parts;

            // Validate and convert to integer
            if (in_array($field, ['ca1_score', 'ca2_score', 'ca3_score', 'ca4_score', 'exam_score'])) {
                $validatedValue = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                if ($validatedValue === null && $value !== '') { // If not a valid integer and not empty string
                    $this->addError("bulkResults.$studentId.$field", 'Score must be a whole number.');
                    return;
                }

                // Max value validation
                $max = ($field === 'exam_score') ? 60 : 10;
                if ($validatedValue !== null && $validatedValue > $max) {
                    $this->addError("bulkResults.$studentId.$field", "Max score is {$max}.");
                    return;
                }

                $this->bulkResults[$studentId][$field] = $validatedValue;
                $this->removePropertyError("bulkResults.$studentId.$field"); // Corrected: Clear previous errors
            }

            // Perform real-time save for individual field update
            $this->saveIndividualBulkResult($studentId, $field, $value);
        }
    }

    protected function saveIndividualBulkResult($studentId, $field, $value)
    {
        try {
            // Ensure all scores are integers for calculation
            $ca1 = filter_var($this->bulkResults[$studentId]['ca1_score'] ?? 0, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
            $ca2 = filter_var($this->bulkResults[$studentId]['ca2_score'] ?? 0, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
            $ca3 = filter_var($this->bulkResults[$studentId]['ca3_score'] ?? 0, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
            $ca4 = filter_var($this->bulkResults[$studentId]['ca4_score'] ?? 0, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
            $exam = filter_var($this->bulkResults[$studentId]['exam_score'] ?? 0, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;

            $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
            $comment = $this->bulkResults[$studentId]['comment'] ?? $this->getDefaultComment($total);

            // If all scores are empty, delete the result
            if ($ca1 === 0 && $ca2 === 0 && $ca3 === 0 && $ca4 === 0 && $exam === 0 && empty($comment)) {
                Result::where([
                    'student_record_id' => $studentId,
                    'subject_id' => $this->selectedSubjectForBulkEdit,
                    'academic_year_id' => $this->academicYearId,
                    'semester_id' => $this->semesterId,
                ])->delete();
                $this->dispatch('showSuccess', 'Result cleared for ' . StudentRecord::find($studentId)->user->name);
            } else {
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
                $this->dispatch('showSuccess', 'Score updated for ' . StudentRecord::find($studentId)->user->name);
            }
        } catch (\Exception $e) {
            $this->dispatch('showError', 'Error updating score: ' . $e->getMessage());
            logger()->error('Individual bulk score save error: ' . $e->getMessage());
        }
    }


    public function openSubjectBulkEdit($subjectId)
    {
        $this->selectedSubjectForBulkEdit = $subjectId;

        $this->bulkStudents = StudentRecord::whereHas('studentSubjects', function ($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        })
            ->whereHas('user', function ($q) {
                $q->whereNull('deleted_at');
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
            ->join('users', 'student_records.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('student_records.*')
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
        $this->dispatch('show-loading'); // This event is not defined in the provided blade, but kept.

        $this->bulkEditMode = true;
        $this->dispatch('hide-loading'); // This event is not defined in the provided blade, but kept.
    }

    public function mount($studentId = null)
    {
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $this->classes = MyClass::all();
        $this->semesters = collect(); 
        $this->setDefaultAcademicYearAndSemester();

        // This property is not used in the current Blade, but kept for consistency
        $this->recentActivities = [
            ['icon' => 'upload', 'action' => 'Bulk upload initiated', 'time' => now()->subMinutes(5)->diffForHumans()],
            ['icon' => 'user-edit', 'action' => 'Individual result updated', 'time' => now()->subHours(2)->diffForHumans()],
            ['icon' => 'eye', 'action' => 'Results viewed', 'time' => now()->subDays(1)->diffForHumans()],
        ];
        
        // Ensure subjects are loaded for the initial form, if no studentId is present
        // If a studentId is present, subjects will be loaded in goToUpload
        if (auth()->user()->isAdmin()) {
            $this->subjects = Subject::all();
        } else {
            // Assuming assignedSubjects is a relation or method on the User model
            $this->subjects = auth()->user()->assignedSubjects ?? collect();
        }

        if ($studentId) {
            $this->studentRecord = StudentRecord::findOrFail($studentId);
            $this->results = $this->initializeResults();
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
                    'ca1_score' => $existing?->ca1_score ?? null, // Use null for empty to prevent 0.00
                    'ca2_score' => $existing?->ca2_score ?? null,
                    'ca3_score' => $existing?->ca3_score ?? null,
                    'ca4_score' => $existing?->ca4_score ?? null,
                    'exam_score' => $existing?->exam_score ?? null,
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
                // Populate new fields from TermReport
                $this->presentDays = $termReport->present_days ?? null;
                $this->absentDays = $termReport->absent_days ?? null;
                // Directly assign, as accessors handle decoding
                $this->psychomotorScores = $termReport->psychomotor_traits;
                $this->affectiveScores = $termReport->affective_traits;
                $this->coCurricularScores = $termReport->co_curricular_activities;
            } else {
                // Initialize to null or default arrays if no term report exists
                $this->overallTeacherComment = '';
                $this->principalComment = '';
                $this->presentDays = null;
                $this->absentDays = null;
                $this->psychomotorScores = $this->getDefaultPsychomotorScores();
                $this->affectiveScores = $this->getDefaultAffectiveScores();
                $this->coCurricularScores = $this->getDefaultCoCurricularScores();
            }
        }
    }

    private function initializeResults()
    {
        return [];
    }

  

    public function getFilteredStudentsProperty()
    {
        $query = StudentRecord::query()
            ->with(['user', 'myClass', 'section']) // Eager load user, myClass, and section to prevent N+1
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

        // Removed filter by subject as per user request
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
        if (in_array($property, ['selectedClass', 'selectedSection', 'studentSearch', 'perPage'])) { // Added perPage here
            $this->resetPage();
        }

        if ($property === 'studentSearch') {
            $this->showStudents = false;
        }
    }

    public function setDefaultAcademicYearAndSemester()
    {
        $this->academicYearId = AcademicYear::orderByDesc('start_year')->first()?->id;
        $this->semesters = $this->academicYearId ? Semester::where('academic_year_id', $this->academicYearId)->get() : collect();
        $this->semesterId = $this->semesters->first()?->id;
    }

    public function updatedAcademicYearId()
    {
        $this->semesters = $this->academicYearId ? Semester::where('academic_year_id', $this->academicYearId)->get() : collect();
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
            // ->when($this->selectedSection, fn($query) => $query->where('section_id', $this->selectedSection)) // Removed section filter for subjects
            ->orderBy('name')
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

        // Subjects for the current student's class
        $this->subjects = Subject::where('my_class_id', $this->studentRecord->my_class_id)->orderBy('name')->get();

        foreach ($this->subjects as $subject) {
            $existing = Result::where([
                'student_record_id' => $this->studentRecord->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
            ])->first();

            $this->results[$subject->id] = [
                'ca1_score' => $existing?->ca1_score ?? null, // Use null for empty to prevent 0.00
                'ca2_score' => $existing?->ca2_score ?? null,
                'ca3_score' => $existing?->ca3_score ?? null,
                'ca4_score' => $existing?->ca4_score ?? null,
                'exam_score' => $existing?->exam_score ?? null,
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
            $this->presentDays = $termReport->present_days ?? null;
            $this->absentDays = $termReport->absent_days ?? null;
            // Directly assign, as accessors handle decoding
            $this->psychomotorScores = $termReport->psychomotor_traits;
            $this->affectiveScores = $termReport->affective_traits;
            $this->coCurricularScores = $termReport->co_curricular_activities;
        } else {
            $this->overallTeacherComment = '';
            $this->principalComment = '';
            $this->presentDays = null;
            $this->absentDays = null;
            $this->psychomotorScores = $this->getDefaultPsychomotorScores();
            $this->affectiveScores = $this->getDefaultAffectiveScores();
            $this->coCurricularScores = $this->getDefaultCoCurricularScores();
        }
    }

    public function goToView($studentId)
    {
        $this->currentStudentId = $studentId;
        $this->studentRecord = StudentRecord::findOrFail($studentId);

        if (empty($this->academicYearId)) {
            $this->academicYearId = AcademicYear::latest()->first()?->id;
        }
        if (empty($this->semesterId)) {
            $this->semesterId = Semester::where('academic_year_id', $this->academicYearId)->first()?->id;
        }

        $this->positions[$this->studentRecord->id] = $this->calculateStudentAndClassPositions(
            $this->studentRecord->id,
            $this->academicYearId,
            $this->semesterId,
            $this->studentRecord->my_class_id
        );

        $this->subjects = Subject::whereIn('id',
            Result::where('student_record_id', $this->studentRecord->id)
                ->where('academic_year_id', $this->academicYearId)
                ->where('semester_id', $this->semesterId)
                ->pluck('subject_id')
        )->orderBy('name')->get();

        $this->grandTotalTest = 0;
        $this->grandTotalExam = 0;
        $this->grandTotal = 0;

        $this->results = [];

        foreach ($this->subjects as $subject) {
            $existing = Result::where([
                'student_record_id' => $this->studentRecord->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
            ])->first();

            if (!$existing) {
                continue;
            }

            $ca1 = (int) ($existing->ca1_score ?? 0);
            $ca2 = (int) ($existing->ca2_score ?? 0);
            $ca3 = (int) ($existing->ca3_score ?? 0);
            $ca4 = (int) ($existing->ca4_score ?? 0);
            $exam = (int) ($existing->exam_score ?? 0);
            $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
            $grade = $this->calculateGrade($total);

            $teacherComment = $existing->teacher_comment;
            if (empty($teacherComment) || $teacherComment === 'Impressive') {
                $teacherComment = $this->getDefaultComment($total);
            }

            $this->results[$subject->id] = [
                'ca1_score' => $ca1,
                'ca2_score' => $ca2,
                'ca3_score' => $ca3,
                'ca4_score' => $ca4,
                'exam_score' => $exam,
                'total_score' => $total, // Ensure total is stored as int
                'grade' => $grade,
                'comment' => $teacherComment,
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
            $this->presentDays = $termReport->present_days ?? null;
            $this->absentDays = $termReport->absent_days ?? null;
            // Directly assign, as accessors handle decoding
            $this->psychomotorScores = $termReport->psychomotor_traits;
            $this->affectiveScores = $termReport->affective_traits;
            $this->coCurricularScores = $termReport->co_curricular_activities;
        } else {
            $this->overallTeacherComment = '';
            $this->principalComment = '';
            $this->presentDays = null;
            $this->absentDays = null;
            $this->psychomotorScores = $this->getDefaultPsychomotorScores();
            $this->affectiveScores = $this->getDefaultAffectiveScores();
            $this->coCurricularScores = $this->getDefaultCoCurricularScores();
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
        $this->subjects = [];
        // Reset new fields when going back
        $this->reset([
            'presentDays',
            'absentDays',
            'overallTeacherComment',
            'principalComment',
            'psychomotorScores', // Reset these arrays too
            'affectiveScores',
            'coCurricularScores',
        ]);
        // Re-initialize arrays to default structure after reset
        $this->psychomotorScores = $this->getDefaultPsychomotorScores();
        $this->affectiveScores = $this->getDefaultAffectiveScores();
        $this->coCurricularScores = $this->getDefaultCoCurricularScores();
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

    protected function calculateStudentAndClassPositions($studentId, $academicYearId, $semesterId, $classId)
    {
        $classStudents = StudentRecord::with([
            'user',
            'results' => function ($query) use ($academicYearId, $semesterId) {
                $query->where('academic_year_id', $academicYearId)
                      ->where('semester_id', $semesterId);
            }
        ])
        ->where('my_class_id', $classId)
        ->where('is_graduated', false)
        ->whereHas('user', function ($q) {
            $q->whereNull('deleted_at');
        })
        ->get();

        $totalStudentsInClass = $classStudents->count();

        $scores = $classStudents->map(function ($record) {
            return [
                'id' => $record->id,
                'total_score' => (int) $record->results->sum('total_score'), // Ensure integer
            ];
        })->sortByDesc('total_score')->values();

        $rank = 1;
        $prevScore = null;
        $studentsAtRank = 0;
        $studentPosition = 'N/A';

        foreach ($scores as $data) {
            if ($prevScore !== null && $data['total_score'] < $prevScore) {
                $rank += $studentsAtRank;
                $studentsAtRank = 1;
            } else {
                $studentsAtRank++;
            }

            if ($data['id'] == $studentId) {
                $studentPosition = $rank;
                break;
            }
            $prevScore = $data['total_score'];
        }

        return [
            'position' => $studentPosition,
            'total_students' => $totalStudentsInClass
        ];
    }

    public function updatedResults($value, $key)
    {
        [$subjectId, $field] = explode('.', $key);

        // Validate and convert to integer, handling empty string as null
        $validatedValue = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        if ($validatedValue === null && $value !== '') { // If not a valid integer and not empty string
            $this->addError("results.$subjectId.$field", 'Score must be a whole number.');
            return;
        }

        // Max value validation
        $max = 0;
        if (str_contains($field, 'ca')) {
            $max = 10;
        } elseif ($field === 'exam_score') {
            $max = 60;
        }

        if ($validatedValue !== null && $validatedValue > $max) {
            $this->addError("results.$subjectId.$field", "Max score is {$max}.");
            return;
        }

        $this->results[$subjectId][$field] = $validatedValue;
        $this->removePropertyError("results.$subjectId.$field"); // Corrected: Clear previous errors

        // Recalculate total and comment for real-time update
        $ca1 = (int) ($this->results[$subjectId]['ca1_score'] ?? 0);
        $ca2 = (int) ($this->results[$subjectId]['ca2_score'] ?? 0);
        $ca3 = (int) ($this->results[$subjectId]['ca3_score'] ?? 0);
        $ca4 = (int) ($this->results[$subjectId]['ca4_score'] ?? 0);
        $exam = (int) ($this->results[$subjectId]['exam_score'] ?? 0);
        $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
        $comment = $this->getDefaultComment($total);

        // Update the Livewire component's results array for the comment and total
        $this->results[$subjectId]['total_score'] = $total;
        $this->results[$subjectId]['comment'] = $comment;

        // Persist to database
        try {
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
                'teacher_comment' => $comment,
                'approved' => false,
            ]);
            $this->dispatch('showSuccess', 'Score updated for ' . Subject::find($subjectId)->name);
        } catch (\Exception $e) {
            $this->dispatch('showError', 'Error saving score: ' . $e->getMessage());
            logger()->error('Individual score save error: ' . $e->getMessage());
        }
    }

    // New updated method for psychomotor, affective, co-curricular scores
    public function updatedPsychomotorScores($value, $key) {
        $this->validateOnly("psychomotorScores.$key");
        // No real-time database save here, will be saved with saveResults()
    }
    public function updatedAffectiveScores($value, $key) {
        $this->validateOnly("affectiveScores.$key");
        // No real-time database save here, will be saved with saveResults()
    }
    public function updatedCoCurricularScores($value, $key) {
        $this->validateOnly("coCurricularScores.$key");
        // No real-time database save here, will be saved with saveResults()
    }

    public function saveResults()
    {
        $this->validate();
    
        try {
            DB::transaction(function () {
                // First validate all subject assignments before saving anything
                foreach ($this->subjects as $subject) {
                    $subjectId = $subject->id;
                    
                    // Skip if no scores entered for this subject
                    if (!isset($this->results[$subjectId]) || 
                        empty(array_filter($this->results[$subjectId], fn($val) => $val !== null))) {
                        continue;
                    }
    
                    // Verify subject is assigned to student
                    if (!$this->studentRecord->studentSubjects()->where('subject_id', $subjectId)->exists()) {
                        throw new \Exception("Subject {$subject->name} is not assigned to this student");
                    }
                }
    
                // Save subject results
                foreach ($this->subjects as $subject) {
                    $subjectId = $subject->id;
                    $data = $this->results[$subjectId] ?? [];
    
                    // Skip if no data for this subject
                    if (empty($data)) {
                        continue;
                    }
    
                    // Validate scores
                    $ca1 = $this->validateScore($data['ca1_score'] ?? null, 10);
                    $ca2 = $this->validateScore($data['ca2_score'] ?? null, 10);
                    $ca3 = $this->validateScore($data['ca3_score'] ?? null, 10);
                    $ca4 = $this->validateScore($data['ca4_score'] ?? null, 10);
                    $exam = $this->validateScore($data['exam_score'] ?? null, 60);
                    $comment = $data['comment'] ?? null;
    
                    $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
                    $finalComment = $comment ?? $this->getDefaultComment($total);
    
                    // Only save if there are actual scores or a comment
                    if ($total > 0 || !empty($finalComment)) {
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
                                'teacher_comment' => $finalComment,
                                'approved' => false,
                            ]
                        );
                    }
                }
    
                // Save term report (unchanged from your original)
                $psychomotorJson = !empty(array_filter($this->psychomotorScores, fn($value) => $value !== null)) 
                    ? json_encode($this->psychomotorScores) 
                    : null;
                
                $affectiveJson = !empty(array_filter($this->affectiveScores, fn($value) => $value !== null))
                    ? json_encode($this->affectiveScores)
                    : null;
                
                $coCurricularJson = !empty(array_filter($this->coCurricularScores, fn($value) => $value !== null))
                    ? json_encode($this->coCurricularScores)
                    : null;
    
                TermReport::updateOrCreate(
                    [
                        'student_record_id' => $this->studentRecord->id,
                        'academic_year_id' => $this->academicYearId,
                        'semester_id' => $this->semesterId,
                    ],
                    [
                        'class_teacher_comment' => $this->overallTeacherComment ?: null,
                        'principal_comment' => $this->principalComment ?: null,
                        'present_days' => $this->presentDays ?: null,
                        'absent_days' => $this->absentDays ?: null,
                        'psychomotor_traits' => $psychomotorJson,
                        'affective_traits' => $affectiveJson,
                        'co_curricular_activities' => $coCurricularJson,
                    ]
                );
            });
    
            $this->dispatch('showSuccess', 'All data saved successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('showError', 'Validation failed: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showError', 'Failed to save: ' . $e->getMessage());
            logger()->error('Save error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
    
    // Add this helper method to your Livewire component
    protected function validateScore($value, $max)
    {
        $score = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        
        if ($score === null && $value !== null) {
            throw new \Exception("Score must be a whole number between 0 and $max");
        }
        
        if ($score !== null && ($score < 0 || $score > $max)) {
            throw new \Exception("Score must be between 0 and $max");
        }
        
        return $score ?? 0;
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

            // Reset the local state for the deleted subject
            $this->results[$subjectId] = [
                'ca1_score' => null,
                'ca2_score' => null,
                'ca3_score' => null,
                'ca4_score' => null,
                'exam_score' => null,
                'comment' => '',
                'total_score' => 0, // Reset total score as well
            ];

            $this->dispatch('showSuccess', 'Result deleted successfully!');
        } catch (\Exception $e) {
            $this->dispatch('showError', 'Failed to delete result: ' . $e->getMessage());
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
            $this->dispatch('showError', 'Failed to delete result: ' . $e->getMessage());
        }
    }

    /**
     * Clears all scores for a specific subject for the current student.
     * This method is called from the upload-content.blade.php
     *
     * @param int $subjectId The ID of the subject whose scores are to be cleared.
     * @return void
     */
    public function clearSubjectScores($subjectId)
    {
        try {
            Result::where([
                'student_record_id' => $this->studentRecord->id,
                'subject_id' => $subjectId,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
            ])->update([
                'ca1_score' => 0,
                'ca2_score' => 0,
                'ca3_score' => 0,
                'ca4_score' => 0,
                'exam_score' => 0,
                'total_score' => 0,
                'teacher_comment' => $this->getDefaultComment(0), // Set default comment for 0 score
            ]);

            // Update the Livewire component's local results array
            $this->results[$subjectId] = [
                'ca1_score' => 0,
                'ca2_score' => 0,
                'ca3_score' => 0,
                'ca4_score' => 0,
                'exam_score' => 0,
                'comment' => $this->getDefaultComment(0),
                'total_score' => 0,
            ];

            $this->dispatch('showSuccess', Subject::find($subjectId)->name . ' scores cleared successfully!');
        } catch (\Exception $e) {
            $this->dispatch('showError', 'Failed to clear scores: ' . $e->getMessage());
            logger()->error('Clear subject scores error: ' . $e->getMessage());
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

// Add these methods to your ResultPage Livewire component

/**
 * Initialize default score structures
 */
private function getDefaultPsychomotorScores() {
    return [
        'Handwriting' => null,
        'Verbal Fluency' => null,
        'Game/Sports' => null,
        'Handling Tools' => null,
    ];
}

private function getDefaultAffectiveScores() {
    return [
        'Punctuality' => null,
        'Neatness' => null,
        'Politeness' => null,
        'Leadership' => null,
    ];
}

private function getDefaultCoCurricularScores() {
    return [
        'Athletics' => null,
        'Football' => null,
        'Volley Ball' => null,
        'Table Tennis' => null,
    ];
}

/**
 * Load student data including extra-curricular activities
 */
public function loadStudentData($studentId) {
    $this->studentRecord = StudentRecord::findOrFail($studentId);
    
    $termReport = TermReport::where([
        'student_record_id' => $this->studentRecord->id,
        'academic_year_id' => $this->academicYearId,
        'semester_id' => $this->semesterId,
    ])->first();

    if ($termReport) {
        $this->presentDays = $termReport->present_days;
        $this->absentDays = $termReport->absent_days;
        
        // Directly assign, as accessors handle decoding
        $this->psychomotorScores = $termReport->psychomotor_traits;
        $this->affectiveScores = $termReport->affective_traits;
        $this->coCurricularScores = $termReport->co_curricular_activities;
    } else {
        $this->presentDays = null;
        $this->absentDays = null;
        $this->psychomotorScores = $this->getDefaultPsychomotorScores();
        $this->affectiveScores = $this->getDefaultAffectiveScores();
        $this->coCurricularScores = $this->getDefaultCoCurricularScores();
    }
}


}