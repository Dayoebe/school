<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSettingVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_setting_id',
        'scope_key',
        'school_id',
        'version_number',
        'stage',
        'settings',
        'meta',
        'changed_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'meta' => 'array',
    ];

    public function siteSetting(): BelongsTo
    {
        return $this->belongsTo(SiteSetting::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
