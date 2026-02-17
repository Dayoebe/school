<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'my_class_id'];

    public function myClass()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function studentRecords()
    {
        return $this->hasMany(StudentRecord::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    /**
     * Get students in this section (basic - not academic year aware)
     */
    public function students(): Collection
    {
        $students = User::students()
            ->inSchool()
            ->whereRelation('studentRecord.section', 'id', $this->id)
            ->get();

        return $students;
    }

    /**
     * Get students in this section for a specific academic year
     * This is the correct way to query students after promotions
     */
    public function studentsForAcademicYear($academicYearId = null): Collection
    {
        if (!$academicYearId) {
            $academicYearId = auth()->user()->school->academic_year_id;
        }
    
        $studentIds = \DB::table('academic_year_student_record')
            ->where('section_id', $this->id)
            ->where('academic_year_id', $academicYearId)
            ->pluck('student_record_id');
    
        // ✅ FIXED: Eager load myClass and section relationships
        $students = User::students()
            ->inSchool()
            ->whereHas('studentRecord', function($query) use ($studentIds) {
                $query->whereIn('id', $studentIds);
            })
            ->with(['studentRecord.myClass', 'studentRecord.section'])
            ->get();
    
        return $students;
    }
    

    /**
     * Count students in this section for current academic year
     */
    public function studentsCount($academicYearId = null): int
    {
        if (!$academicYearId) {
            $academicYearId = auth()->user()->school->academic_year_id;
        }

        return \DB::table('academic_year_student_record')
            ->where('section_id', $this->id)
            ->where('academic_year_id', $academicYearId)
            ->count();
    }
}