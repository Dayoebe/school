<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'usage_area',
        'title',
        'alt_text',
        'disk',
        'path',
        'optimized_path',
        'thumbnails',
        'mime_type',
        'file_size',
        'width',
        'height',
        'uploaded_by',
    ];

    protected $casts = [
        'thumbnails' => 'array',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk ?: 'public')->url($this->path);
    }

    public function getOptimizedUrlAttribute(): ?string
    {
        if (!$this->optimized_path) {
            return null;
        }

        return Storage::disk($this->disk ?: 'public')->url($this->optimized_path);
    }

    public function getPreviewUrlAttribute(): string
    {
        return $this->optimized_url ?: $this->url;
    }
}
