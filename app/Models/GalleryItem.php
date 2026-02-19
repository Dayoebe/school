<?php

namespace App\Models;

use App\Traits\InSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryItem extends Model
{
    use HasFactory;
    use InSchool;

    protected $fillable = [
        'school_id',
        'gallery_category_id',
        'title',
        'caption',
        'media_url',
        'taken_on',
        'is_featured',
        'is_active',
        'sort_order',
        'uploaded_by',
    ];

    protected $casts = [
        'taken_on' => 'date',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function transformUrl(?string $url, string $transformation = 'f_auto,q_auto'): ?string
    {
        if (!$url || !str_contains($url, 'res.cloudinary.com')) {
            return $url;
        }

        if (str_contains($url, '/upload/' . $transformation . '/')) {
            return $url;
        }

        return str_replace('/upload/', '/upload/' . $transformation . '/', $url);
    }
}
