<?php

namespace App\Models;

use App\Traits\InSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BroadcastMessage extends Model
{
    use HasFactory;
    use InSchool;

    protected $fillable = [
        'school_id',
        'title',
        'body',
        'target_type',
        'target_meta',
        'send_portal',
        'send_email',
        'send_sms',
        'sent_at',
        'email_sent_at',
        'sms_sent_at',
        'sms_status',
        'created_by',
    ];

    protected $casts = [
        'target_meta' => 'array',
        'send_portal' => 'boolean',
        'send_email' => 'boolean',
        'send_sms' => 'boolean',
        'sent_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'sms_sent_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(BroadcastMessageRecipient::class);
    }
}
