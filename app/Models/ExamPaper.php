<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ExamPaper extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'my_class_id',
        'subject_id',
        'title',
        'instructions',
        'typed_content',
        'attachment_path',
        'attachment_name',
        'attachment_mime_type',
        'attachment_size',
        'uploaded_by',
        'published_at',
        'published_by',
        'sealed_at',
        'sealed_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'sealed_at' => 'datetime',
    ];

    protected $appends = [
        'attachment_url',
        'is_published',
        'is_sealed',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function myClass(): BelongsTo
    {
        return $this->belongsTo(MyClass::class, 'my_class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function sealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sealed_by');
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('exam.semester', function (Builder $semesterQuery) use ($schoolId) {
            $semesterQuery->where('school_id', $schoolId);
        });
    }

    public function scopeForCurrentSchoolAcademicPeriod(Builder $query, ?User $user): Builder
    {
        $academicYearId = $user?->school?->academic_year_id;
        $semesterId = $user?->school?->semester_id;

        if (!$academicYearId || !$semesterId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('exam.semester', function (Builder $semesterQuery) use ($academicYearId, $semesterId) {
            $semesterQuery
                ->where('semesters.id', $semesterId)
                ->where('semesters.academic_year_id', $academicYearId);
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    public function scopeVisibleToStudent(Builder $query, int $studentUserId): Builder
    {
        return $query->whereExists(function ($subQuery) use ($studentUserId) {
            $subQuery->select(DB::raw(1))
                ->from('student_records as sr')
                ->leftJoin('academic_year_student_record as aysr', 'aysr.student_record_id', '=', 'sr.id')
                ->join('exams as e', 'e.id', '=', 'exam_papers.exam_id')
                ->join('semesters as sem', 'sem.id', '=', 'e.semester_id')
                ->where('sr.user_id', $studentUserId)
                ->where(function ($matchQuery) {
                    $matchQuery
                        ->where(function ($academicYearMatch) {
                            $academicYearMatch
                                ->whereColumn('aysr.academic_year_id', 'sem.academic_year_id')
                                ->whereColumn('aysr.my_class_id', 'exam_papers.my_class_id');
                        })
                        ->orWhere(function ($fallbackMatch) {
                            $fallbackMatch
                                ->whereNull('aysr.id')
                                ->whereColumn('sr.my_class_id', 'exam_papers.my_class_id');
                        });
                });
        });
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment_path) {
            return null;
        }

        return asset('storage/' . $this->attachment_path);
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->published_at !== null;
    }

    public function getIsSealedAttribute(): bool
    {
        return $this->sealed_at !== null;
    }
}
