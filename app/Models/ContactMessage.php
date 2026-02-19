<?php

namespace App\Models;

use App\Traits\InSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    use HasFactory;
    use InSchool;

    protected $fillable = [
        'school_id',
        'full_name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'read_at',
        'resolved_at',
        'handled_by',
        'response_note',
        'response_sent_at',
        'response_sent_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'resolved_at' => 'datetime',
        'response_sent_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function responseBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'response_sent_by');
    }
}
