<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Syllabus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'file', 'subject_id', 'semester_id',
    ];

    /**
     * Get the subject that owns the Syllabus.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('subject', function (Builder $subjectQuery) use ($schoolId) {
            $subjectQuery->where('school_id', $schoolId);
        });
    }

    public function scopeForSemester(Builder $query, ?int $semesterId): Builder
    {
        if (!$semesterId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('semester_id', $semesterId);
    }

    public function scopeForClass(Builder $query, ?int $classId): Builder
    {
        if (!$classId) {
            return $query;
        }

        return $query->whereHas('subject', function (Builder $subjectQuery) use ($classId) {
            $subjectQuery->where(function (Builder $classQuery) use ($classId) {
                $classQuery->where('my_class_id', $classId)
                    ->orWhereHas('classes', function (Builder $assignedClassQuery) use ($classId) {
                        $assignedClassQuery->where('my_classes.id', $classId);
                    });
            });
        });
    }
}
