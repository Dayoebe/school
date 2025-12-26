<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class MyClass extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'class_group_id'];

    public function school()
    {
        $this->hasOneThrough(School::class, ClassGroup::class);
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function studentRecords()
    {
        return $this->hasMany(StudentRecord::class);
    }

    /**
     * Get class teachers for this class
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_teacher', 'class_id', 'teacher_id')
            ->withTimestamps();
    }

    /**
     * Check if a user is a class teacher for this class
     */
    public function hasTeacher($teacherId): bool
    {
        return $this->teachers()->where('users.id', $teacherId)->exists();
    }

    /**
     * Add a teacher as class teacher
     */
    public function addTeacher($teacherId)
    {
        if (!$this->hasTeacher($teacherId)) {
            $this->teachers()->attach($teacherId);
        }
    }

    /**
     * Remove a teacher as class teacher
     */
    public function removeTeacher($teacherId)
    {
        $this->teachers()->detach($teacherId);
    }

    public function result()
    {
        return $this->belongsTo(Result::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Get students in this class (basic - not academic year aware)
     * ⚠️ DEPRECATED: Use studentsForAcademicYear() instead for accurate results
     */
    public function students(): Collection
    {
        $students = User::students()
            ->inSchool()
            ->whereRelation('studentRecord.myClass', 'id', $this->id)
            ->get();

        return $students;
    }

    /**
     * 🔥 RECOMMENDED: Get students in this class for a specific academic year
     * This is the correct way to query students after promotions
     * 
     * @param int|null $academicYearId If null, uses current academic year
     * @return Collection
     */
    public function studentsForAcademicYear($academicYearId = null): Collection
    {
        // Use current academic year if not specified
        if (!$academicYearId) {
            $academicYearId = auth()->user()?->school->academic_year_id;
        }

        if (!$academicYearId) {
            return collect();
        }

        // Get all student records that have this class in the specified academic year
        $studentIds = DB::table('academic_year_student_record')
            ->where('my_class_id', $this->id)
            ->where('academic_year_id', $academicYearId)
            ->pluck('student_record_id');

        if ($studentIds->isEmpty()) {
            return collect();
        }

        // Get the actual user records
        $students = User::students()
            ->inSchool()
            ->whereHas('studentRecord', function($query) use ($studentIds) {
                $query->whereIn('id', $studentIds);
            })
            ->with(['studentRecord' => function($query) use ($academicYearId) {
                $query->with(['academicYears' => function($q) use ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                }]);
            }])
            ->get();

        return $students;
    }

    /**
     * Get students in this class and section for a specific academic year
     * 
     * @param int|null $academicYearId
     * @param int|null $sectionId
     * @return Collection
     */
    public function studentsForAcademicYearAndSection($academicYearId = null, $sectionId = null): Collection
    {
        if (!$academicYearId) {
            $academicYearId = auth()->user()?->school->academic_year_id;
        }

        if (!$academicYearId) {
            return collect();
        }

        $query = DB::table('academic_year_student_record')
            ->where('my_class_id', $this->id)
            ->where('academic_year_id', $academicYearId);

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        $studentIds = $query->pluck('student_record_id');

        if ($studentIds->isEmpty()) {
            return collect();
        }

        $students = User::students()
            ->inSchool()
            ->whereHas('studentRecord', function($query) use ($studentIds) {
                $query->whereIn('id', $studentIds);
            })
            ->with('studentRecord')
            ->get();

        return $students;
    }

    /**
     * Count students in this class for current academic year
     * 
     * @param int|null $academicYearId
     * @return int
     */
    public function studentsCount($academicYearId = null): int
    {
        if (!$academicYearId) {
            $academicYearId = auth()->user()?->school->academic_year_id;
        }

        if (!$academicYearId) {
            return 0;
        }

        return DB::table('academic_year_student_record')
            ->where('my_class_id', $this->id)
            ->where('academic_year_id', $academicYearId)
            ->count();
    }

    /**
     * Get student record IDs for this class in a specific academic year
     * Useful for queries
     * 
     * @param int|null $academicYearId
     * @return Collection
     */
    public function getStudentRecordIds($academicYearId = null): Collection
    {
        if (!$academicYearId) {
            $academicYearId = auth()->user()?->school->academic_year_id;
        }

        if (!$academicYearId) {
            return collect();
        }

        return DB::table('academic_year_student_record')
            ->where('my_class_id', $this->id)
            ->where('academic_year_id', $academicYearId)
            ->pluck('student_record_id');
    }

    /**
     * Check if a student is in this class for a specific academic year
     * 
     * @param int $studentRecordId
     * @param int|null $academicYearId
     * @return bool
     */
    public function hasStudent($studentRecordId, $academicYearId = null): bool
    {
        if (!$academicYearId) {
            $academicYearId = auth()->user()?->school->academic_year_id;
        }

        if (!$academicYearId) {
            return false;
        }

        return DB::table('academic_year_student_record')
            ->where('my_class_id', $this->id)
            ->where('academic_year_id', $academicYearId)
            ->where('student_record_id', $studentRecordId)
            ->exists();
    }

    public function syllabi(): HasManyThrough
    {
        return $this->hasManyThrough(Syllabus::class, Subject::class);
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }
}