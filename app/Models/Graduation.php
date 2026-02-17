<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Graduation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_record_id',
        'academic_year_id',
        'graduation_class_id',
        'graduation_section_id',
        'graduation_date',
        'certificate_number',
        'remarks',
        'school_id',
    ];

    protected $casts = [
        'graduation_date' => 'date',
    ];

    /**
     * Get the student record
     */
    public function studentRecord(): BelongsTo
    {
        return $this->belongsTo(StudentRecord::class);
    }

    /**
     * Get the academic year of graduation
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the class from which student graduated
     */
    public function graduationClass(): BelongsTo
    {
        return $this->belongsTo(MyClass::class, 'graduation_class_id');
    }

    /**
     * Get the section from which student graduated
     */
    public function graduationSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'graduation_section_id');
    }

    /**
     * Get the school
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the user (student)
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, StudentRecord::class, 'id', 'id', 'student_record_id', 'user_id');
    }
}