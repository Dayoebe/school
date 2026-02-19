<?php

namespace App\Livewire\Result\Upload;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{AcademicYear, StudentRecord, Subject, Result, MyClass, Section, Semester};
use Illuminate\Support\Facades\DB;

class BulkUpload extends Component
{
    public $academicYearId;
    public $semesterId;
    public $selectedClass;
    public $selectedSection;
    public $selectedSubject;

    public $subjects = [];
    public $students;
    public $bulkResults = [];
    public $isSaving = false;
    public $hasAnyError = false; // ADD THIS

    public function mount()
    {
        $schoolId = auth()->user()->school_id;

        $this->academicYearId = session('result_academic_year_id')
            ?? auth()->user()->school?->academic_year_id
            ?? AcademicYear::query()
                ->orderBy('start_year', 'desc')
                ->value('id');
        $this->semesterId = session('result_semester_id');

        if (!$this->semesterId && $this->academicYearId) {
            $this->semesterId = Semester::where('academic_year_id', $this->academicYearId)
                ->orderBy('name')
                ->value('id');
        }

        $this->students = collect();
    }

    #[On('academic-period-changed')]
    public function handlePeriodChange($data)
    {
        $this->academicYearId = $data['academicYearId'];
        $this->semesterId = $data['semesterId'];
        $this->reset(['selectedClass', 'selectedSection', 'selectedSubject']);
        $this->students = collect();
        $this->bulkResults = [];
        $this->hasAnyError = false; // ADD THIS
    }

    public function updatedSelectedClass()
    {
        $this->subjects = collect();

        if ($this->selectedClass) {
            if (!$this->classBelongsToCurrentSchool($this->selectedClass)) {
                $this->selectedClass = null;
                $this->dispatch('error', 'Selected class is not in your current school.');
            } else {
            $this->subjects = Subject::where(function ($query) {
                    $query->where('my_class_id', $this->selectedClass)
                        ->orWhereHas('classes', function ($classQuery) {
                            $classQuery->where('my_classes.id', $this->selectedClass);
                        })
                        ->orWhereIn('subjects.id', function ($subQuery) {
                            $subQuery->from('student_subject')
                                ->where('my_class_id', $this->selectedClass)
                                ->select('subject_id');
                        })
                        ->orWhereIn('subjects.id', function ($subQuery) {
                            $subQuery->from('student_subject as ss')
                                ->join('academic_year_student_record as aysr', 'aysr.student_record_id', '=', 'ss.student_record_id')
                                ->where('aysr.my_class_id', $this->selectedClass)
                                ->when($this->academicYearId, function ($q) {
                                    $q->where('aysr.academic_year_id', $this->academicYearId);
                                })
                                ->select('ss.subject_id');
                        });
                })
                ->where('subjects.school_id', auth()->user()->school_id)
                ->orderBy('name')
                ->distinct()
                ->get();
            }
        }

        $this->reset(['selectedSubject']);
        $this->students = collect();
        $this->bulkResults = [];
        $this->hasAnyError = false; // ADD THIS
    }

