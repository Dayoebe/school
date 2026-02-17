<?php

// CbtExam.php
namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\MyClass;
use App\Models\User;

class CbtExam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id', 'created_by', 'title', 'slug', 'description', 'exam_code',
        'exam_type', 'difficulty_level', 'duration_minutes', 'total_questions',
        'pass_percentage', 'max_attempts', 'questions_per_page', 'randomize_questions',
        'randomize_options', 'show_results_immediately', 'allow_review', 'allow_navigation',
        'start_date', 'end_date', 'available_days', 'start_time', 'end_time',
        'result_delivery', 'result_release_date', 'email_results', 'show_correct_answers',
        'show_explanations', 'is_published', 'is_active', 'max_participants',
        'instructions', 'exam_settings', 'tags', 'thumbnail'
    ];

    protected $casts = [
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'show_results_immediately' => 'boolean',
        'allow_review' => 'boolean',
        'allow_navigation' => 'boolean',
        'email_results' => 'boolean',
        'show_correct_answers' => 'boolean',
        'show_explanations' => 'boolean',
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'result_release_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'available_days' => 'array',
        'exam_settings' => 'array',
        'tags' => 'array',
        'pass_percentage' => 'decimal:2',
        'average_score' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($exam) {
            if (empty($exam->slug)) {
                $exam->slug = static::generateUniqueSlug($exam->title);
            }
            if (empty($exam->exam_code)) {
                $exam->exam_code = static::generateExamCode();
            }
        });

        static::updating(function ($exam) {
            if ($exam->isDirty('title')) {
                $exam->slug = static::generateUniqueSlug($exam->title);
            }
        });
    }

    // Relationships
    public function course()
    {
        return $this->belongsTo(MyClass::class, 'course_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'cbt_exam_questions')
                    ->withPivot(['order', 'points', 'is_mandatory'])
                    ->orderByPivot('order');
    }

    public function results()
    {
        return $this->hasMany(CbtResult::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'cbt_results', 'cbt_exam_id', 'user_id')
                    ->distinct();
    }

    // Static Methods
    public static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    public static function generateExamCode()
    {
        do {
            $code = 'CBT' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('exam_code', $code)->exists());

        return $code;
    }

    // Status Methods
    public function isAvailable()
    {
        if (!$this->is_published || !$this->is_active) {
            return false;
        }

        $now = now();

        // Check date range
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }
        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        // Check day of week
        if ($this->available_days && !in_array($now->dayOfWeek, $this->available_days)) {
            return false;
        }

        // Check time range
        if ($this->start_time && $this->end_time) {
            $currentTime = $now->format('H:i:s');
            if ($currentTime < $this->start_time->format('H:i:s') || 
                $currentTime > $this->end_time->format('H:i:s')) {
                return false;
            }
        }

        // Check participant limit
        if ($this->max_participants && $this->results()->count() >= $this->max_participants) {
            return false;
        }

        return true;
    }

    public function canUserTake(User $user)
    {
        if (!$this->isAvailable()) {
            return [false, 'Exam is not available'];
        }

        // Check if user has exceeded max attempts
        $attempts = $this->results()->where('user_id', $user->id)->count();
        if ($attempts >= $this->max_attempts) {
            return [false, 'Maximum attempts exceeded'];
        }

        // Check if user has an active session
        $activeSession = $this->results()
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();
        
        if ($activeSession) {
            return [false, 'You have an active exam session'];
        }

        return [true, 'Eligible to take exam'];
    }

    public function getUserResult(User $user, $attemptNumber = null)
    {
        $query = $this->results()->where('user_id', $user->id);
        
        if ($attemptNumber) {
            $query->where('attempt_number', $attemptNumber);
        } else {
            $query->orderBy('attempt_number', 'desc');
        }

        return $query->first();
    }

    public function getUserBestResult(User $user)
    {
        return $this->results()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('percentage_score', 'desc')
            ->first();
    }

    // Analytics Methods
    public function getStats()
    {
        $results = $this->results()->where('status', 'completed');
        
        return [
            'total_attempts' => $results->count(),
            'unique_participants' => $results->distinct('user_id')->count(),
            'pass_rate' => $results->count() > 0 
                ? round($results->where('passed', true)->count() / $results->count() * 100, 1)
                : 0,
            'average_score' => round($results->avg('percentage_score') ?? 0, 1),
            'highest_score' => $results->max('percentage_score') ?? 0,
            'lowest_score' => $results->min('percentage_score') ?? 0,
            'average_time' => round($results->avg('time_spent_seconds') / 60 ?? 0, 1), // in minutes
        ];
    }

    public function getQuestionStats()
    {
        $stats = [];
        
        foreach ($this->questions as $question) {
            $answers = CbtAnswer::whereHas('result', function($q) {
                $q->where('cbt_exam_id', $this->id)
                  ->where('status', 'completed');
            })->where('question_id', $question->id);

            $total = $answers->count();
            $correct = $answers->where('is_correct', true)->count();
            
            $stats[] = [
                'question_id' => $question->id,
                'question_text' => Str::limit($question->question_text, 50),
                'total_answers' => $total,
                'correct_answers' => $correct,
                'accuracy_rate' => $total > 0 ? round($correct / $total * 100, 1) : 0,
                'difficulty_rating' => $this->calculateDifficultyRating($correct, $total)
            ];
        }

        return collect($stats);
    }

    private function calculateDifficultyRating($correct, $total)
    {
        if ($total < 5) return 'Insufficient data';
        
        $accuracy = $correct / $total * 100;
        
        if ($accuracy >= 80) return 'Easy';
        if ($accuracy >= 60) return 'Medium';
        if ($accuracy >= 40) return 'Hard';
        return 'Very Hard';
    }

    // Accessors
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        return $minutes . ' minutes';
    }

    public function getStatusBadgeAttribute()
    {
        if (!$this->is_published) {
            return ['draft', 'bg-gray-100 text-gray-800'];
        }
        if (!$this->is_active) {
            return ['inactive', 'bg-red-100 text-red-800'];
        }
        if (!$this->isAvailable()) {
            return ['unavailable', 'bg-yellow-100 text-yellow-800'];
        }
        return ['available', 'bg-green-100 text-green-800'];
    }

    public function getDifficultyColorAttribute()
    {
        return match($this->difficulty_level) {
            'beginner' => 'green',
            'intermediate' => 'blue',
            'advanced' => 'orange',
            'expert' => 'red',
            default => 'gray'
        };
    }

    public function getExamTypeIconAttribute()
    {
        return match($this->exam_type) {
            'practice' => 'fas fa-dumbbell',
            'mock' => 'fas fa-file-alt',
            'final' => 'fas fa-graduation-cap',
            'certification' => 'fas fa-certificate',
            default => 'fas fa-clipboard-check'
        };
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->published()
                    ->active()
                    ->where(function($q) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                    })
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('exam_type', $type);
    }
}

