<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'short_name',
        'school_id',
        'is_general',
        'my_class_id',
    ];

    protected static function booted()
    {
        // Auto-assign subject to all students in the class when created
        static::created(function ($subject) {
            $subject->autoAssignToClassStudents();
        });

        // Re-assign when subject properties change
        static::updated(function ($subject) {
            if ($subject->isDirty(['my_class_id', 'is_general'])) {
                $subject->autoAssignToClassStudents();
            }
        });
    }

    /**
     * Automatically assign this subject to all students in its class
     */
    public function autoAssignToClassStudents()
    {
        if (!$this->my_class_id) {
            return;
        }

        $currentAcademicYearId = auth()->user()?->school->academic_year_id;
        
        if (!$currentAcademicYearId) {
            return;
        }

        // Get all students in this class for current academic year
        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('my_class_id', $this->my_class_id)
            ->where('academic_year_id', $currentAcademicYearId)
            ->pluck('student_record_id');

        if ($studentRecordIds->isEmpty()) {
            return;
        }

        $students = StudentRecord::whereIn('id', $studentRecordIds)->get();

        foreach ($students as $student) {
            // Check if subject is applicable to this student
            if ($this->is_general) {
                // General subject - assign to all students
                $this->assignToStudent($student);
            } else {
                // Elective subject - check if student's section matches
                if ($student->section_id && $this->sections->contains($student->section_id)) {
                    $this->assignToStudent($student);
                }
            }
        }
    }

    /**
     * Assign this subject to a specific student
     */
    protected function assignToStudent(StudentRecord $student)
    {
        $exists = DB::table('student_subject')
            ->where('student_record_id', $student->id)
            ->where('subject_id', $this->id)
            ->exists();

        if (!$exists) {
            DB::table('student_subject')->insert([
                'student_record_id' => $student->id,
                'subject_id' => $this->id,
                'my_class_id' => $this->my_class_id,
                'section_id' => $student->section_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class);
    }

    public function myClass()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'subject_user');
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function timetableRecord(): MorphOne
    {
        return $this->morphOne(TimetableRecord::class, 'timetable_time_slot_weekdayable');
    }

    public function assignToClassStudents($classId, $sectionId = null)
    {
        $students = StudentRecord::where('my_class_id', $classId)
            ->when($sectionId, function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->get();

        $syncData = [];
        foreach ($students as $student) {
            $syncData[$student->id] = [
                'my_class_id' => $classId,
                'section_id' => $sectionId,
            ];
        }

        $this->studentRecords()->syncWithoutDetaching($syncData);
    }

    public function studentRecords()
    {
        return $this->belongsToMany(
            StudentRecord::class,
            'student_subject',
            'subject_id',
            'student_record_id'
        )->withPivot('my_class_id', 'section_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_subject')
            ->withTimestamps()
            ->withPivot('my_class_id', 'section_id');
    }
}