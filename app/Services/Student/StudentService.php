<?php

namespace App\Services\Student;

use App\Exceptions\EmptyRecordsException;
use App\Exceptions\InvalidValueException;
use App\Models\Promotion;
use App\Models\School;
use App\Models\StudentRecord;
use App\Models\User;
use App\Services\MyClass\MyClassService;
use App\Services\Print\PrintService;
use App\Services\Section\SectionService;
use App\Services\User\UserService;
use Illuminate\Support\Facades\DB;

class StudentService
{
    public $myClassService;
    public $userService;
    public SectionService $sectionService;

    public function __construct(MyClassService $myClassService, UserService $userService, SectionService $sectionService)
    {
        $this->myClassService = $myClassService;
        $this->sectionService = $sectionService;
        $this->userService = $userService;
    }

    public function getAllStudents()
    {
        return $this->userService->getUsersByRole('student')->load('studentRecord');
    }

    public function getAllActiveStudents()
    {
        return $this->userService->getUsersByRole('student')->load('studentRecord')->filter(function ($student) {
            if ($student->studentRecord) {
                return $student->studentRecord->is_graduated == false;
            }
        });
    }

    public function getAllGraduatedStudents()
    {
        return $this->userService->getUsersByRole('student')->load('studentRecord')->filter(function ($student) {
            return $student->studentRecord()->withoutGlobalScopes()->first()->is_graduated == true;
        });
    }

    public function getStudentById($id)
    {
        return $this->userService->getUserById($id)->load('studentRecord');
    }

    public function createStudent($record)
    {
        DB::transaction(function () use ($record) {
            $student = $this->userService->createUser($record);
            $student->assignRole('student');
            $this->createStudentRecord($student, $record);
        });
    }

    public function createStudentRecord(User $student, $record)
    {
        $record['admission_number'] || $record['admission_number'] = $this->generateAdmissionNumber();
        if ($record['section_id']) {
            $section = $this->sectionService->getSectionById($record['section_id']);
            if (!$this->myClassService->getClassById($record['my_class_id'])->sections->contains($section)) {
                throw new InvalidValueException('Section is not in class');
            }
        }
        if (auth()->user()->school->academic_year_id == null) {
            throw new EmptyRecordsException('Academic Year not set');
        }
        $student->studentRecord()->firstOrCreate([
            'user_id' => $student->id,
        ], [
            'my_class_id'      => $record['my_class_id'],
            'section_id'       => $record['section_id'] ?? null,
            'admission_number' => $record['admission_number'],
            'admission_date'   => $record['admission_date'],
        ]);
        if ($record['section_id']) {
            $currentAcademicYear = $student->school->academicYear;
            $student->studentRecord->load('academicYears')->academicYears()->sync([$currentAcademicYear->id => [
                'my_class_id' => $record['my_class_id'],
                'section_id'  => $record['section_id'],
            ]]);
        }
    }

    public function updateStudent(User $student, $records)
    {
        $student = $this->userService->updateUser($student, $records);
    }

    public function deleteStudent(User $student)
    {
        $student->delete();
    }

    public function generateAdmissionNumber($schoolId = null)
    {
        $schoolInitials = (School::find($schoolId) ?? auth()->user()->school)->initials;
        $schoolInitials != null && $schoolInitials .= '/';
        $currentYear = date('y');
        do {
            $admissionNumber = "$schoolInitials"."$currentYear/".\mt_rand('100000', '999999');
            if (StudentRecord::where('admission_number', $admissionNumber)->count() <= 0) {
                $uniqueAdmissionNumberFound = true;
            } else {
                $uniqueAdmissionNumberFound = false;
            }
        } while ($uniqueAdmissionNumberFound == false);

        return $admissionNumber;
    }

    public function printProfile(string $name, string $view, array $data)
    {
        return PrintService::createPdfFromView($view, $data)->download($name.'.pdf');
    }

    public function promoteStudents($records)
    {
        $oldClass = $this->myClassService->getClassById($records['old_class_id']);
        $newClass = $this->myClassService->getClassById($records['new_class_id']);
        $academicYear = auth()->user()->school->academic_year_id;
        if (!$oldClass->sections()->where('id', $records['old_section_id'])->exists()) {
            throw new InvalidValueException('Old section is not in old class');
        }
        if (!$newClass->sections()->where('id', $records['new_section_id'])->exists()) {
            throw new InvalidValueException('New section is not in new class');
        }
        if ($academicYear == null) {
            throw new InvalidValueException('Academic year is not set');
        }
        $students = $this->getAllActiveStudents()->whereIn('id', $records['student_id']);
        if (!$students->count()) {
            throw new EmptyRecordsException('No students to promote', 1);
        }
        $currentAcademicYear = auth()->user()->school->academicYear;
        foreach ($students as $student) {
            if (in_array($student->id, $records['student_id'])) {
                $student->studentRecord()->update([
                    'my_class_id' => $records['new_class_id'],
                    'section_id'  => $records['new_section_id'],
                ]);
                $student->studentRecord->load('academicYears')->academicYears()->syncWithoutDetaching([$currentAcademicYear->id => [
                    'my_class_id' => $records['new_class_id'],
                    'section_id'  => $records['new_section_id'],
                ]]);
            }
        }
        Promotion::create([
            'old_class_id'     => $records['old_class_id'],
            'new_class_id'     => $records['new_class_id'],
            'old_section_id'   => $records['old_section_id'],
            'new_section_id'   => $records['new_section_id'],
            'students'         => $students->pluck('id'),
            'academic_year_id' => $academicYear,
            'school_id'        => auth()->user()->school_id,
        ]);
    }

    public function getAllPromotions()
    {
        return Promotion::where('school_id', auth()->user()->school_id)->get();
    }

    public function getPromotionsByAcademicYearId(int $academicYearId)
    {
        return Promotion::where('school_id', auth()->user()->school_id)->where('academic_year_id', $academicYearId)->get();
    }

    public function resetPromotion(Promotion $promotion)
    {
        $students = $this->getStudentById($promotion->students);
        $currentAcademicYear = auth()->user()->school->academicYear;
        foreach ($students as $student) {
            $student->allStudentRecords->load('academicYears')->academicYears()->syncWithoutDetaching([$currentAcademicYear->id => [
                'my_class_id' => $promotion->old_class_id,
                'section_id'  => $promotion->old_section_id,
            ]]);
            $student->allStudentRecords()->update([
                'my_class_id' => $promotion->old_class_id,
                'section_id'  => $promotion->old_section_id,
            ]);
        }
        $promotion->delete();
    }

    public function graduateStudents($records)
    {
        $students = $this->getAllActiveStudents()->whereIn('id', $records['student_id']);
        if (!$students->count()) {
            throw new InvalidValueException('No students to graduate');
        }
        foreach ($students as $student) {
            if (in_array($student->id, $records['student_id'])) {
                $student->studentRecord()->update([
                    'is_graduated' => true,
                ]);
            }
        }
    }

    public function resetGraduation(User $student)
    {
        $student->graduatedStudentRecord()->update([
            'is_graduated' => false,
        ]);
    }
}
