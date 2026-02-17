<?php

namespace App\Livewire\Students;

use Livewire\Component;
use App\Models\User;
use App\Models\AcademicYear;
// You might need to import other models like Attendance, SchoolTerm, etc., based on your database schema
// use App\Models\Attendance;

class StudentAttendance extends Component
{
    public $studentId;
    public $academicYearId;
    public $attendanceRecords = [];
    public $loading = true;

    public function mount($studentId, $academicYearId)
    {
        $this->studentId = $studentId;
        $this->academicYearId = $academicYearId;
        $this->loadAttendance();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['studentId', 'academicYearId'])) {
            $this->loadAttendance();
        }
    }

    public function loadAttendance()
    {
        $this->loading = true;
        $this->attendanceRecords = [];

        if (!$this->studentId || !$this->academicYearId) {
            $this->loading = false;
            return;
        }

        // --- Placeholder for actual attendance fetching logic ---
        // You would query your Attendance model here, potentially joining with User, AcademicYear, etc.
        // Example (adjust based on your actual Attendance model and relationships):
        /*
        $this->attendanceRecords = Attendance::where('student_id', $this->studentId)
                                            ->where('academic_year_id', $this->academicYearId)
                                            ->orderBy('date', 'desc')
                                            ->get()
                                            ->map(function($record) {
                                                return [
                                                    'date' => $record->date->format('M d, Y'),
                                                    'status' => $record->status, // e.g., 'Present', 'Absent', 'Late'
                                                    'reason' => $record->reason,
                                                ];
                                            })->toArray();
        */

        // Dummy data for demonstration
        $this->attendanceRecords = [
            ['date' => 'Jul 15, 2025', 'status' => 'Present', 'reason' => null],
            ['date' => 'Jul 14, 2025', 'status' => 'Absent', 'reason' => 'Sick'],
            ['date' => 'Jul 13, 2025', 'status' => 'Present', 'reason' => null],
            ['date' => 'Jul 12, 2025', 'status' => 'Late', 'reason' => 'Traffic'],
        ];
        // --- End Placeholder ---

        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.students.student-attendance');
    }
}
