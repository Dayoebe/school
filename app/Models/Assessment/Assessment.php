<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\User;

class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'section_id',
        'lesson_id',
        'title',
        'slug',
        'description',
        'type',
        'pass_percentage',
        'estimated_duration_minutes',
        'deadline',
        'project_type',
        'required_skills',
        'deliverables',
        'resources',
        'is_mandatory',
        'weight',
        'allows_collaboration',
        'evaluation_criteria',
        'due_date',
        'max_score',
        'instructions',
        'order',
        'max_attempts',
        'shuffle_questions',
        'shuffle_options',
        'results_published_at',
        'results_published_by',
        'is_locked',
        'exam_published_at',
        'exam_published_by',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'due_date' => 'datetime',
        'required_skills' => 'array',
        'deliverables' => 'array',
        'resources' => 'array',
        'is_mandatory' => 'boolean',
        'allows_collaboration' => 'boolean',
        'max_attempts' => 'integer',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'results_published_at' => 'datetime',
        'is_locked' => 'boolean',
        'exam_published_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(MyClass::class, 'course_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Subject::class, 'lesson_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'assessment_id')->orderBy('order');
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    public function attemptSessions()
    {
        return $this->hasMany(AttemptSession::class, 'assessment_id');
    }

    public function studentLocks()
    {
        return $this->hasMany(AssessmentStudentLock::class, 'assessment_id');
    }

    public function resultsPublishedBy()
    {
        return $this->belongsTo(User::class, 'results_published_by');
    }

    public function examPublishedBy()
    {
        return $this->belongsTo(User::class, 'exam_published_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($assessment) {
            // Generate slug
            $assessment->slug = Str::slug($assessment->title);

            // Set order based on whether it has a section or not
            if (
                $assessment->section_id
                && $assessment->section
                && method_exists($assessment->section, 'assessments')
            ) {
                // For assessments within sections
                $assessment->order = $assessment->order ?? $assessment->section->assessments()->count() + 1;
            } else {
                // For direct course assessments (like CBT assessments)
                $assessment->order = $assessment->order ?? Assessment::where('course_id', $assessment->course_id)
                    ->whereNull('section_id')
                    ->count() + 1;
            }

            // Set default max_attempts if not specified
            if (!isset($assessment->max_attempts)) {
                $assessment->max_attempts = null; // null = unlimited attempts
            }
        });

        static::updating(function ($assessment) {
            if ($assessment->isDirty('title')) {
                $assessment->slug = Str::slug($assessment->title);
            }
        });
    }

    public function getIsQuizAttribute()
    {
        return $this->type === 'quiz';
    }

    /**
     * NEW: Check if user can still take this assessment
     */
    public function canUserTakeAssessment($userId)
    {
        if (!$this->isExamPublished()) {
            return [false, 'This CBT exam has not been published to students yet.'];
        }

        if ($this->isLockedForStudent($userId)) {
            return [false, $this->lockedStudentMessage()];
        }

        $activeSession = $this->getActiveAttemptSession($userId);
        if ($activeSession && !$activeSession->isExpired()) {
            return [true, 'You have an in-progress attempt that can be resumed'];
        }

        // If max_attempts is null, unlimited attempts allowed
        if ($this->max_attempts === null) {
            return [true, 'You can take this assessment'];
        }

        $attemptCount = $this->getStudentAttemptCount($userId);

        if ($attemptCount >= $this->max_attempts) {
            return [false, "Maximum attempts ({$this->max_attempts}) exhausted"];
        }

        $remainingAttempts = $this->max_attempts - $attemptCount;
        return [true, "You have {$remainingAttempts} attempt(s) remaining"];
    }

    /**
     * NEW: Get total number of attempts by a student
     */
    public function getStudentAttemptCount($userId)
    {
        if ($this->relationLoaded('studentAnswers')) {
            return $this->studentAnswers
                ->where('user_id', (int) $userId)
                ->whereNotNull('submitted_at')
                ->pluck('attempt_number')
                ->unique()
                ->count();
        }

        return $this->studentAnswers()
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->distinct('attempt_number')
            ->count('attempt_number');
    }

    /**
     * NEW: Get remaining attempts for a student
     */
    public function getRemainingAttempts($userId)
    {
        if ($this->max_attempts === null) {
            return 'Unlimited';
        }

        $attemptCount = $this->getStudentAttemptCount($userId);
        $remaining = $this->max_attempts - $attemptCount;

        return max(0, $remaining);
    }

    /**
     * Get student's latest attempt for this assessment
     */
    public function getStudentLatestAttempt($userId)
    {
        if ($this->relationLoaded('studentAnswers')) {
            return $this->studentAnswers
                ->where('user_id', (int) $userId)
                ->whereNotNull('submitted_at')
                ->sortByDesc('attempt_number')
                ->first();
        }

        return $this->studentAnswers()
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->orderBy('attempt_number', 'desc')
            ->first();
    }

    /**
     * Get student's attempt results
     */
    public function getStudentResults($userId, $attemptNumber = null)
    {
        if ($this->relationLoaded('studentAnswers')) {
            $answers = $this->studentAnswers
                ->where('user_id', (int) $userId)
                ->whereNotNull('submitted_at');

            if ($attemptNumber !== null) {
                $answers = $answers->where('attempt_number', (int) $attemptNumber);
            } else {
                $latestAttemptNumber = $answers->max('attempt_number');

                if ($latestAttemptNumber === null) {
                    return null;
                }

                $attemptNumber = (int) $latestAttemptNumber;
                $answers = $answers->where('attempt_number', $attemptNumber);
            }

            if ($answers->isEmpty()) {
                return null;
            }

            $questionCollection = $this->relationLoaded('questions') ? $this->questions : null;

            if ($questionCollection !== null) {
                $questionsById = $questionCollection->keyBy('id');

                $answers = $answers->map(function (StudentAnswer $answer) use ($questionsById) {
                    if (!$answer->relationLoaded('question') && $questionsById->has($answer->question_id)) {
                        $answer->setRelation('question', $questionsById->get($answer->question_id));
                    }

                    return $answer;
                });
            }

            $totalQuestions = $questionCollection?->count() ?? $this->questions()->count();
            $answeredQuestions = $answers->count();
            $correctAnswers = $answers->where('is_correct', true)->count();
            $incorrectAnswers = max(0, $answeredQuestions - $correctAnswers);
            $unansweredQuestions = max(0, $totalQuestions - $answeredQuestions);
            $totalPoints = $answers->sum('points_earned');
            $maxPoints = $questionCollection !== null
                ? $questionCollection->sum('points')
                : $this->questions()->sum('points');

            $percentage = $maxPoints > 0 ? round(($totalPoints / $maxPoints) * 100, 1) : 0;

            return [
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'correct_answers' => $correctAnswers,
                'incorrect_answers' => $incorrectAnswers,
                'unanswered_questions' => $unansweredQuestions,
                'total_points' => $totalPoints,
                'max_points' => $maxPoints,
                'percentage' => $percentage,
                'passed' => $percentage >= $this->pass_percentage,
                'attempt_number' => $attemptNumber ?? ($answers->first()->attempt_number ?? 1),
                'submitted_at' => $answers->sortByDesc('submitted_at')->first()?->submitted_at,
                'answers' => $answers->keyBy('question_id')
            ];
        }

        $query = $this->studentAnswers()
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->with('question');

        if ($attemptNumber) {
            $query->where('attempt_number', $attemptNumber);
        } else {
            // Get latest attempt
            $latestAttempt = $this->getStudentLatestAttempt($userId);
            if ($latestAttempt) {
                $query->where('attempt_number', $latestAttempt->attempt_number);
            }
        }

        $answers = $query->get();

        if ($answers->isEmpty()) {
            return null;
        }

        $totalQuestions = $this->questions()->count();
        $answeredQuestions = $answers->count();
        $correctAnswers = $answers->where('is_correct', true)->count();
        $incorrectAnswers = max(0, $answeredQuestions - $correctAnswers);
        $unansweredQuestions = max(0, $totalQuestions - $answeredQuestions);
        $totalPoints = $answers->sum('points_earned');
        $maxPoints = $this->questions()->sum('points');

        $percentage = $maxPoints > 0 ? round(($totalPoints / $maxPoints) * 100, 1) : 0;

        return [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'correct_answers' => $correctAnswers,
            'incorrect_answers' => $incorrectAnswers,
            'unanswered_questions' => $unansweredQuestions,
            'total_points' => $totalPoints,
            'max_points' => $maxPoints,
            'percentage' => $percentage,
            'passed' => $percentage >= $this->pass_percentage,
            'attempt_number' => $answers->first()->attempt_number ?? 1,
            'submitted_at' => $answers->first()->submitted_at ?? null,
            'answers' => $answers->keyBy('question_id')
        ];
    }

    /**
     * Check if student has completed this assessment
     */
    public function isCompletedByStudent($userId)
    {
        return $this->studentAnswers()
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->exists();
    }

    /**
     * Check if student has passed this assessment
     */
    public function isPassedByStudent($userId)
    {
        $results = $this->getStudentResults($userId);
        return $results ? $results['passed'] : false;
    }

    /**
     * Get next attempt number for a student
     */
    public function getNextAttemptNumber($userId)
    {
        $lastSubmittedAttempt = $this->relationLoaded('studentAnswers')
            ? $this->studentAnswers
                ->where('user_id', (int) $userId)
                ->whereNotNull('submitted_at')
                ->max('attempt_number')
            : $this->studentAnswers()
                ->where('user_id', $userId)
                ->whereNotNull('submitted_at')
                ->max('attempt_number');

        $lastSessionAttempt = $this->relationLoaded('attemptSessions')
            ? $this->attemptSessions
                ->where('user_id', (int) $userId)
                ->max('attempt_number')
            : $this->attemptSessions()
                ->where('user_id', $userId)
                ->max('attempt_number');

        $lastAttempt = max((int) ($lastSubmittedAttempt ?? 0), (int) ($lastSessionAttempt ?? 0));

        return $lastAttempt + 1;
    }

    public function getActiveAttemptSession($userId): ?AttemptSession
    {
        if ($this->relationLoaded('attemptSessions')) {
            return $this->attemptSessions
                ->where('user_id', (int) $userId)
                ->where('status', 'in_progress')
                ->sortByDesc(fn (AttemptSession $attemptSession) => $attemptSession->started_at?->getTimestamp() ?? 0)
                ->first();
        }

        return $this->attemptSessions()
            ->where('user_id', $userId)
            ->where('status', 'in_progress')
            ->latest('started_at')
            ->first();
    }

    public function isResultPublished(): bool
    {
        return $this->results_published_at !== null;
    }

    public function isExamPublished(): bool
    {
        return $this->exam_published_at !== null;
    }

    public function isLockedForStudent(int|string|null $userId): bool
    {
        if (!$userId) {
            return false;
        }

        if ($this->relationLoaded('studentLocks')) {
            return $this->studentLocks->contains(fn ($lock) => (int) $lock->user_id === (int) $userId);
        }

        return $this->studentLocks()
            ->where('user_id', (int) $userId)
            ->exists();
    }

    public function lockedStudentMessage(): string
    {
        return 'You are currently marked ineligible for this CBT exam. Please contact school administration.';
    }

    public function canUserViewResults(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->can('manage cbt')) {
            return true;
        }

        return $this->isResultPublished();
    }

    /**
     * Get assessment statistics
     */
    public function getStatsAttribute()
    {
        $totalAttempts = $this->studentAnswers()
            ->distinct('user_id', 'attempt_number')
            ->count();

        $passedAttempts = $this->studentAnswers()
            ->select('user_id', 'attempt_number')
            ->selectRaw('SUM(points_earned) as total_points')
            ->groupBy('user_id', 'attempt_number')
            ->havingRaw('(SUM(points_earned) / ?) * 100 >= ?', [
                $this->questions()->sum('points'),
                $this->pass_percentage
            ])
            ->count();

        $averageScore = $this->studentAnswers()
            ->select('user_id', 'attempt_number')
            ->selectRaw('SUM(points_earned) as total_points')
            ->groupBy('user_id', 'attempt_number')
            ->get()
            ->avg('total_points');

        $maxPoints = $this->questions()->sum('points');

        return [
            'total_attempts' => $totalAttempts,
            'passed_attempts' => $passedAttempts,
            'pass_rate' => $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100, 1) : 0,
            'average_score' => $averageScore ? round($averageScore, 1) : 0,
            'average_percentage' => $maxPoints > 0 && $averageScore ? round(($averageScore / $maxPoints) * 100, 1) : 0
        ];
    }

    /**
     * Scope for published assessments
     */
    public function scopePublished($query)
    {
        return $query;
    }

    /**
     * Scope for mandatory assessments
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Scope for standalone CBT assessments that are not tied to lesson sections.
     * Subject-linked CBTs still count as standalone assessments.
     */
    public function scopeStandaloneCBT($query)
    {
        return $query->where('type', 'quiz')
            ->whereNull('section_id');
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('course.classGroup', function (Builder $classGroupQuery) use ($schoolId) {
            $classGroupQuery->where('school_id', $schoolId);
        });
    }

    public function scopeVisibleToUser(Builder $query, ?User $user): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        $query->forSchool($user->school_id);

        if (!$user->hasRole('student')) {
            return $query->whereRaw('1 = 0');
        }

        $classId = static::resolveAssignedClassIdForUser($user);
        if (!$classId) {
            return $query->whereRaw('1 = 0');
        }

        $studentRecord = static::resolveStudentRecordForUser($user);

        return $query
            ->where('course_id', $classId)
            ->where(function (Builder $assessmentQuery) use ($classId, $studentRecord) {
                $assessmentQuery->whereNull('lesson_id')
                    ->orWhereHas('lesson', function (Builder $lessonQuery) use ($classId, $studentRecord) {
                        $lessonQuery->where(function (Builder $subjectQuery) use ($classId, $studentRecord) {
                            $subjectQuery->where('subjects.my_class_id', $classId)
                                ->orWhereHas('classes', function (Builder $classQuery) use ($classId) {
                                    $classQuery->where('my_classes.id', $classId);
                                });

                            if ($studentRecord) {
                                $subjectQuery->orWhereIn('subjects.id', function ($subQuery) use ($studentRecord, $classId) {
                                    $subQuery->from('student_subject')
                                        ->where('student_record_id', $studentRecord->id)
                                        ->where(function ($classScope) use ($classId) {
                                            $classScope->whereNull('my_class_id')
                                                ->orWhere('my_class_id', $classId);
                                        })
                                        ->select('subject_id');
                                });
                            }
                        });
                    });
            });
    }

    public function scopeAvailableForStudentExamAccess(Builder $query, ?User $user): Builder
    {
        return $query
            ->visibleToUser($user)
            ->whereNotNull('exam_published_at');
    }

    public static function resolveAssignedClassIdForUser(?User $user): ?int
    {
        if (!$user) {
            return null;
        }

        $assignedClassCache = static::assignedClassIdCache();
        if (isset($assignedClassCache[$user])) {
            return $assignedClassCache[$user];
        }

        $studentRecord = static::resolveStudentRecordForUser($user);

        if (!$studentRecord) {
            return $assignedClassCache[$user] = null;
        }

        $academicYearId = $user->school?->academic_year_id;
        if ($academicYearId) {
            $classId = DB::table('academic_year_student_record')
                ->where('student_record_id', $studentRecord->id)
                ->where('academic_year_id', $academicYearId)
                ->value('my_class_id');

            if ($classId) {
                return $assignedClassCache[$user] = (int) $classId;
            }
        }

        return $assignedClassCache[$user] = ($studentRecord->my_class_id ? (int) $studentRecord->my_class_id : null);
    }

    protected static function resolveStudentRecordForUser(?User $user): ?StudentRecord
    {
        if (!$user) {
            return null;
        }

        $user->loadMissing(['studentRecord', 'school']);

        $studentRecord = $user->getRelation('studentRecord');

        return $studentRecord instanceof StudentRecord ? $studentRecord : null;
    }

    protected static function assignedClassIdCache(): \WeakMap
    {
        static $cache;

        if (!$cache instanceof \WeakMap) {
            $cache = new \WeakMap();
        }

        return $cache;
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->estimated_duration_minutes) {
            return 'No time limit';
        }

        $hours = floor($this->estimated_duration_minutes / 60);
        $minutes = $this->estimated_duration_minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return $minutes . 'm';
    }

    /**
     * NEW: Get formatted max attempts
     */
    public function getFormattedMaxAttemptsAttribute()
    {
        if ($this->max_attempts === null) {
            return 'Unlimited';
        }

        return $this->max_attempts . ' attempt' . ($this->max_attempts > 1 ? 's' : '');
    }
}
