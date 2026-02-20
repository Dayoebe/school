<?php

namespace App\Models\Assessment;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttemptSession extends Model
{
    use HasFactory;

    protected $table = 'assessment_attempt_sessions';

    protected $fillable = [
        'assessment_id',
        'user_id',
        'school_id',
        'attempt_number',
        'current_question_index',
        'status',
        'started_at',
        'expires_at',
        'completed_at',
        'last_activity_at',
        'question_order',
        'answers_snapshot',
        'flagged_question_ids',
        'security_violations',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'question_order' => 'array',
        'answers_snapshot' => 'array',
        'flagged_question_ids' => 'array',
        'security_violations' => 'array',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->greaterThanOrEqualTo($this->expires_at);
    }
}

