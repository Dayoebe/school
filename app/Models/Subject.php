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
        'my_class_id', // Deprecated - kept for legacy compatibility
        'is_legacy',
        'merged_into_subject_id',
    ];

    protected $casts = [
        'is_general' => 'boolean',
        'is_legacy' => 'boolean',
    ];

    protected static function booted()
    {
        // Auto-assign subject to students when assigned to a class
        static::created(function ($subject) {
            if (!$subject->is_legacy) {
                $subject->autoAssignToClassStudents();
            }
        });
    }

    /**
     * Scope to exclude legacy subjects
     */
    public function scopeActive($query)
    {
        return $query->where('is_legacy', false);
    }

    /**
     * Get the primary subject if this is a legacy record
     */
    public function getPrimarySubject()
    {
        if ($this->is_legacy && $this->merged_into_subject_id) {
            return Subject::find($this->merged_into_subject_id);
        }
        return $this;
    }

    /**
     * Classes assigned to this subject (Many-to-Many)
     */
    public function classes()
    {
        return $this->belongsToMany(MyClass::class, 'class_subject', 'subject_id', 'my_class_id')
            ->withTimestamps();
    }

    /**
     * Legacy single class relationship (kept for backward compatibility)
     */
    public function myClass()
    {
        return $this->belongsTo(MyClass::class);
    }

    /**
     * Teachers assigned to this subject
     * Can be general (all classes) or class-specific
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'subject_teacher', 'subject_id', 'user_id')
            ->withPivot('my_class_id', 'is_general')
            ->withTimestamps();
    }

    /**
     * Get general teachers (teaching all classes)
     */
    public function generalTeachers()
    {
        return $this->teachers()->wherePivot('is_general', true);
    }

    /**
     * Get teacher for a specific class
     * Returns class-specific teacher if exists, otherwise general teacher
     */
    public function getTeacherForClass($classId)
    {
        // First try to get class-specific teacher
        $classTeacher = $this->teachers()
            ->wherePivot('my_class_id', $classId)
            ->wherePivot('is_general', false)
            ->first();

        if ($classTeacher) {
            return $classTeacher;
        }

        // Fallback to general teacher
        return $this->generalTeachers()->first();
    }

    /**
     * Get all teachers for a specific class (including general)
     */
    public function getTeachersForClass($classId)
    {
        return $this->teachers()
            ->where(function($query) use ($classId) {
                $query->wherePivot('my_class_id', $classId)
                      ->orWherePivot('is_general', true);
            })
            ->get();
    }

    /**
     * Assign teacher to this subject for a specific class
     */
    public function assignTeacher($teacherId, $classId = null, $isGeneral = false)
    {
        $data = [
            'school_id' => $this->school_id,
            'is_general' => $isGeneral,
        ];

        if (!$isGeneral && $classId) {
            $data['my_class_id'] = $classId;
        }

        $this->teachers()->syncWithoutDetaching([
            $teacherId => $data
        ]);
    }

    /**
     * Assign this subject to a class
     */
    public function assignToClass($classId)
    {
        $this->classes()->syncWithoutDetaching([
            $classId => [
                'school_id' => $this->school_id,
            ]
        ]);

        // Auto-assign to students in that class
        $this->autoAssignToClassStudents($classId);
    }

    /**
     * Remove subject from a class
     */
    public function removeFromClass($classId)
    {
        $this->classes()->detach($classId);
        
        // Optionally remove from students in that class
        $this->removeFromClassStudents($classId);
    }

    /**
     * Automatically assign this subject to all students in assigned classes
     */
    public function autoAssignToClassStudents($specificClassId = null)
    {
        $currentAcademicYearId = auth()->user()?->school->academic_year_id;
        
        if (!$currentAcademicYearId) {
            return;
        }

        $classIds = $specificClassId 
            ? [$specificClassId] 
            : $this->classes->pluck('id')->toArray();

        if (empty($classIds)) {
            return;
        }

        $studentRecordIds = DB::table('academic_year_student_record')
            ->whereIn('my_class_id', $classIds)
            ->where('academic_year_id', $currentAcademicYearId)
            ->pluck('student_record_id')
            ->unique();

        if ($studentRecordIds->isEmpty()) {
            return;
        }

        $students = StudentRecord::whereIn('id', $studentRecordIds)->get();

        foreach ($students as $student) {
            $this->assignToStudent($student);
        }
    }

    /**
     * Remove subject from students in a specific class
     */
    protected function removeFromClassStudents($classId)
    {
        $currentAcademicYearId = auth()->user()?->school->academic_year_id;
        
        if (!$currentAcademicYearId) {
            return;
        }

        $studentRecordIds = DB::table('academic_year_student_record')
            ->where('my_class_id', $classId)
            ->where('academic_year_id', $currentAcademicYearId)
            ->pluck('student_record_id');

        DB::table('student_subject')
            ->whereIn('student_record_id', $studentRecordIds)
            ->where('subject_id', $this->id)
            ->delete();
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
                'my_class_id' => $student->my_class_id,
                'section_id' => $student->section_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // Existing relationships
    public function sections()
    {
        return $this->belongsToMany(Section::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function timetableRecord(): MorphOne
    {
        return $this->morphOne(TimetableRecord::class, 'timetable_time_slot_weekdayable');
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