<?php

namespace App\Livewire\Result;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
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
    public function openSubjectBulkEdit($subjectId)
    {
        // Validate subject exists and belongs to selected class
        $subject = Subject::find($subjectId);

        if (!$subject) {
            $this->dispatch('showError', 'Subject not found.');
            return;
        }

        if (!$this->selectedClass) {
            $this->dispatch('showError', 'Please select a class first.');
            return;
        }

        if ($subject->my_class_id != $this->selectedClass) {
            $this->dispatch('showError', 'Subject does not belong to the selected class.');
            return;
        }

        // Validate academic year and semester
        if (!$this->academicYearId || !$this->semesterId) {
            $this->dispatch('showError', 'Please select academic year and semester.');
            return;
        }

        $semesterValid = Semester::where('id', $this->semesterId)
            ->where('academic_year_id', $this->academicYearId)
            ->exists();

        if (!$semesterValid) {
            $this->dispatch('showError', 'Invalid semester for the selected academic year.');
            return;
        }

        $this->selectedSubjectForBulkEdit = $subjectId;

        // Get students for THIS academic year from pivot table
        $pivotQuery = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId)
            ->where('my_class_id', $this->selectedClass);

        if ($this->selectedSection) {
            $pivotQuery->where('section_id', $this->selectedSection);
        }

        $studentRecordIds = $pivotQuery->pluck('student_record_id');

        if ($studentRecordIds->isEmpty()) {
            $this->dispatch('showError', 'No students found for this class in the current academic year.');
            return;
        }

        // 🔥 FIX N+1: Eager load user relationship and batch load results
        $this->bulkStudents = StudentRecord::whereIn('student_records.id', $studentRecordIds)
            ->whereHas('studentSubjects', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            })
            ->whereHas('user', function ($q) {
                $q->whereNull('deleted_at')
                    ->where('school_id', auth()->user()->school_id);
            })
            ->with([
                'user', // 🔥 CRITICAL: Eager load user to prevent N+1
                'myClass',
                'section'
            ])
            ->join('users', 'student_records.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('student_records.*')
            ->get();

        if ($this->bulkStudents->isEmpty()) {
            $this->dispatch('showError', 'No students enrolled in this subject for the selected class/section.');
            return;
        }

        // 🔥 OPTIMIZATION: Batch load all results in one query
        $existingResults = Result::whereIn('student_record_id', $this->bulkStudents->pluck('id'))
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->get()
            ->keyBy('student_record_id');

        // Initialize bulk results array using pre-loaded results
        $this->bulkResults = [];
        foreach ($this->bulkStudents as $student) {
            $existing = $existingResults->get($student->id);
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
        $this->bulkEditMode = true;

        $this->dispatch('showSuccess', "Loaded {$this->bulkStudents->count()} students for {$subject->name}");
    }

    // Replace saveBulkResults with enhanced validation
    public function saveBulkResults()
    {
        // 🔥 FIX #1: Validate before processing
        if (!$this->selectedSubjectForBulkEdit) {
            $this->dispatch('showError', 'No subject selected.');
            return;
        }

        if (empty($this->bulkResults)) {
            $this->dispatch('showError', 'No results to save.');
            return;
        }

        // Validate subject belongs to class
        $subject = Subject::find($this->selectedSubjectForBulkEdit);
        if (!$subject || $subject->my_class_id != $this->selectedClass) {
            $this->dispatch('showError', 'Invalid subject for the selected class.');
            return;
        }

        // Validate semester belongs to academic year
        $semesterValid = Semester::where('id', $this->semesterId)
            ->where('academic_year_id', $this->academicYearId)
            ->exists();

        if (!$semesterValid) {
            $this->dispatch('showError', 'Invalid semester for the selected academic year.');
            return;
        }

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
            $skippedCount = 0;
            $errorStudents = [];

            foreach ($this->bulkResults as $studentId => $data) {
                // 🔥 FIX #2: Validate student exists and has academic year record
                $studentRecord = StudentRecord::find($studentId);

                if (!$studentRecord) {
                    $skippedCount++;
                    continue;
                }

                // Verify student has record for this academic year
                $yearRecord = DB::table('academic_year_student_record')
                    ->where('student_record_id', $studentId)
                    ->where('academic_year_id', $this->academicYearId)
                    ->where('my_class_id', $this->selectedClass)
                    ->first();

                if (!$yearRecord) {
                    $errorStudents[] = $studentRecord->user->name ?? 'Unknown';
                    $skippedCount++;
                    \Log::warning('Student has no academic year record', [
                        'student_record_id' => $studentId,
                        'academic_year_id' => $this->academicYearId
                    ]);
                    continue;
                }

                // 🔥 FIX #3: Verify subject is assigned to student
                if (!$studentRecord->studentSubjects()->where('subject_id', $this->selectedSubjectForBulkEdit)->exists()) {
                    $errorStudents[] = $studentRecord->user->name ?? 'Unknown';
                    $skippedCount++;
                    \Log::warning('Subject not assigned to student', [
                        'student_record_id' => $studentId,
                        'subject_id' => $this->selectedSubjectForBulkEdit
                    ]);
                    continue;
                }

                // Convert to integer, default to null if empty
                $ca1 = $this->nullIfEmpty($data['ca1_score'] ?? null);
                $ca2 = $this->nullIfEmpty($data['ca2_score'] ?? null);
                $ca3 = $this->nullIfEmpty($data['ca3_score'] ?? null);
                $ca4 = $this->nullIfEmpty($data['ca4_score'] ?? null);
                $exam = $this->nullIfEmpty($data['exam_score'] ?? null);
                $comment = $data['comment'] ?? null;

                // Calculate total only if at least one score exists
                $total = null;
                if (!is_null($ca1))
                    $total = ($total ?? 0) + $ca1;
                if (!is_null($ca2))
                    $total = ($total ?? 0) + $ca2;
                if (!is_null($ca3))
                    $total = ($total ?? 0) + $ca3;
                if (!is_null($ca4))
                    $total = ($total ?? 0) + $ca4;
                if (!is_null($exam))
                    $total = ($total ?? 0) + $exam;

                // Only update or create if there's at least one score or comment
                if (!is_null($total) || !empty($comment)) {
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
            }

            DB::commit();

            // Build detailed success/error message
            $message = "Successfully saved results for {$savedCount} student(s)!";

            if ($skippedCount > 0) {
                $message .= " ({$skippedCount} skipped due to validation errors)";
            }

            if (!empty($errorStudents)) {
                $message .= " Students with errors: " . implode(', ', array_slice($errorStudents, 0, 5));
                if (count($errorStudents) > 5) {
                    $message .= ' and ' . (count($errorStudents) - 5) . ' more.';
                }
            }

            $this->dispatch('showSuccess', $message);

            \Log::info('Bulk results saved', [
                'saved' => $savedCount,
                'skipped' => $skippedCount,
                'subject_id' => $this->selectedSubjectForBulkEdit,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId
            ]);

            $this->bulkEditMode = false;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showError', 'Error saving results: ' . $e->getMessage());
            logger()->error('Bulk result save error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            $this->isSaving = false;
        }
    }

    // Keep the existing nullIfEmpty helper method
    protected function nullIfEmpty($value)
    {
        return ($value === null || $value === '') ? null : (int) $value;
    }
    public function mount($studentId = null)
    {
        // 🔥 FIX: Check if user is authenticated first
        if (!auth()->check()) {
            abort(403, 'Unauthorized access');
        }
    
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $this->classes = MyClass::all();
        $this->semesters = collect(); 
        $this->setDefaultAcademicYearAndSemester();        
        
        if (!$this->academicYearId) {
            session()->flash('error', 'No academic year found. Please create one first.');
            return;
        }          
       
        // This property is not used in the current Blade, but kept for consistency
        $this->recentActivities = [
            ['icon' => 'upload', 'action' => 'Bulk upload initiated', 'time' => now()->subMinutes(5)->diffForHumans()],
            ['icon' => 'user-edit', 'action' => 'Individual result updated', 'time' => now()->subHours(2)->diffForHumans()],
            ['icon' => 'eye', 'action' => 'Results viewed', 'time' => now()->subDays(1)->diffForHumans()],
        ];
        
        // 🔥 FIX: Safe auth check with null coalescing
        if (auth()->user()?->isAdmin()) {
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
                    'ca1_score' => $existing?->ca1_score ?? null,
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
    }
    private function initializeResults()
    {
        return [];
    }




    /**
     * Fixed getFilteredStudentsProperty - Already has eager loading
     * This is correct, just documenting for completeness
     */
    public function getFilteredStudentsProperty()
    {
        if (!$this->academicYearId) {
            return collect();
        }

        $pivotQuery = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId)
            ->where('my_class_id', $this->selectedClass);

        if ($this->selectedSection) {
            $pivotQuery->where('section_id', $this->selectedSection);
        }

        $studentRecordIds = $pivotQuery->pluck('student_record_id');

        if ($studentRecordIds->isEmpty()) {
            return collect();
        }

        $query = StudentRecord::query()
            ->whereIn('student_records.id', $studentRecordIds)
            ->with([ // 🔥 Already has eager loading - GOOD!
                'user',
                'results' => function ($q) {
                    $q->where('academic_year_id', $this->academicYearId)
                        ->where('semester_id', $this->semesterId);
                }
            ])
            ->where('student_records.is_graduated', false)
            ->whereHas('user', function ($q) {
                $q->whereNull('deleted_at');
            });

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

    /**
     * Fixed goToUpload - Already optimized, but ensure consistency
     */
    public function goToUpload($studentId)
    {
        $this->mode = 'upload';
        $this->currentStudentId = $studentId;

        // 🔥 Eager load relationships
        $this->studentRecord = StudentRecord::with(['user', 'myClass', 'section'])
            ->findOrFail($studentId);

        // Subjects for the current student's class
        $this->subjects = Subject::where('my_class_id', $this->studentRecord->my_class_id)
            ->orderBy('name')
            ->get();

        // 🔥 OPTIMIZATION: Batch load all results for this student
        $existingResults = Result::where('student_record_id', $this->studentRecord->id)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->get()
            ->keyBy('subject_id');

        foreach ($this->subjects as $subject) {
            $existing = $existingResults->get($subject->id);

            $this->results[$subject->id] = [
                'ca1_score' => $existing?->ca1_score ?? null,
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

        // 🔥 OPTIMIZATION: Eager load all relationships
        $this->studentRecord = StudentRecord::with([
            'myClass',
            'section',
            'user'
        ])->findOrFail($studentId);

        if (empty($this->academicYearId)) {
            $this->academicYearId = AcademicYear::latest()->first()?->id;
        }
        if (empty($this->semesterId)) {
            $this->semesterId = Semester::where('academic_year_id', $this->academicYearId)->first()?->id;
        }

        // Calculate position
        $this->positions[$this->studentRecord->id] = $this->calculateStudentAndClassPositions(
            $this->studentRecord->id,
            $this->academicYearId,
            $this->semesterId,
            $this->studentRecord->my_class_id
        );

        // 🔥 OPTIMIZATION: Get subjects with results in one query
        $resultsCollection = Result::where('student_record_id', $this->studentRecord->id)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->with('subject')
            ->get();

        $this->subjects = $resultsCollection->pluck('subject')->sortBy('name');

        $this->grandTotalTest = 0;
        $this->grandTotalExam = 0;
        $this->grandTotal = 0;
        $this->results = [];

        // Process results (already loaded, no additional queries)
        foreach ($resultsCollection as $result) {
            $subject = $result->subject;

            $ca1 = (int) ($result->ca1_score ?? 0);
            $ca2 = (int) ($result->ca2_score ?? 0);
            $ca3 = (int) ($result->ca3_score ?? 0);
            $ca4 = (int) ($result->ca4_score ?? 0);
            $exam = (int) ($result->exam_score ?? 0);
            $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
            $grade = $this->calculateGrade($total);

            $teacherComment = $result->teacher_comment;
            if (empty($teacherComment) || $teacherComment === 'Impressive') {
                $teacherComment = $this->getDefaultComment($total);
            }

            $this->results[$subject->id] = [
                'ca1_score' => $ca1,
                'ca2_score' => $ca2,
                'ca3_score' => $ca3,
                'ca4_score' => $ca4,
                'exam_score' => $exam,
                'total_score' => $total,
                'grade' => $grade,
                'comment' => $teacherComment,
            ];

            $this->grandTotalTest += $ca1 + $ca2 + $ca3 + $ca4;
            $this->grandTotalExam += $exam;
            $this->grandTotal += $total;
        }

        // Load term report
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

    // Replace the existing updatedResults method with this:
    public function updatedResults($value, $key)
    {
        [$subjectId, $field] = explode('.', $key);

        // Validate and convert to integer, handling empty string as null
        $validatedValue = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        if ($validatedValue === null && $value !== '' && $value !== null) {
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
        $this->resetErrorBag("results.$subjectId.$field");

        // Call the transaction-wrapped save method
        $this->saveIndividualResult($subjectId, $field, $validatedValue);
    }

    // 🔥 NEW METHOD: Transaction-wrapped individual save
    protected function saveIndividualResult($subjectId, $field, $value)
    {
        try {
            DB::beginTransaction();

            // Validate academic year record exists
            $yearRecord = DB::table('academic_year_student_record')
                ->where('student_record_id', $this->studentRecord->id)
                ->where('academic_year_id', $this->academicYearId)
                ->first();

            if (!$yearRecord) {
                DB::rollBack();
                $this->dispatch('showError', 'Student has no record for this academic year.');
                return;
            }

            // Validate semester belongs to academic year
            $semesterValid = Semester::where('id', $this->semesterId)
                ->where('academic_year_id', $this->academicYearId)
                ->exists();

            if (!$semesterValid) {
                DB::rollBack();
                $this->dispatch('showError', 'Invalid semester for this academic year.');
                return;
            }

            // Validate subject belongs to student's class for this year
            $subject = Subject::find($subjectId);
            if (!$subject || $subject->my_class_id != $yearRecord->my_class_id) {
                DB::rollBack();
                $this->dispatch('showError', 'Subject does not belong to student\'s class.');
                return;
            }

            // Verify subject is assigned to student
            if (!$this->studentRecord->studentSubjects()->where('subject_id', $subjectId)->exists()) {
                DB::rollBack();
                $this->dispatch('showError', 'Subject not assigned to student.');
                return;
            }

            // Calculate total with validated scores
            $ca1 = (int) ($this->results[$subjectId]['ca1_score'] ?? 0);
            $ca2 = (int) ($this->results[$subjectId]['ca2_score'] ?? 0);
            $ca3 = (int) ($this->results[$subjectId]['ca3_score'] ?? 0);
            $ca4 = (int) ($this->results[$subjectId]['ca4_score'] ?? 0);
            $exam = (int) ($this->results[$subjectId]['exam_score'] ?? 0);
            $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
            $comment = $this->getDefaultComment($total);

            // Update the component's results array
            $this->results[$subjectId]['total_score'] = $total;
            $this->results[$subjectId]['comment'] = $comment;

            // Save to database
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

            DB::commit();

            $this->dispatch('showSuccess', 'Score updated for ' . $subject->name);

            \Log::info('Individual result saved', [
                'student_record_id' => $this->studentRecord->id,
                'subject_id' => $subjectId,
                'field' => $field,
                'value' => $value,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showError', 'Error updating score: ' . $e->getMessage());
            logger()->error('Individual score save error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'student_record_id' => $this->studentRecord->id,
                'subject_id' => $subjectId
            ]);
        }
    }

    // Replace saveIndividualBulkResult with this transaction-safe version:
    protected function saveIndividualBulkResult($studentId, $field, $value)
    {
        try {
            DB::beginTransaction();

            // Validate academic year record
            $yearRecord = DB::table('academic_year_student_record')
                ->where('student_record_id', $studentId)
                ->where('academic_year_id', $this->academicYearId)
                ->first();

            if (!$yearRecord) {
                DB::rollBack();
                $student = StudentRecord::find($studentId);
                $this->dispatch('showError', 'Student has no record for this academic year: ' . $student->user->name);
                return;
            }

            // Validate subject belongs to class
            $subject = Subject::find($this->selectedSubjectForBulkEdit);
            if (!$subject || $subject->my_class_id != $yearRecord->my_class_id) {
                DB::rollBack();
                $this->dispatch('showError', 'Subject does not belong to student\'s class.');
                return;
            }

            // Calculate total with validated integer scores
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

                DB::commit();
                $student = StudentRecord::find($studentId);
                $this->dispatch('showSuccess', 'Result cleared for ' . $student->user->name);
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

                DB::commit();
                $student = StudentRecord::find($studentId);
                $this->dispatch('showSuccess', 'Score updated for ' . $student->user->name);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showError', 'Error updating score: ' . $e->getMessage());
            logger()->error('Individual bulk score save error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'student_id' => $studentId
            ]);
        }
    }
    // New updated method for psychomotor, affective, co-curricular scores
    public function updatedPsychomotorScores($value, $key)
    {
        $this->validateOnly("psychomotorScores.$key");
        // No real-time database save here, will be saved with saveResults()
    }
    public function updatedAffectiveScores($value, $key)
    {
        $this->validateOnly("affectiveScores.$key");
        // No real-time database save here, will be saved with saveResults()
    }
    public function updatedCoCurricularScores($value, $key)
    {
        $this->validateOnly("coCurricularScores.$key");
        // No real-time database save here, will be saved with saveResults()
    }

    public function saveResults()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                // 🔥 FIX #1: Validate semester belongs to academic year
                $semesterValid = Semester::where('id', $this->semesterId)
                    ->where('academic_year_id', $this->academicYearId)
                    ->exists();

                if (!$semesterValid) {
                    throw new \Exception('Invalid semester for the selected academic year.');
                }

                // 🔥 FIX #2: Verify student was in this class during this academic year
                $yearRecord = DB::table('academic_year_student_record')
                    ->where('student_record_id', $this->studentRecord->id)
                    ->where('academic_year_id', $this->academicYearId)
                    ->first();

                if (!$yearRecord) {
                    throw new \Exception('Student has no record for this academic year. They may have been promoted or not yet assigned.');
                }

                // Get the student's class for this academic year
                $studentClassForYear = $yearRecord->my_class_id;

                // Save subject results
                foreach ($this->subjects as $subject) {
                    $subjectId = $subject->id;
                    $data = $this->results[$subjectId] ?? [];

                    // Skip if no data for this subject
                    if (empty($data)) {
                        continue;
                    }

                    // 🔥 FIX #3: Verify subject belongs to student's class for this academic year
                    if ($subject->my_class_id != $studentClassForYear) {
                        \Log::warning('Subject does not belong to student\'s class for this academic year', [
                            'subject_id' => $subjectId,
                            'subject_name' => $subject->name,
                            'subject_class_id' => $subject->my_class_id,
                            'student_class_id' => $studentClassForYear,
                            'academic_year_id' => $this->academicYearId
                        ]);
                        throw new \Exception("Subject '{$subject->name}' does not belong to the student's class for this academic year.");
                    }

                    // 🔥 FIX #4: Verify subject is assigned to student
                    if (!$this->studentRecord->studentSubjects()->where('subject_id', $subjectId)->exists()) {
                        \Log::warning('Subject not assigned to student', [
                            'subject_id' => $subjectId,
                            'subject_name' => $subject->name,
                            'student_record_id' => $this->studentRecord->id
                        ]);
                        throw new \Exception("Subject '{$subject->name}' is not assigned to this student.");
                    }

                    // Validate scores with helper method
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

                // Validate attendance data
                if ($this->presentDays !== null && $this->absentDays !== null) {
                    if ($this->presentDays < 0 || $this->absentDays < 0) {
                        throw new \Exception('Attendance days cannot be negative.');
                    }
                }

                // Save term report with proper JSON encoding
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

                \Log::info('Results saved successfully', [
                    'student_record_id' => $this->studentRecord->id,
                    'academic_year_id' => $this->academicYearId,
                    'semester_id' => $this->semesterId,
                    'subjects_count' => count($this->subjects)
                ]);
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

    // Helper method - keep existing validateScore method
    protected function validateScore($value, $max)
    {
        $score = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        if ($score === null && $value !== null && $value !== '') {
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
    private function getDefaultPsychomotorScores()
    {
        return [
            'Handwriting' => null,
            'Verbal Fluency' => null,
            'Game/Sports' => null,
            'Handling Tools' => null,
        ];
    }

    private function getDefaultAffectiveScores()
    {
        return [
            'Punctuality' => null,
            'Neatness' => null,
            'Politeness' => null,
            'Leadership' => null,
        ];
    }

    private function getDefaultCoCurricularScores()
    {
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
    public function loadStudentData($studentId)
    {
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



    #[On('set-subject-for-upload')]
    public function setSubjectForUpload($subjectId)
    {
        $this->openSubjectBulkEdit($subjectId);
    }

}