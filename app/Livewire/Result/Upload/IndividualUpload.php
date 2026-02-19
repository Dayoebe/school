<?php

namespace App\Livewire\Result\Upload;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{AcademicYear, StudentRecord, Subject, Result, TermReport, MyClass, Section, Semester};
use Illuminate\Support\Facades\DB;

class IndividualUpload extends Component
{
    public $academicYearId;
    public $semesterId;
    public $selectedClass;
    public $selectedSection;
    public $selectedStudent;
    
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
        $schoolId = auth()->user()->school_id;

        // First check session, then fall back to school's current academic year/semester
        $this->academicYearId = session('result_academic_year_id') 
            ?? auth()->user()->school?->academic_year_id
            ?? AcademicYear::query()
                ->orderBy('start_year', 'desc')
                ->value('id');
        
        // Get current semester for the academic year
        if (!session('result_semester_id') && $this->academicYearId) {
            $currentSemester = Semester::where('academic_year_id', $this->academicYearId)
                ->orderBy('name')
                ->first();
            $this->semesterId = $currentSemester?->id;
        } else {
            $this->semesterId = session('result_semester_id');
        }
        
        $this->initializeScores();
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];
        $this->reset(['selectedClass', 'selectedSection', 'selectedStudent', 'studentRecord']);
    }

    public function updatedSelectedClass()
    {
        $this->reset(['selectedStudent', 'studentRecord', 'selectedSection']);
    }

    public function updatedSelectedSection()
    {
        $this->reset(['selectedStudent', 'studentRecord']);
    }

    public function loadStudent()
    {
        if (!$this->selectedStudent) {
            $this->dispatch('error', 'Please select a student');
            return;
        }

        if (!$this->academicYearId) {
            $this->academicYearId = session('result_academic_year_id')
                ?? auth()->user()->school?->academic_year_id
                ?? AcademicYear::query()
                    ->orderBy('start_year', 'desc')
                    ->value('id');
        }

        if (!$this->academicYearId) {
            $this->dispatch('error', 'Please select an academic year');
            return;
        }

        // Ensure a semester is available for the selected academic year
        if (!$this->semesterId && $this->academicYearId) {
            $this->semesterId = Semester::where('academic_year_id', $this->academicYearId)
                ->where('school_id', auth()->user()->school_id)
                ->orderBy('name')
                ->value('id');

            if ($this->semesterId) {
                session(['result_semester_id' => $this->semesterId]);
            }
        }

        if (!$this->semesterId) {
            $this->dispatch('error', 'No term found for this academic year. Please create/select a term first.');
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

            // Start with subjects directly assigned to this student.
            $studentSubjectIds = $this->studentRecord->studentSubjects()
                ->select('subjects.id')
                ->where('subjects.school_id', auth()->user()->school_id)
                ->pluck('subjects.id');

            // Prefer subjects tied to the selected/derived class.
            $this->subjects = Subject::whereIn('subjects.id', $studentSubjectIds)
                ->where('subjects.school_id', auth()->user()->school_id)
                ->where(function ($query) use ($classId) {
                    $query->where('subjects.my_class_id', $classId)
                        ->orWhereHas('classes', function ($classQuery) use ($classId) {
                            $classQuery->where('my_classes.id', $classId);
                        })
                        ->orWhereExists(function ($subQuery) use ($classId) {
                            $subQuery->select(DB::raw(1))
                                ->from('student_subject as ss')
                                ->whereColumn('ss.subject_id', 'subjects.id')
                                ->where('ss.student_record_id', $this->selectedStudent)
                                ->where('ss.my_class_id', $classId);
                        });
                })
                ->orderBy('subjects.name')
                ->distinct()
                ->get();

            // Fallback: if class mappings are incomplete, still use student's assigned subjects.
            if ($this->subjects->isEmpty() && $studentSubjectIds->isNotEmpty()) {
                $this->subjects = Subject::whereIn('subjects.id', $studentSubjectIds)
                    ->where('subjects.school_id', auth()->user()->school_id)
                    ->orderBy('subjects.name')
                    ->distinct()
                    ->get();
            }
    
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
    
        try {
            DB::beginTransaction();
    
            $savedSubjects = 0;
            $errors = [];
            
            // ✅ PRE-VALIDATE: Get all subjects student is enrolled in
            $enrolledSubjectIds = $this->studentRecord->studentSubjects()
                ->pluck('subject_id')
                ->toArray();
            
            // Save all results
            foreach ($this->subjects as $subject) {
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
    
            // Save term report
            TermReport::updateOrCreate(
                [
                    'student_record_id' => $this->studentRecord->id,
                    'academic_year_id' => $this->academicYearId,
                    'semester_id' => $this->semesterId,
                ],
                [
                    'present_days' => $this->presentDays,
                    'absent_days' => $this->absentDays,
                    'class_teacher_comment' => $this->overallTeacherComment,
                    'principal_comment' => $this->principalComment,
                    'psychomotor_traits' => json_encode($this->psychomotorScores),
                    'affective_traits' => json_encode($this->affectiveScores),
                    'co_curricular_activities' => json_encode($this->coCurricularScores),
                ]
            );
    
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

    public function render()
    {
        $classesQuery = MyClass::query();

        if (auth()->user()->school_id) {
            $classesQuery->whereHas('classGroup', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            });
        }

        $classes = $classesQuery->orderBy('name')->get();

        $sections = Section::when($this->selectedClass, function($q) {
            $q->where('my_class_id', $this->selectedClass)
                ->whereHas('myClass.classGroup', function ($query) {
                    $query->where('school_id', auth()->user()->school_id);
                });
        })->get();

        $students = collect();

        if ($this->selectedClass) {
            // Prefer academic-year specific class/section, fallback to current student_records assignment.
            if ($this->academicYearId) {
                $query = DB::table('academic_year_student_record')
                    ->where('academic_year_id', $this->academicYearId)
                    ->where('my_class_id', $this->selectedClass);

                if ($this->selectedSection) {
                    $query->where('section_id', $this->selectedSection);
                }

                $studentIds = $query->pluck('student_record_id');

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

            if ($students->isEmpty()) {
                $students = StudentRecord::where('my_class_id', $this->selectedClass)
                    ->whereHas('user', function ($query) {
                        $query->where('school_id', auth()->user()->school_id)
                            ->whereNull('deleted_at');
                    })
                    ->when($this->selectedSection, fn ($q) => $q->where('section_id', $this->selectedSection))
                    ->withActiveUser()
                    ->orderByName()
                    ->get();
            }
        }
    
        return view('livewire.result.upload.individual-upload', compact('classes', 'sections', 'students'))
            ->layout('layouts.result', [
                'title' => 'Individual Result Upload',
                'page_heading' => 'Individual Result Upload'
            ]);
    }
}
