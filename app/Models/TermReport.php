<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TermReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_record_id',
        'academic_year_id',
        'semester_id',
        'class_teacher_comment',
        'principal_comment',
        'general_announcement',
        'resumption_date',
    ];

    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
