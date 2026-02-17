<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\User;

// CbtResult.php
class CbtResult extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cbt_exam_id', 'user_id', 'session_id', 'attempt_number',
        'started_at', 'submitted_at', 'completed_at', 'time_spent_seconds',
        'time_remaining_seconds', 'auto_submitted', 'total_questions',
        'answered_questions', 'correct_answers', 'wrong_answers',
        'unanswered_questions', 'total_points', 'points_earned',
        'percentage_score', 'passed', 'grade', 'rank', 'status',
        'result_viewed', 'result_viewed_at', 'result_emailed',
        'result_emailed_at', 'certificate_eligible', 'answer_sequence',
        'time_analytics', 'browser_info', 'ip_address', 'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'auto_submitted' => 'boolean',
        'passed' => 'boolean',
        'result_viewed' => 'boolean',
        'result_viewed_at' => 'datetime',
        'result_emailed' => 'boolean',
        'result_emailed_at' => 'datetime',
        'certificate_eligible' => 'boolean',
        'answer_sequence' => 'array',
        'time_analytics' => 'array',
        'browser_info' => 'array',
        'percentage_score' => 'decimal:2',
        'total_points' => 'decimal:2',
        'points_earned' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($result) {
            if (empty($result->session_id)) {
                $result->session_id = Str::uuid();
            }
        });
    }

    // Relationships
    public function exam()
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(CbtAnswer::class);
    }

    // Methods
    public function calculateGrade()
    {
        $score = $this->percentage_score;
        
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }

    public function getFormattedTimeSpentAttribute()
    {
        $totalSeconds = $this->time_spent_seconds;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function updateAchievements()
    {
        if (
            $this->status === 'completed'
            && $this->passed
            && class_exists(\App\Models\Assessment\UserAchievement::class)
        ) {
            \App\Models\Assessment\UserAchievement::checkAndAwardAchievements($this->user_id);
        }
    }

    public function canShowResults()
    {
        $exam = $this->exam;
        
        if ($exam->show_results_immediately) {
            return true;
        }
        
        if ($exam->result_delivery === 'scheduled' && 
            $exam->result_release_date && 
            now()->gte($exam->result_release_date)) {
            return true;
        }
        
        return $exam->result_delivery === 'manual';
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePassed($query)
    {
        return $query->where('passed', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
