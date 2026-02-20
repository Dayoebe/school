<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\MyClass;
use App\Models\Section;
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

    public function resultsPublishedBy()
    {
        return $this->belongsTo(User::class, 'results_published_by');
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
        return $this->studentAnswers()
            ->where('user_id', $userId)
            ->orderBy('attempt_number', 'desc')
            ->first();
    }

    /**
     * Get student's attempt results
     */
    public function getStudentResults($userId, $attemptNumber = null)
    {
        $query = $this->studentAnswers()
            ->where('user_id', $userId)
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
        $correctAnswers = $answers->where('is_correct', true)->count();
        $totalPoints = $answers->sum('points_earned');
        $maxPoints = $this->questions()->sum('points');

        $percentage = $maxPoints > 0 ? round(($totalPoints / $maxPoints) * 100, 1) : 0;

        return [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answers->count(),
            'correct_answers' => $correctAnswers,
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
        $lastSubmittedAttempt = $this->studentAnswers()
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->max('attempt_number');

        $lastSessionAttempt = $this->attemptSessions()
            ->where('user_id', $userId)
            ->max('attempt_number');

        $lastAttempt = max((int) ($lastSubmittedAttempt ?? 0), (int) ($lastSessionAttempt ?? 0));

        return $lastAttempt + 1;
    }

    public function getActiveAttemptSession($userId): ?AttemptSession
    {
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
     * NEW: Scope for standalone CBT assessments (not tied to sections/lessons)
     */
    public function scopeStandaloneCBT($query)
    {
        return $query->where('type', 'quiz')
            ->whereNull('section_id')
            ->whereNull('lesson_id');
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
