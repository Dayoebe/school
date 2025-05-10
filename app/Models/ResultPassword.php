<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultPassword extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_record_id',
        'academic_year_id',
        'term_id',
        'password',
        'used',
    ];

    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Semester::class);
    }
}
