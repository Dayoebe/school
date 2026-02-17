<?php

namespace App\Livewire\Result\Upload;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{StudentRecord, Subject, Result, MyClass, Section};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

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
        $this->academicYearId = session('result_academic_year_id');
        $this->semesterId = session('result_semester_id');
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
        $this->subjects = Subject::where('my_class_id', $this->selectedClass)
            ->orderBy('name')
            ->get();
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

        // Validate subject belongs to class
        $subject = Subject::find($this->selectedSubject);
        if (!$subject || $subject->my_class_id != $this->selectedClass) {
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

        if ($studentRecordIds->isEmpty()) {
            $this->dispatch('error', 'No students found for this class in current academic year');
            $this->students = collect();
            return;
        }

        // Load students enrolled in this subject with eager loading to prevent N+1
        $this->students = StudentRecord::whereIn('student_records.id', $studentRecordIds)
            ->whereHas('studentSubjects', fn($q) => $q->where('subject_id', $this->selectedSubject))
            ->whereHas('user', fn($q) => $q->whereNull('deleted_at'))
            ->with([
                'user' => function($query) {
                    $query->whereNull('deleted_at');
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
        
        // Ensure we have exactly 3 parts: bulkResults, studentId, field
        if (count($parts) !== 3) {
            return;
        }
        
        [$prefix, $studentId, $field] = $parts;
    
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
            $student = StudentRecord::find($studentId);
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
            $yearRecord = DB::table('academic_year_student_record')
                ->where('student_record_id', $studentId)
                ->where('academic_year_id', $this->academicYearId)
                ->where('my_class_id', $this->selectedClass)
                ->first();
    
            if (!$yearRecord) {
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
                ->whereHas('studentSubjects', fn($q) => $q->where('subject_id', $this->selectedSubject))
                ->pluck('id')
                ->toArray();
    
            foreach ($this->bulkResults as $studentId => $data) {
                try {
                    // Check if student is enrolled in the subject
                    if (!in_array($studentId, $enrolledStudentIds)) {
                        $student = StudentRecord::find($studentId);
                        $subjectName = Subject::find($this->selectedSubject)?->name ?? 'this subject';
                        $errors[] = $student->user->name . " - Not enrolled in {$subjectName}";
                        continue;
                    }
    
                    // Validate student record exists for this academic year
                    $yearRecord = DB::table('academic_year_student_record')
                        ->where('student_record_id', $studentId)
                        ->where('academic_year_id', $this->academicYearId)
                        ->where('my_class_id', $this->selectedClass)
                        ->first();
    
                    if (!$yearRecord) {
                        $student = StudentRecord::find($studentId);
                        $errors[] = $student->user->name . ' - No academic year record';
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
                    $student = StudentRecord::find($studentId);
                    $errors[] = $student->user->name . ' - ' . $e->getMessage();
                }
            }
    
            DB::commit();
            
            if (empty($errors)) {
                $subjectName = Subject::find($this->selectedSubject)?->name ?? 'Subject';
                session()->flash('success', "Bulk upload successful! Results saved for {$savedCount} student(s) in {$subjectName}");
                
                return redirect()->route('result');
            } else {
                $errorCount = count($errors);
                $subjectName = Subject::find($this->selectedSubject)?->name ?? 'Subject';
                
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

    public function render()
    {
        $classes = MyClass::orderBy('name')->get();
        $sections = Section::when($this->selectedClass, fn($q) => $q->where('my_class_id', $this->selectedClass))->get();
    
        return view('livewire.result.upload.bulk-upload', compact('classes', 'sections'))
            ->layout('layouts.new', [
                'title' => 'Bulk Result Upload',
                'page_heading' => 'Bulk Result Upload'
            ]);
    }
}