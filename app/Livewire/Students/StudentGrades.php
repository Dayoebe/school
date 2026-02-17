<?php

namespace App\Livewire\Students;

use Livewire\Component;
use App\Models\Result;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Subject; // Import the Subject model
use Illuminate\Support\Collection;

class StudentGrades extends Component
{
    // Public properties to receive data from the parent component
    public $studentId;
    public $academicYearId;
    public $semesterId;

    // Properties to hold fetched data
    public $grades = [];
    public $subjects = []; // Add this public property to hold the student's subjects
    public $loading = true; // Add a loading state

    // Properties for editing grades
    public $isEditing = false;
    public $selectedSubject;

    /**
     * Mount method to initialize component properties.
     * This method is called when the component is first created.
     *
     * @param int $studentId The ID of the student.
     * @param int $academicYearId The ID of the academic year.
     * @param int $semesterId The ID of the semester.
     */
    public function mount($studentId, $academicYearId, $semesterId)
    {
        $this->studentId = $studentId;
        $this->academicYearId = $academicYearId;
        $this->semesterId = $semesterId;

        $this->loadSubjectsAndGrades();
    }

    /**
     * Listener for property updates. This method will be called whenever
     * studentId, academicYearId, or semesterId changes.
     */
    public function updated($propertyName)
    {
        if (in_array($propertyName, ['studentId', 'academicYearId', 'semesterId'])) {
            $this->loadSubjectsAndGrades();
        }
    }

    /**
     * Fetches the student's subjects and their grades.
     */
    public function loadSubjectsAndGrades()
    {
        $this->loading = true;
        $this->grades = []; // Clear previous grades
        $this->subjects = []; // Clear previous subjects

        if (!$this->studentId || !$this->academicYearId || !$this->semesterId) {
            $this->loading = false;
            return;
        }

        $student = User::find($this->studentId);

        if (!$student || !$student->studentRecord) {
            $this->loading = false;
            return;
        }

        // Fetch subjects the student is registered for in their current class/section
        // Assuming 'registeredSubjects' relationship on User model, or through StudentRecord
        // For simplicity, let's fetch subjects associated with their current class/section
        $this->subjects = Subject::where('my_class_id', $student->studentRecord->my_class_id)
                                    ->when($student->studentRecord->section_id, function ($query) use ($student) {
                                        $query->whereHas('sections', function ($q) use ($student) {
                                            $q->where('sections.id', $student->studentRecord->section_id);
                                        })->orWhere('is_general', true); // Include general subjects
                                    }, function ($query) {
                                        $query->where('is_general', true); // If no section, only general subjects for class
                                    })
                                    ->get();


        // Fetch results for the specific student, academic year, and semester
        $results = Result::where('student_record_id', $student->studentRecord->id)
                         ->where('academic_year_id', $this->academicYearId)
                         ->where('semester_id', $this->semesterId)
                         ->get()
                         ->keyBy('subject_id'); // Key results by subject_id for easy lookup

        // Map results to subjects
        $processedGrades = [];
        foreach ($this->subjects as $subject) {
            $result = $results->get($subject->id); // Get result for this subject
            $ca1 = (float)($result->ca1_score ?? 0);
            $ca2 = (float)($result->ca2_score ?? 0);
            $ca3 = (float)($result->ca3_score ?? 0);
            $ca4 = (float)($result->ca4_score ?? 0);
            $exam = (float)($result->exam_score ?? 0);

            $total = $ca1 + $ca2 + $ca3 + $ca4 + $exam;

            $processedGrades[$subject->id] = [
                'id' => $result->id ?? null, // Result ID if exists
                'ca1_score' => $result->ca1_score,
                'ca2_score' => $result->ca2_score,
                'ca3_score' => $result->ca3_score,
                'ca4_score' => $result->ca4_score,
                'exam_score' => $result->exam_score,
                'total' => $total,
                'grade' => $this->calculateGrade($total),
                'teacher_comment' => $result->teacher_comment,
            ];
        }

        $this->grades = $processedGrades;
        $this->loading = false;
    }

    /**
     * Sets the selected subject for editing and opens the edit form.
     * @param int $subjectId
     */
    public function editGrade($subjectId)
    {
        $this->selectedSubject = $this->subjects->firstWhere('id', $subjectId);
        $this->isEditing = true;
    }

    /**
     * Saves the edited grade for the selected subject.
     */
    public function saveGrade()
    {
        if (!$this->selectedSubject) {
            session()->flash('error', 'No subject selected for editing.');
            return;
        }

        $subjectId = $this->selectedSubject->id;
        $studentRecordId = User::find($this->studentId)->studentRecord->id;

        // Validation rules for the scores
        $rules = [
            "grades.{$subjectId}.ca1_score" => 'nullable|numeric|min:0|max:10',
            "grades.{$subjectId}.ca2_score" => 'nullable|numeric|min:0|max:10',
            "grades.{$subjectId}.ca3_score" => 'nullable|numeric|min:0|max:10',
            "grades.{$subjectId}.ca4_score" => 'nullable|numeric|min:0|max:10',
            "grades.{$subjectId}.exam_score" => 'nullable|numeric|min:0|max:60',
        ];

        $this->validate($rules);

        $data = $this->grades[$subjectId];

        // Calculate total score
        $total = $this->calculateTotal($data);

        // Find existing result or create a new one
        $result = Result::firstOrNew([
            'student_record_id' => $studentRecordId,
            'subject_id' => $subjectId,
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
        ]);

        // Update result attributes
        $result->fill([
            'ca1_score' => $data['ca1_score'] === '' ? null : (float)$data['ca1_score'],
            'ca2_score' => $data['ca2_score'] === '' ? null : (float)$data['ca2_score'],
            'ca3_score' => $data['ca3_score'] === '' ? null : (float)$data['ca3_score'],
            'ca4_score' => $data['ca4_score'] === '' ? null : (float)$data['ca4_score'],
            'exam_score' => $data['exam_score'] === '' ? null : (float)$data['exam_score'],
            'total_score' => $total,
            'teacher_comment' => $data['teacher_comment'] ?? null, // Assuming you might add a comment field to the form
        ]);

        $result->save();

        session()->flash('message', 'Grades saved successfully!');
        $this->isEditing = false;
        $this->loadSubjectsAndGrades(); // Reload data to reflect changes
    }


    /**
     * Helper function to calculate the grade based on total score.
     * This can be a shared trait or a separate service if used elsewhere.
     * @param float $total
     * @return string
     */
    protected function calculateGrade($total)
    {
        if ($total >= 75) return 'A1';
        if ($total >= 70) return 'B2';
        if ($total >= 65) return 'B3';
        if ($total >= 60) return 'C4';
        if ($total >= 55) return 'C5';
        if ($total >= 50) return 'C6';
        if ($total >= 45) return 'D7';
        if ($total >= 40) return 'E8';
        return 'F9';
    }

    /**
     * Calculates the total score for a student.
     * @param array $data
     * @return float
     */
    public function calculateTotal($data)
    {
        $ca1 = (float)($data['ca1_score'] ?? 0);
        $ca2 = (float)($data['ca2_score'] ?? 0);
        $ca3 = (float)($data['ca3_score'] ?? 0);
        $ca4 = (float)($data['ca4_score'] ?? 0);
        $exam = (float)($data['exam_score'] ?? 0);

        return $ca1 + $ca2 + $ca3 + $ca4 + $exam;
    }

    /**
     * Render the Livewire component.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.students.student-grades', [
            'subjects' => $this->subjects, // Pass subjects to the view
        ]);
    }
}
