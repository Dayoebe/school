<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User;
use App\Models\MyClass;
use App\Models\Subject;

class LearningSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'activity_type',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(MyClass::class, 'course_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Subject::class, 'lesson_id');
    }

    /**
     * Start a new learning session
     */
    public static function startSession($userId, $courseId = null, $lessonId = null, $activityType = 'lesson')
    {
        return static::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'lesson_id' => $lessonId,
            'started_at' => now(),
            'activity_type' => $activityType,
        ]);
    }

    /**
     * End the current session
     */
    public function endSession()
    {
        $this->update([
            'ended_at' => now(),
            'duration_minutes' => $this->started_at->diffInMinutes(now()),
        ]);

        return $this;
    }

    /**
     * Get active session for user
     */
    public static function getActiveSession($userId)
    {
        return static::where('user_id', $userId)
            ->whereNull('ended_at')
            ->latest()
            ->first();
    }

    /**
     * Get daily study time for user
     */
    public static function getDailyStudyTime($userId, $date = null)
    {
        $date = $date ?? now();
        
        return static::where('user_id', $userId)
            ->whereDate('started_at', $date)
            ->whereNotNull('ended_at')
            ->sum('duration_minutes');
    }

    /**
     * Get weekly study time for user
     */
    public static function getWeeklyStudyTime($userId, $startOfWeek = null)
    {
        $startOfWeek = $startOfWeek ?? now()->startOfWeek();
        
        return static::where('user_id', $userId)
            ->whereBetween('started_at', [$startOfWeek, $startOfWeek->copy()->endOfWeek()])
            ->whereNotNull('ended_at')
            ->sum('duration_minutes');
    }

    /**
     * Get study streak for user
     */
    public static function getStudyStreak($userId)
    {
        $streak = 0;
        $currentDate = now()->startOfDay();
        
        // Check if user studied today
        $hasActivityToday = static::where('user_id', $userId)
            ->whereDate('started_at', $currentDate)
            ->whereNotNull('ended_at')
            ->exists();
            
        if ($hasActivityToday) {
            $streak = 1;
            
            // Check previous days
            for ($i = 1; $i < 365; $i++) {
                $checkDate = $currentDate->copy()->subDays($i);
                
                $hasActivity = static::where('user_id', $userId)
                    ->whereDate('started_at', $checkDate)
                    ->whereNotNull('ended_at')
                    ->exists();
                    
                if ($hasActivity) {
                    $streak++;
                } else {
                    break;
                }
            }
        }
        
        return $streak;
    }

    /**
     * Scope for completed sessions
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('ended_at');
    }

    /**
     * Scope for active sessions
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration_minutes) {
            return 'Active';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return $minutes . 'm';
    }
}
