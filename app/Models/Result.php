<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_record_id',
        'subject_id',
        'academic_year_id',
        'semester_id',
        'test_score',
        'exam_score',
        'total_score',
        'subject_position',
        'teacher_comment',
        'approved',
    ];

    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
    public function validate()
{
    if (!$this->studentRecord || !$this->academicYearId || !$this->semesterId) {
        session()->flash('error', 'Invalid student record, academic year, or semester.');
        return false;
    }
    return true;
}
}
