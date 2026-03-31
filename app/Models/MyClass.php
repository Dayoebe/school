<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MyClass extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected const ACTIVE_STUDENT_CLASS_CODES = [
        'JSS1',
        'JSS2',
        'JSS3',
        'SS1',
        'SS2',
        'SS3',
        'SSS1',
        'SSS2',
        'SSS3',
    ];

    protected $fillable = ['name', 'class_group_id'];

    public function scopeInstructional($query)
    {
        return $query->whereIn(
            DB::raw("UPPER(REPLACE(name, ' ', ''))"),
            self::ACTIVE_STUDENT_CLASS_CODES
        );
    }

    public function isInstructional(): bool
    {
        $normalizedName = strtoupper(str_replace(' ', '', (string) $this->name));

        return in_array($normalizedName, self::ACTIVE_STUDENT_CLASS_CODES, true);
    }

    public function school()
    {
        return $this->hasOneThrough(School::class, ClassGroup::class);
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

    /**
     * ✅ NEW: Many-to-Many relationship for subjects (a class can have many subjects, a subject can be in many classes)
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'my_class_id', 'subject_id')
            ->withTimestamps()
            ->withPivot('school_id')
            ->where('subjects.is_legacy', false);
    }

    /**
     * ⚠️ DEPRECATED: Old one-to-many relationship (kept for backward compatibility)
     * Use subjects() instead
     */
    public function legacySubjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'my_class_id')
            ->where('is_legacy', false);
    }

    /**
     * Get students in this class (basic - not academic year aware)
     * ⚠️ DEPRECATED: Use studentsForAcademicYear() instead for accurate results
     */
    public function students(): Collection
    {
        $students = User::query()
            ->inSchool()
            ->whereNull('deleted_at')
            ->whereHas('studentRecord', function ($query) {
                $query->where('my_class_id', $this->id);
            })
            ->with(['studentRecord.myClass', 'studentRecord.section'])
            ->orderBy('name')
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
        $studentIds = $this->getStudentRecordIds($academicYearId);
    
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
     * Get students in this class and section for a specific academic year
     * 
     * @param int|null $academicYearId
     * @param int|null $sectionId
     * @return Collection
     */
    public function studentsForAcademicYearAndSection($academicYearId = null, $sectionId = null): Collection
    {
        $studentIds = $this->resolveStudentRecordIdsForAcademicYear($academicYearId, $sectionId);

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
     * Count students in this class for current academic year
     * 
     * @param int|null $academicYearId
     * @return int
     */
    public function studentsCount($academicYearId = null): int
    {
        return $this->getStudentRecordIds($academicYearId)->count();
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
        return $this->resolveStudentRecordIdsForAcademicYear($academicYearId);
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
        return $this->getStudentRecordIds($academicYearId)
            ->contains((int) $studentRecordId);
    }

    protected function resolveStudentRecordIdsForAcademicYear($academicYearId = null, $sectionId = null)
    {
        if (!$academicYearId) {
            $academicYearId = auth()->user()?->school->academic_year_id;
        }

        if (!$academicYearId) {
            return collect();
        }

        $schoolId = $this->classGroup?->school_id ?? $this->classGroup()->value('school_id');

        $academicYearStudentIds = DB::table('academic_year_student_record as aysr')
            ->join('student_records as sr', 'sr.id', '=', 'aysr.student_record_id')
            ->join('users as u', 'u.id', '=', 'sr.user_id')
            ->where('aysr.my_class_id', $this->id)
            ->where('aysr.academic_year_id', $academicYearId)
            ->where('sr.is_graduated', false)
            ->whereNull('u.deleted_at')
            ->when($schoolId, fn ($query) => $query->where('u.school_id', $schoolId))
            ->when($sectionId, fn ($query) => $query->where('aysr.section_id', $sectionId))
            ->pluck('aysr.student_record_id')
            ->map(fn ($id) => (int) $id);

        $fallbackStudentIds = StudentRecord::query()
            ->select('student_records.id')
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->where('student_records.my_class_id', $this->id)
            ->where('student_records.is_graduated', false)
            ->whereNull('users.deleted_at')
            ->when($schoolId, fn ($query) => $query->where('users.school_id', $schoolId))
            ->when($sectionId, fn ($query) => $query->where('student_records.section_id', $sectionId))
            ->pluck('student_records.id')
            ->map(fn ($id) => (int) $id);

        return $academicYearStudentIds
            ->merge($fallbackStudentIds)
            ->unique()
            ->values();
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
