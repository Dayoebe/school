<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'scope_key',
        'school_id',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
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
