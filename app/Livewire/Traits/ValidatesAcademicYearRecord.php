<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\DB;
use App\Models\{Semester, Subject};

trait ValidatesAcademicYearRecord
{
    /**
     * Validate that a student has a record for the given academic year
     */
    protected function validateStudentAcademicYear($studentRecordId, $academicYearId)
    {
        $exists = DB::table('academic_year_student_record')
            ->where('student_record_id', $studentRecordId)
            ->where('academic_year_id', $academicYearId)
            ->exists();

        if (!$exists) {
            throw new \Exception(
                "Student has no record for this academic year. They may have been promoted or not yet assigned."
            );
        }

        return true;
    }

    /**
     * Validate semester belongs to academic year
     */
    protected function validateSemesterBelongsToYear($semesterId, $academicYearId)
    {
        $valid = Semester::where('id', $semesterId)
            ->where('academic_year_id', $academicYearId)
            ->exists();

        if (!$valid) {
            throw new \Exception("Invalid semester for the selected academic year.");
        }

        return true;
    }

    /**
     * Validate subject belongs to student's class for the academic year
     */
    protected function validateSubjectForStudentClass($subjectId, $studentRecordId, $academicYearId)
    {
        // Get student's class for this academic year
        $yearRecord = DB::table('academic_year_student_record')
            ->where('student_record_id', $studentRecordId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        if (!$yearRecord) {
            throw new \Exception("Student has no class record for this academic year.");
        }

        // Check if subject belongs to that class
        $subject = Subject::find($subjectId);
        if (!$subject || $subject->my_class_id != $yearRecord->my_class_id) {
            throw new \Exception("Subject does not belong to student's class for this academic year.");
        }

        return true;
    }

    /**
     * Validate student is enrolled in subject
     */
    protected function validateStudentEnrolledInSubject($studentRecordId, $subjectId)
    {
        $enrolled = DB::table('student_subject')
            ->where('student_record_id', $studentRecordId)
            ->where('subject_id', $subjectId)
            ->exists();

        if (!$enrolled) {
            throw new \Exception("Student is not enrolled in this subject.");
        }

        return true;
    }

    /**
     * Get student's class for specific academic year
     */
    protected function getStudentClassForYear($studentRecordId, $academicYearId)
    {
        $record = DB::table('academic_year_student_record')
            ->where('student_record_id', $studentRecordId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        return $record ? $record->my_class_id : null;
    }

    /**
     * Comprehensive validation before saving results
     */
    protected function validateBeforeSave($studentRecordId, $subjectId, $academicYearId, $semesterId)
    {
        // 1. Validate academic year record exists
        $this->validateStudentAcademicYear($studentRecordId, $academicYearId);

        // 2. Validate semester belongs to year
        $this->validateSemesterBelongsToYear($semesterId, $academicYearId);

        // 3. Validate subject belongs to student's class for this year
        $this->validateSubjectForStudentClass($subjectId, $studentRecordId, $academicYearId);

        // 4. Validate student is enrolled in subject
        $this->validateStudentEnrolledInSubject($studentRecordId, $subjectId);

        return true;
    }
}