<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'scope_key',
        'school_id',
        'settings',
        'draft_settings',
        'published_version',
        'draft_version',
        'pending_version',
        'workflow_status',
        'draft_updated_at',
        'approval_requested_at',
        'approved_at',
        'rejected_at',
        'published_at',
        'draft_updated_by',
        'published_by',
        'approval_requested_by',
        'approved_by',
        'rejected_by',
        'rejection_note',
    ];

    protected $casts = [
        'settings' => 'array',
        'draft_settings' => 'array',
        'pending_version' => 'integer',
        'published_at' => 'datetime',
        'draft_updated_at' => 'datetime',
        'approval_requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SiteSettingVersion::class);
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function draftUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'draft_updated_by');
    }

    public function approvalRequestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public static function generalScopeKey(): string
    {
        return 'general';
    }

    public static function schoolScopeKey(int $schoolId): string
    {
        return 'school:' . $schoolId;
    }
}