    public function loadStudents()
    {
        if (!$this->selectedClass || !$this->selectedSubject) {
            $this->dispatch('error', 'Please select both class and subject');
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
            $this->dispatch('error', 'Please select an academic year first');
            return;
        }

        if (!$this->semesterId) {
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

        if (!$this->classBelongsToCurrentSchool($this->selectedClass)) {
            $this->dispatch('error', 'Selected class is not in your current school.');
            return;
        }

        if ($this->selectedSection && !$this->sectionBelongsToCurrentSchoolClass($this->selectedSection, $this->selectedClass)) {
            $this->dispatch('error', 'Selected section is not valid for your current school/class.');
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

        // Validate subject belongs to class
        $subject = Subject::query()->find($this->selectedSubject);
        $subjectFromStudentClassPivot = DB::table('student_subject')
            ->where('subject_id', $this->selectedSubject)
            ->where('my_class_id', $this->selectedClass)
            ->exists();

        $subjectFromStudentYearClassPivot = DB::table('student_subject as ss')
            ->join('academic_year_student_record as aysr', 'aysr.student_record_id', '=', 'ss.student_record_id')
            ->where('ss.subject_id', $this->selectedSubject)
            ->where('aysr.my_class_id', $this->selectedClass)
            ->when($this->academicYearId, function ($q) {
                $q->where('aysr.academic_year_id', $this->academicYearId);
            })
            ->exists();

        $subjectBelongsToClass = $subject && (
            (int) $subject->my_class_id === (int) $this->selectedClass
            || $subject->classes()->where('my_classes.id', $this->selectedClass)->exists()
            || $subjectFromStudentClassPivot
            || $subjectFromStudentYearClassPivot
        );

        if (!$subjectBelongsToClass) {
            $this->dispatch('error', 'Invalid subject for selected class');
            $this->students = collect();
            return;
        }

        // Get students for this academic year
        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('academic_year_id', $this->academicYearId)
            ->where('my_class_id', $this->selectedClass)
            ->when($this->selectedSection, fn($q) => $q->where('section_id', $this->selectedSection))
            ->pluck('student_record_id');

        // Fallback for students not yet promoted/mapped in academic_year_student_record.
        if ($studentRecordIds->isEmpty()) {
            $studentRecordIds = StudentRecord::where('my_class_id', $this->selectedClass)
                ->whereHas('user', function ($query) {
                    $query->where('school_id', auth()->user()->school_id)
                        ->whereNull('deleted_at');
                })
                ->when($this->selectedSection, fn($q) => $q->where('section_id', $this->selectedSection))
                ->withActiveUser()
                ->pluck('student_records.id');

            if ($studentRecordIds->isNotEmpty()) {
                $this->syncAcademicYearRecords($studentRecordIds);
            }
        }

        if ($studentRecordIds->isEmpty()) {
            $this->dispatch('error', 'No students found for this class in current academic year');
            $this->students = collect();
            return;
        }

        // Load students enrolled in this subject with eager loading to prevent N+1
        $this->students = StudentRecord::whereIn('student_records.id', $studentRecordIds)
            ->whereHas('studentSubjects', fn($q) => $q->where('subject_id', $this->selectedSubject))
            ->whereHas('user', fn($q) => $q->where('school_id', auth()->user()->school_id)->whereNull('deleted_at'))
            ->with([
                'user' => function($query) {
                    $query->where('school_id', auth()->user()->school_id)
                        ->whereNull('deleted_at');
                }, 
                'myClass', 
                'section',
                'studentSubjects' => function($query) {
                    $query->where('subject_id', $this->selectedSubject);
                }
            ])
            ->orderByName()
            ->get();

        if ($this->students->isEmpty()) {
            $this->dispatch('error', 'No students enrolled in this subject');
            return;
        }

        // Load existing results
        $existingResults = Result::where('subject_id', $this->selectedSubject)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->whereIn('student_record_id', $this->students->pluck('id'))
            ->get()
            ->keyBy('student_record_id');

        // Initialize bulk results
        $this->bulkResults = [];
        foreach ($this->students as $student) {
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

        // Check for any existing errors
        $this->checkForErrors();

        $this->dispatch('success', "Loaded {$this->students->count()} students for bulk upload");
    }

    // ADD THIS METHOD TO CHECK FOR ERRORS
    public function checkForErrors()
    {
        $this->hasAnyError = false;
        
        foreach ($this->bulkResults as $studentId => $data) {
            $ca1 = (int) ($data['ca1_score'] ?? 0);
            $ca2 = (int) ($data['ca2_score'] ?? 0);
            $ca3 = (int) ($data['ca3_score'] ?? 0);
            $ca4 = (int) ($data['ca4_score'] ?? 0);
            $exam = (int) ($data['exam_score'] ?? 0);
            
            if ($ca1 > 10 || $ca2 > 10 || $ca3 > 10 || $ca4 > 10 || $exam > 60) {
                $this->hasAnyError = true;
                return; // Found an error, no need to continue
            }
        }
    }

    public function updatedBulkResults($value, $key)
    {
        // Split the key into parts safely
        $parts = explode('.', $key);
        
        // In updatedBulkResults, key format is "studentId.field"
        if (count($parts) !== 2) {
            return;
        }
        
        [$studentId, $field] = $parts;
    
        // Validate individual field
        if (in_array($field, ['ca1_score', 'ca2_score', 'ca3_score', 'ca4_score'])) {
            $this->validate(["bulkResults.$studentId.$field" => 'nullable|numeric|min:0|max:10']);
        } elseif ($field === 'exam_score') {
            $this->validate(["bulkResults.$studentId.$field" => 'nullable|numeric|min:0|max:60']);
        }
    
        // Auto-save individual field
        $this->saveIndividualField($studentId, $field, $value);
        
        // Check for errors after updating
        $this->checkForErrors(); // ADD THIS
    }

    protected function saveIndividualField($studentId, $field, $value)
    {
        try {
            DB::beginTransaction();
    
            // Validate student is enrolled in the subject
            $student = $this->getStudentRecordForCurrentSchool($studentId);
            if (!$student) {
                DB::rollBack();
                return;
            }
    
            $isEnrolled = $student->studentSubjects()
                ->where('subject_id', $this->selectedSubject)
                ->exists();
    
            if (!$isEnrolled) {
                DB::rollBack();
                \Log::warning("Attempted to save result for student {$studentId} not enrolled in subject {$this->selectedSubject}");
                return;
            }
    
            // Validate student has academic year record
            if (!$this->ensureAcademicYearRecord($studentId, $student)) {
                DB::rollBack();
                return;
            }
    
            // Calculate total
            $data = $this->bulkResults[$studentId];
            $ca1 = (int) ($data['ca1_score'] ?? 0);
            $ca2 = (int) ($data['ca2_score'] ?? 0);
            $ca3 = (int) ($data['ca3_score'] ?? 0);
            $ca4 = (int) ($data['ca4_score'] ?? 0);
            $exam = (int) ($data['exam_score'] ?? 0);
            $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
    
            Result::updateOrCreate(
                [
                    'student_record_id' => $studentId,
                    'subject_id' => $this->selectedSubject,
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
            \Log::error('Bulk field save error: ' . $e->getMessage());
        }
    }
    public function saveAll()
    {
        $this->isSaving = true;
    
        try {
            DB::beginTransaction();
    
            $savedCount = 0;
            $errors = [];
    
            // Validate all students are still enrolled in this subject
            $enrolledStudentIds = StudentRecord::whereIn('id', array_keys($this->bulkResults))
                ->whereHas('user', fn($q) => $q->where('school_id', auth()->user()->school_id)->whereNull('deleted_at'))
                ->whereHas('studentSubjects', fn($q) => $q->where('subject_id', $this->selectedSubject))
                ->pluck('id')
                ->toArray();
    
            foreach ($this->bulkResults as $studentId => $data) {
                try {
                    // Check if student is enrolled in the subject
                    if (!in_array($studentId, $enrolledStudentIds)) {
                        $student = $this->getStudentRecordForCurrentSchool($studentId);
                        $subjectName = Subject::query()
                            ->find($this->selectedSubject)?->name ?? 'this subject';
                        $errors[] = ($student?->user?->name ?? "Student {$studentId}") . " - Not enrolled in {$subjectName}";
                        continue;
                    }
    
                    // Validate student record exists for this academic year
                    if (!$this->ensureAcademicYearRecord($studentId)) {
                        $student = $this->getStudentRecordForCurrentSchool($studentId);
                        $errors[] = ($student?->user?->name ?? "Student {$studentId}") . ' - No academic year record';
                        continue;
                    }
    
                    // Calculate total
                    $ca1 = (int) ($data['ca1_score'] ?? 0);
                    $ca2 = (int) ($data['ca2_score'] ?? 0);
                    $ca3 = (int) ($data['ca3_score'] ?? 0);
                    $ca4 = (int) ($data['ca4_score'] ?? 0);
                    $exam = (int) ($data['exam_score'] ?? 0);
                    $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;
    
                    // Only save if there's actual data
                    if ($total > 0 || !empty($data['comment'])) {
                        Result::updateOrCreate(
                            [
                                'student_record_id' => $studentId,
                                'subject_id' => $this->selectedSubject,
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
                        $savedCount++;
                    }
                } catch (\Exception $e) {
                    $student = $this->getStudentRecordForCurrentSchool($studentId);
                    $errors[] = ($student?->user?->name ?? "Student {$studentId}") . ' - ' . $e->getMessage();
                }
            }
    
            DB::commit();
            
            if (empty($errors)) {
                $subjectName = Subject::query()
                    ->find($this->selectedSubject)?->name ?? 'Subject';
                session()->flash('success', "Bulk upload successful! Results saved for {$savedCount} student(s) in {$subjectName}");
                
                return redirect()->route('result');
            } else {
                $errorCount = count($errors);
                $subjectName = Subject::query()
                    ->find($this->selectedSubject)?->name ?? 'Subject';
                
                session()->flash('warning', "Partially saved. Results saved for {$savedCount} student(s) in {$subjectName}, but {$errorCount} error(s) occurred.");
                session()->flash('error_details', array_slice($errors, 0, 5)); // Store first 5 errors
                
                return redirect()->route('result');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save: ' . $e->getMessage());
            return redirect()->route('result');
        } finally {
            $this->isSaving = false;
        }
    }
    public function deleteResult($studentId)
    {
        try {
            $student = $this->getStudentRecordForCurrentSchool($studentId);
            if (!$student) {
                $this->dispatch('error', 'Student not found in your current school.');
                return;
            }

            Result::where('student_record_id', $studentId)
                ->where('subject_id', $this->selectedSubject)
                ->where('academic_year_id', $this->academicYearId)
                ->where('semester_id', $this->semesterId)
                ->delete();

            $this->bulkResults[$studentId] = [
                'ca1_score' => null,
                'ca2_score' => null,
                'ca3_score' => null,
                'ca4_score' => null,
                'exam_score' => null,
                'comment' => '',
            ];

            // Check for errors after deletion
            $this->checkForErrors(); // ADD THIS

            $this->dispatch('success', 'Result deleted successfully');
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete: ' . $e->getMessage());
        }
    }

    protected function ensureAcademicYearRecord($studentId, ?StudentRecord $student = null): bool
    {
        if (!$this->academicYearId || !$this->selectedClass) {
            return false;
        }

        if (!$this->classBelongsToCurrentSchool($this->selectedClass)) {
            return false;
        }

        if ($this->selectedSection && !$this->sectionBelongsToCurrentSchoolClass($this->selectedSection, $this->selectedClass)) {
            return false;
        }

        $exists = DB::table('academic_year_student_record')
            ->where('student_record_id', $studentId)
            ->where('academic_year_id', $this->academicYearId)
            ->exists();

        if ($exists) {
            return true;
        }

        $student ??= $this->getStudentRecordForCurrentSchool($studentId);
        if (!$student) {
            return false;
        }

        DB::table('academic_year_student_record')->updateOrInsert(
            [
                'student_record_id' => $studentId,
                'academic_year_id' => $this->academicYearId,
            ],
            [
                'my_class_id' => $this->selectedClass,
                'section_id' => $this->selectedSection ?: $student->section_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return true;
    }

    protected function syncAcademicYearRecords($studentRecordIds): void
    {
        if (!$this->academicYearId || !$this->selectedClass) {
            return;
        }

        if (!$this->classBelongsToCurrentSchool($this->selectedClass)) {
            return;
        }

        $sectionByStudent = StudentRecord::whereIn('id', $studentRecordIds)
            ->whereHas('user', fn($q) => $q->where('school_id', auth()->user()->school_id)->whereNull('deleted_at'))
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

    protected function getStudentRecordForCurrentSchool($studentId): ?StudentRecord
    {
        return StudentRecord::whereHas('user', function ($query) {
            $query->where('school_id', auth()->user()->school_id)
                ->whereNull('deleted_at');
        })->find($studentId);
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

        $sections = Section::when($this->selectedClass, function ($q) {
            $q->where('my_class_id', $this->selectedClass)
                ->whereHas('myClass.classGroup', function ($query) {
                    $query->where('school_id', auth()->user()->school_id);
                });
        })->get();
    
        return view('livewire.result.upload.bulk-upload', compact('classes', 'sections'))
            ->layout('layouts.result', [
                'title' => 'Bulk Result Upload',
                'page_heading' => 'Bulk Result Upload'
            ]);
    }
}
