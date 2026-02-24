<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastMessageRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'broadcast_message_id',
        'school_id',
        'user_id',
        'email',
        'phone',
        'channels',
        'portal_delivered_at',
        'email_delivered_at',
        'sms_delivered_at',
        'sms_status',
    ];

    protected $casts = [
        'channels' => 'array',
        'portal_delivered_at' => 'datetime',
        'email_delivered_at' => 'datetime',
        'sms_delivered_at' => 'datetime',
    ];

    public function broadcastMessage(): BelongsTo
    {
        return $this->belongsTo(BroadcastMessage::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
