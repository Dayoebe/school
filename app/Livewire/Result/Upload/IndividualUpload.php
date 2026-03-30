<?php

namespace App\Livewire\Result\Upload;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{AcademicYear, StudentRecord, Subject, Result, TermReport, MyClass, Section, Semester};
use App\Traits\RestrictsTeacherResultUploads;
use Illuminate\Support\Facades\DB;

class IndividualUpload extends Component
{
    use RestrictsTeacherResultUploads;

    public $academicYearId;
    public $semesterId;
    public $selectedClass;
    public $selectedSection;
    public $selectedStudent;
    public $loadedClassId;
    
    // Student data
    public $studentRecord;
    public $subjects = [];
    public $results = [];
    
    // Term report data
    public $presentDays;
    public $absentDays;
    public $overallTeacherComment;
    public $principalComment;
    public $psychomotorScores = [];
    public $affectiveScores = [];
    public $coCurricularScores = [];

    public function mount()
    {
        $school = auth()->user()?->school;

        $this->academicYearId = $school?->academic_year_id;
        $this->semesterId = $school?->semester_id;
        
        $this->initializeScores();
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];
        $this->reset(['selectedClass', 'selectedSection', 'selectedStudent', 'studentRecord', 'loadedClassId']);
    }

    public function updatedSelectedClass()
    {
        if ($this->selectedClass && !$this->currentUserCanUploadResultClass($this->selectedClass, $this->academicYearId)) {
            $this->selectedClass = null;
            $this->dispatch('error', 'You can only upload results for classes assigned to you.');
        }

        $this->reset(['selectedStudent', 'studentRecord', 'selectedSection']);
        $this->loadedClassId = null;
    }

    public function updatedSelectedSection()
    {
        $this->reset(['selectedStudent', 'studentRecord']);
        $this->loadedClassId = null;
    }

    public function loadStudent()
    {
        if (!$this->selectedStudent) {
            $this->dispatch('error', 'Please select a student');
            return;
        }

        $school = auth()->user()?->school;

        if (!$this->academicYearId) {
            $this->academicYearId = $school?->academic_year_id;
        }

        if (!$this->academicYearId) {
            $this->dispatch('error', 'No active academic session is configured. Ask an admin to set the current academic session first.');
            return;
        }

        if (!$this->semesterId && $school?->semester_id) {
            $this->semesterId = Semester::where('id', $school->semester_id)
                ->where('academic_year_id', $this->academicYearId)
                ->where('school_id', auth()->user()->school_id)
                ->value('id');
        }

        if (!$this->semesterId) {
            $this->dispatch('error', 'No active term is configured for this academic session. Ask an admin to set the current term first.');
            return;
        }

        $academicYearValid = AcademicYear::where('id', $this->academicYearId)
            ->where('school_id', auth()->user()->school_id)
            ->exists();
        if (!$academicYearValid) {
            $this->dispatch('error', 'Selected academic year is not in your current school.');
            return;
        }

        $semesterValid = Semester::where('id', $this->semesterId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('school_id', auth()->user()->school_id)
            ->exists();
        if (!$semesterValid) {
            $this->dispatch('error', 'Selected term is not in your current school.');
            return;
        }

        try {
            if ($this->selectedClass && !$this->currentUserCanUploadResultClass($this->selectedClass, $this->academicYearId)) {
                $this->dispatch('error', 'You can only upload results for classes assigned to you.');
                return;
            }

            if (!$this->currentUserCanUploadResultStudent(
                $this->selectedStudent,
                $this->selectedClass,
                $this->academicYearId,
                $this->selectedSection
            )) {
                $this->dispatch('error', 'You can only load students from classes assigned to you.');
                return;
            }

            $this->studentRecord = StudentRecord::with(['user', 'myClass', 'section'])
                ->whereHas('user', function ($query) {
                    $query->where('school_id', auth()->user()->school_id)
                        ->whereNull('deleted_at');
                })
                ->findOrFail($this->selectedStudent);

            if ($this->selectedClass && !$this->classBelongsToCurrentSchool($this->selectedClass)) {
                $this->dispatch('error', 'Selected class is not in your current school.');
                return;
            }

            if ($this->selectedSection && !$this->sectionBelongsToCurrentSchoolClass($this->selectedSection, $this->selectedClass ?: $this->studentRecord->my_class_id)) {
                $this->dispatch('error', 'Selected section is not valid for your current school/class.');
                return;
            }

            // Validate/backfill student academic year record
            $yearRecord = DB::table('academic_year_student_record')
                ->where('student_record_id', $this->selectedStudent)
                ->where('academic_year_id', $this->academicYearId)
                ->first();

            if (!$yearRecord) {
                $fallbackClassId = $this->selectedClass ?: $this->studentRecord->my_class_id;
                $fallbackSectionId = $this->selectedSection ?: $this->studentRecord->section_id;

                if (!$fallbackClassId) {
                    $this->dispatch('error', 'Student has no class record for this academic year.');
                    return;
                }

                if (!$this->classBelongsToCurrentSchool($fallbackClassId)) {
                    $this->dispatch('error', 'Student class does not belong to your current school.');
                    return;
                }

                if ($fallbackSectionId && !$this->sectionBelongsToCurrentSchoolClass($fallbackSectionId, $fallbackClassId)) {
                    $fallbackSectionId = null;
                }

                DB::table('academic_year_student_record')->updateOrInsert(
                    [
                        'student_record_id' => $this->selectedStudent,
                        'academic_year_id' => $this->academicYearId,
                    ],
                    [
                        'my_class_id' => $fallbackClassId,
                        'section_id' => $fallbackSectionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $yearRecord = (object) [
                    'my_class_id' => $fallbackClassId,
                    'section_id' => $fallbackSectionId,
                ];
            }

            // Get class from academic year record (fallback to selected/current class)
            $classId = $yearRecord->my_class_id ?: $this->selectedClass ?: $this->studentRecord->my_class_id;
            if (!$this->classBelongsToCurrentSchool($classId)) {
                $this->dispatch('error', 'Class for this student is not in your current school.');
                return;
            }

            $this->loadedClassId = (int) $classId;

            $studentSectionId = $yearRecord->section_id ?: $this->selectedSection ?: $this->studentRecord->section_id;

            $allowedSubjects = $this->accessibleResultUploadSubjectsQuery($classId, $this->academicYearId)
                ->with(['classes:id', 'sections:id'])
                ->get();

            $classWideSubjectIds = $allowedSubjects
                ->filter(function (Subject $subject) use ($classId, $studentSectionId) {
                    $belongsToClass = (int) $subject->my_class_id === (int) $classId
                        || $subject->classes->contains('id', (int) $classId);

                    if (!$belongsToClass) {
                        return false;
                    }

                    if ($subject->is_general) {
                        return true;
                    }

                    return $studentSectionId
                        ? $subject->sections->contains('id', (int) $studentSectionId)
                        : false;
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();

            if ($classWideSubjectIds->isNotEmpty()) {
                $this->ensureSubjectAssignmentsForLoadedStudent($classWideSubjectIds, $classId, $studentSectionId);
            }

            $studentSubjectIds = $this->studentRecord->studentSubjects()
                ->select('subjects.id')
                ->where('subjects.school_id', auth()->user()->school_id)
                ->pluck('subjects.id')
                ->map(fn ($id) => (int) $id);

            $visibleSubjectIds = $studentSubjectIds
                ->merge($classWideSubjectIds)
                ->unique()
                ->values();

            $this->subjects = $allowedSubjects
                ->whereIn('id', $visibleSubjectIds)
                ->sortBy('name')
                ->values();
    
            // Load existing results
            $existingResults = Result::where('student_record_id', $this->selectedStudent)
                ->where('academic_year_id', $this->academicYearId)
                ->where('semester_id', $this->semesterId)
                ->get()
                ->keyBy('subject_id');
    
            // Initialize results array
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
    
            // Load term report
            $termReport = TermReport::where('student_record_id', $this->selectedStudent)
                ->where('academic_year_id', $this->academicYearId)
                ->where('semester_id', $this->semesterId)
                ->first();
    
            if ($termReport) {
                $this->presentDays = $termReport->present_days;
                $this->absentDays = $termReport->absent_days;
                $this->overallTeacherComment = $termReport->class_teacher_comment;
                $this->principalComment = $termReport->principal_comment;
                
                // Decode JSON properly
                $this->psychomotorScores = is_string($termReport->psychomotor_traits) 
                    ? json_decode($termReport->psychomotor_traits, true) 
                    : ($termReport->psychomotor_traits ?? $this->getDefaultPsychomotorScores());
                    
                $this->affectiveScores = is_string($termReport->affective_traits)
                    ? json_decode($termReport->affective_traits, true)
                    : ($termReport->affective_traits ?? $this->getDefaultAffectiveScores());
                    
                $this->coCurricularScores = is_string($termReport->co_curricular_activities)
                    ? json_decode($termReport->co_curricular_activities, true)
                    : ($termReport->co_curricular_activities ?? $this->getDefaultCoCurricularScores());
            } else {
                $this->initializeScores();
            }
    
            if ($this->subjects->isEmpty()) {
                $this->dispatch('error', 'Student loaded, but no assigned subjects were found. Assign subjects to this student/class first.');
            } else {
                $this->dispatch('success', 'Student loaded successfully');
            }
    
        } catch (\Exception $e) {
            \Log::error('Load student error: ' . $e->getMessage(), [
                'student_id' => $this->selectedStudent,
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('error', 'Failed to load student: ' . $e->getMessage());
            $this->studentRecord = null;
            $this->loadedClassId = null;
        }
    }
    public function updatedResults($value, $key)
    {
        // Parse the key to get subject ID and field
        $parts = explode('.', $key);
        if (count($parts) !== 2) return;
        
        [$subjectId, $field] = $parts;
        
        // Only auto-save score fields, not comments
        if (!in_array($field, ['ca1_score', 'ca2_score', 'ca3_score', 'ca4_score', 'exam_score'])) {
            return;
        }

        try {
            // Validate the score
            if (in_array($field, ['ca1_score', 'ca2_score', 'ca3_score', 'ca4_score'])) {
                if ($value !== null && $value !== '' && ($value < 0 || $value > 10)) {
                    $this->dispatch('error', 'CA scores must be between 0 and 10');
                    return;
                }
            } elseif ($field === 'exam_score') {
                if ($value !== null && $value !== '' && ($value < 0 || $value > 60)) {
                    $this->dispatch('error', 'Exam score must be between 0 and 60');
                    return;
                }
            }

            // Auto-save the score
            $this->saveIndividualScore($subjectId, $field, $value);

        } catch (\Exception $e) {
            $this->dispatch('error', 'Validation error: ' . $e->getMessage());
        }
    }

    protected function saveIndividualScore($subjectId, $field, $value)
    {
        if (!$this->studentRecord) return;
    
        try {
            DB::beginTransaction();

            $classId = (int) ($this->loadedClassId ?: $this->selectedClass ?: $this->studentRecord->my_class_id);
            $subjectId = (int) $subjectId;

            if (
                !$this->currentUserCanUploadResultStudent(
                    $this->studentRecord->id,
                    $classId,
                    $this->academicYearId,
                    $this->selectedSection
                ) ||
                !$this->currentUserCanUploadResultSubject($subjectId, $classId, $this->academicYearId)
            ) {
                DB::rollBack();
                \Log::warning('Blocked unauthorized individual result upload attempt', [
                    'user_id' => auth()->id(),
                    'student_id' => $this->studentRecord->id,
                    'subject_id' => $subjectId,
                    'class_id' => $classId,
                ]);
                return;
            }
    
            // ✅ VALIDATION: Check if student is enrolled in subject
            $isEnrolled = $this->studentRecord->studentSubjects()
                ->where('subject_id', $subjectId)
                ->exists();
    
            if (!$isEnrolled) {
                DB::rollBack();
                $subject = Subject::query()->find($subjectId);
                \Log::warning('Attempted to save result for non-enrolled subject', [
                    'student_id' => $this->studentRecord->id,
                    'subject_id' => $subjectId,
                    'subject_name' => $subject?->name
                ]);
                return;
            }
    
            // Get all current scores for this subject
            $data = $this->results[$subjectId] ?? [];
            
            // Calculate total
            $ca1 = (int) ($data['ca1_score'] ?? 0);
            $ca2 = (int) ($data['ca2_score'] ?? 0);
            $ca3 = (int) ($data['ca3_score'] ?? 0);
            $ca4 = (int) ($data['ca4_score'] ?? 0);
            $exam = (int) ($data['exam_score'] ?? 0);
            $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
    
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
                    'teacher_comment' => $data['comment'] ?? null,
                    'approved' => false,
                ]
            );
    
            DB::commit();
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Auto-save individual score error: ' . $e->getMessage(), [
                'student_id' => $this->studentRecord->id,
                'subject_id' => $subjectId,
                'field' => $field,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    public function saveAll()
    {
        if (!$this->studentRecord) {
            $this->dispatch('error', 'No student loaded');
            return;
        }

        $classId = (int) ($this->loadedClassId ?: $this->selectedClass ?: $this->studentRecord->my_class_id);
        $canManageClassTeacherReport = $this->currentUserCanManageResultClassTeacherReport(
            $this->loadedClassId ?: $classId
        );
        $canEditPrincipalComment = $this->currentUserCanEditPrincipalResultComment();

        if (
            !$this->currentUserCanUploadResultStudent(
                $this->studentRecord->id,
                $classId,
                $this->academicYearId,
                $this->selectedSection
            )
        ) {
            $this->dispatch('error', 'You can only upload results for students in your assigned classes.');
            return;
        }
    
        try {
            DB::beginTransaction();
    
            $savedSubjects = 0;
            $errors = [];
            $allowedSubjectIds = $this->accessibleResultUploadSubjectsQuery($classId, $this->academicYearId)
                ->pluck('subjects.id')
                ->map(fn ($id) => (int) $id)
                ->all();
            
            // ✅ PRE-VALIDATE: Get all subjects student is enrolled in
            $enrolledSubjectIds = $this->studentRecord->studentSubjects()
                ->pluck('subject_id')
                ->toArray();
            
            // Save all results
            foreach ($this->subjects as $subject) {
                if (!in_array((int) $subject->id, $allowedSubjectIds, true)) {
                    $errors[] = $subject->name . ' - You are not assigned to upload this subject for the selected class';
                    continue;
                }

                // ✅ SKIP if student not enrolled in this subject
                if (!in_array($subject->id, $enrolledSubjectIds)) {
                    $errors[] = $subject->name . ' - Student not enrolled';
                    continue;
                }
                
                $data = $this->results[$subject->id] ?? [];
                
                $ca1 = (int) ($data['ca1_score'] ?? 0);
                $ca2 = (int) ($data['ca2_score'] ?? 0);
                $ca3 = (int) ($data['ca3_score'] ?? 0);
                $ca4 = (int) ($data['ca4_score'] ?? 0);
                $exam = (int) ($data['exam_score'] ?? 0);
                $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
    
                // Only save if there's actual data
                if ($total > 0 || !empty($data['comment'])) {
                    try {
                        Result::updateOrCreate(
                            [
                                'student_record_id' => $this->studentRecord->id,
                                'subject_id' => $subject->id,
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
                                'teacher_comment' => $data['comment'] ?? null,
                                'approved' => false,
                            ]
                        );
                        $savedSubjects++;
                    } catch (\Exception $e) {
                        $errors[] = $subject->name . ' - ' . $e->getMessage();
                    }
                }
            }
    
            $termReportPayload = [];

            if ($canManageClassTeacherReport) {
                $termReportPayload = array_merge($termReportPayload, [
                    'present_days' => $this->presentDays,
                    'absent_days' => $this->absentDays,
                    'class_teacher_comment' => $this->overallTeacherComment,
                    'psychomotor_traits' => json_encode($this->psychomotorScores),
                    'affective_traits' => json_encode($this->affectiveScores),
                    'co_curricular_activities' => json_encode($this->coCurricularScores),
                ]);
            }

            if ($canEditPrincipalComment) {
                $termReportPayload['principal_comment'] = $this->principalComment;
            }

            if ($termReportPayload !== []) {
                TermReport::updateOrCreate(
                    [
                        'student_record_id' => $this->studentRecord->id,
                        'academic_year_id' => $this->academicYearId,
                        'semester_id' => $this->semesterId,
                    ],
                    $termReportPayload
                );
            }
    
            DB::commit();
            
            // Prepare feedback message
            $studentName = $this->studentRecord->user->name;
            
            if (empty($errors)) {
                session()->flash('success', "Results saved successfully for {$studentName}! ({$savedSubjects} subjects)");
            } else {
                $errorCount = count($errors);
                session()->flash('warning', "Partially saved for {$studentName}. {$savedSubjects} subjects saved, but {$errorCount} error(s) occurred.");
                session()->flash('error_details', array_slice($errors, 0, 5));
            }

            $this->dispatch('result-upload-saved');
            
            return redirect()->route('result');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Save all error: ' . $e->getMessage(), [
                'student_id' => $this->studentRecord->id,
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('error', 'Failed to save: ' . $e->getMessage());
        }
    }
    protected function initializeScores()
    {
        $this->psychomotorScores = $this->getDefaultPsychomotorScores();
        $this->affectiveScores = $this->getDefaultAffectiveScores();
        $this->coCurricularScores = $this->getDefaultCoCurricularScores();
    }

    protected function getDefaultPsychomotorScores()
    {
        return ['Handwriting' => null, 'Verbal Fluency' => null, 'Game/Sports' => null, 'Handling Tools' => null];
    }

    protected function getDefaultAffectiveScores()
    {
        return ['Punctuality' => null, 'Neatness' => null, 'Politeness' => null, 'Leadership' => null];
    }

    protected function getDefaultCoCurricularScores()
    {
        return ['Athletics' => null, 'Football' => null, 'Volley Ball' => null, 'Table Tennis' => null];
    }

    protected function classBelongsToCurrentSchool($classId): bool
    {
        if (!$classId) {
            return false;
        }

        return MyClass::where('id', $classId)
            ->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->exists();
    }

    protected function sectionBelongsToCurrentSchoolClass($sectionId, $classId): bool
    {
        if (!$sectionId || !$classId) {
            return false;
        }

        return Section::where('id', $sectionId)
            ->where('my_class_id', $classId)
            ->whereHas('myClass.classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->exists();
    }

    protected function ensureSubjectAssignmentsForLoadedStudent($subjectIds, int $classId, $sectionId = null): void
    {
        foreach ($subjectIds as $subjectId) {
            DB::table('student_subject')->updateOrInsert(
                [
                    'student_record_id' => $this->selectedStudent,
                    'subject_id' => $subjectId,
                ],
                [
                    'my_class_id' => $classId,
                    'section_id' => $sectionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    protected function resolveSelectedClassStudentRecordIds()
    {
        $studentIds = collect();

        if ($this->academicYearId) {
            $studentIds = DB::table('academic_year_student_record')
                ->where('academic_year_id', $this->academicYearId)
                ->where('my_class_id', $this->selectedClass)
                ->when($this->selectedSection, fn ($q) => $q->where('section_id', $this->selectedSection))
                ->pluck('student_record_id')
                ->map(fn ($id) => (int) $id);
        }

        $fallbackStudentIds = StudentRecord::where('my_class_id', $this->selectedClass)
            ->whereHas('user', function ($query) {
                $query->where('school_id', auth()->user()->school_id)
                    ->whereNull('deleted_at');
            })
            ->when($this->selectedSection, fn ($q) => $q->where('section_id', $this->selectedSection))
            ->withActiveUser()
            ->pluck('student_records.id')
            ->map(fn ($id) => (int) $id);

        $missingAcademicYearRecordIds = $fallbackStudentIds->diff($studentIds)->values();

        if ($missingAcademicYearRecordIds->isNotEmpty()) {
            $this->syncMissingAcademicYearRecords($missingAcademicYearRecordIds);
        }

        return $studentIds
            ->merge($fallbackStudentIds)
            ->unique()
            ->values();
    }

    protected function syncMissingAcademicYearRecords($studentRecordIds): void
    {
        if (!$this->academicYearId || !$this->selectedClass || $studentRecordIds->isEmpty()) {
            return;
        }

        $sectionByStudent = StudentRecord::whereIn('id', $studentRecordIds)
            ->whereHas('user', function ($query) {
                $query->where('school_id', auth()->user()->school_id)
                    ->whereNull('deleted_at');
            })
            ->pluck('section_id', 'id');

        foreach ($studentRecordIds as $studentId) {
            DB::table('academic_year_student_record')->updateOrInsert(
                [
                    'student_record_id' => $studentId,
                    'academic_year_id' => $this->academicYearId,
                ],
                [
                    'my_class_id' => $this->selectedClass,
                    'section_id' => $this->selectedSection ?: ($sectionByStudent[$studentId] ?? null),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function render()
    {
        $isRestrictedTeacherResultUploader = $this->isRestrictedTeacherResultUploader();
        $activeClassId = (int) ($this->loadedClassId ?: $this->selectedClass ?: $this->studentRecord?->my_class_id ?: 0);
        $canManageClassTeacherReport = $this->currentUserCanManageResultClassTeacherReport($activeClassId);
        $canEditPrincipalComment = $this->currentUserCanEditPrincipalResultComment();
        $classes = $this->accessibleResultUploadClassesQuery($this->academicYearId)
            ->orderBy('name')
            ->get();

        $sections = collect();

        if ($this->selectedClass && $this->currentUserCanUploadResultClass($this->selectedClass, $this->academicYearId)) {
            $sections = Section::when($this->selectedClass, function ($q) {
                $q->where('my_class_id', $this->selectedClass)
                    ->whereHas('myClass.classGroup', function ($query) {
                        $query->where('school_id', auth()->user()->school_id);
                    });
            })->get();
        }

        $students = collect();

        if ($this->selectedClass && $this->currentUserCanUploadResultClass($this->selectedClass, $this->academicYearId)) {
            $studentIds = $this->resolveSelectedClassStudentRecordIds();

            if ($studentIds->isNotEmpty()) {
                $students = StudentRecord::whereIn('student_records.id', $studentIds)
                    ->whereHas('user', function ($query) {
                        $query->where('school_id', auth()->user()->school_id)
                            ->whereNull('deleted_at');
                    })
                    ->withActiveUser()
                    ->orderByName()
                    ->get();
            }
        }
    
        return view('livewire.result.upload.individual-upload', compact(
            'classes',
            'sections',
            'students',
            'isRestrictedTeacherResultUploader',
            'canManageClassTeacherReport',
            'canEditPrincipalComment',
        ))
            ->layout('layouts.result', [
                'title' => 'Individual Result Upload',
                'page_heading' => 'Individual Result Upload'
            ]);
    }
}
