<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    public const UPLOAD_ARCHIVE_MARKER = '[system-exam-upload-archive]';

    protected $fillable = [
        'name',
        'description',
        'semester_id',
        'start_date',
        'stop_date',
        'active',
        'publish_result',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'start_date' => 'date:Y-m-d',
        'stop_date' => 'date:Y-m-d',
        'active' => 'boolean',
        'publish_result' => 'boolean',
    ];

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function examSlots(): HasMany
    {
        return $this->hasMany(ExamSlot::class);
    }

    public function papers(): HasMany
    {
        return $this->hasMany(ExamPaper::class);
    }

    public function scopeUploadArchives(Builder $query): Builder
    {
        return $query->where('description', 'like', '%' . self::UPLOAD_ARCHIVE_MARKER . '%');
    }

    public function scopeWithoutUploadArchives(Builder $query): Builder
    {
        return $query->where(function (Builder $archiveQuery) {
            $archiveQuery
                ->whereNull('description')
                ->orWhere('description', 'not like', '%' . self::UPLOAD_ARCHIVE_MARKER . '%');
        });
    }

    public function isUploadArchive(): bool
    {
        return str_contains((string) $this->description, self::UPLOAD_ARCHIVE_MARKER);
    }

    public static function uploadArchiveName(Semester $semester): string
    {
        return trim(sprintf('%s Exam Uploads', $semester->name));
    }

    public static function uploadArchiveDescription(Semester $semester): string
    {
        return trim(sprintf(
            '%s Auto-generated upload archive for %s %s',
            self::UPLOAD_ARCHIVE_MARKER,
            $semester->name,
            $semester->academicYear?->name ?? 'current session'
        ));
    }

    public function getTotalAttainableMarksInASubjectAttribute()
    {
        $totalMarks = 0;
        foreach ($this->examSlots as $examSlot) {
            $totalMarks += $examSlot->total_marks;
        }

        return $totalMarks;
    }

    public function calculateStudentTotalMarkInSubjectForSemester(Semester $semester, User $user, Subject $subject)
    {
        return $this->examRecordService->getAllUserExamRecordInSemesterForSubject($semester, $user->id, $subject->id)->pluck('student_marks')->sum();
    }
}
