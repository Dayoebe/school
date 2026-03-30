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
        $students = User::query()
            ->inSchool()
            ->whereNull('deleted_at')
            ->whereHas('studentRecord', function ($query) {
                $query->where('section_id', $this->id);
            })
            ->with(['studentRecord.myClass', 'studentRecord.section'])
            ->orderBy('name')
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

        if (!$academicYearId) {
            return new Collection();
        }
    
        $studentIds = \DB::table('academic_year_student_record')
            ->where('section_id', $this->id)
            ->where('academic_year_id', $academicYearId)
            ->pluck('student_record_id');

        if ($studentIds->isEmpty()) {
            return new Collection();
        }
    
        $students = User::query()
            ->inSchool()
            ->whereNull('deleted_at')
            ->whereHas('studentRecord', function($query) use ($studentIds) {
                $query->whereIn('id', $studentIds);
            })
            ->with(['studentRecord.myClass', 'studentRecord.section'])
            ->orderBy('name')
            ->get();
    
        return $students;
    }
    

    /**
     * Count students in this section for current academic year
     */
    public function studentsCount($academicYearId = null): int
    {
        if (!$academicYearId) {
            $academicYearId = auth()->user()?->school->academic_year_id;
        }

        if (!$academicYearId) {
            return 0;
        }

        $schoolId = $this->myClass?->classGroup?->school_id
            ?? $this->myClass()->with('classGroup')->first()?->classGroup?->school_id;

        return \DB::table('academic_year_student_record as aysr')
            ->join('student_records as sr', 'sr.id', '=', 'aysr.student_record_id')
            ->join('users as u', 'u.id', '=', 'sr.user_id')
            ->where('aysr.section_id', $this->id)
            ->where('aysr.academic_year_id', $academicYearId)
            ->where('sr.is_graduated', false)
            ->whereNull('u.deleted_at')
            ->when($schoolId, fn ($query) => $query->where('u.school_id', $schoolId))
            ->distinct()
            ->count('aysr.student_record_id');
    }
}
