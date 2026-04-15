<?php

namespace App\Models;

use App\Traits\InSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notice extends Model
{
    use HasFactory;
    use InSchool;

    protected $fillable = [
        'title',
        'content',
        'attachment',
        'start_date',
        'stop_date',
        'active',
        'send_email',
        'email_subject',
        'email_body',
        'email_recipient_roles',
        'email_sent_at',
        'email_recipient_count',
        'school_id',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'send_email' => 'boolean',
        'email_recipient_roles' => 'array',
        'email_sent_at' => 'datetime',
        'email_recipient_count' => 'integer',
        'start_date' => 'date',
        'stop_date' => 'date',
    ];

    public function scopeActive($query)
    {
        $query->where('start_date', '<=', date('Y-m-d'))
        ->where('stop_date', '>=', date('Y-m-d'))
        ->where('active', 1);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function readByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notice_reads')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    //used in view for displaying time on datatable
    public function getStartDateForHumansAttribute()
    {
        return \Carbon\Carbon::parse($this->start_date)->diffForHumans();
    }

    //used in view for displaying time on datatable
    public function getStopDateForHumansAttribute()
    {
        return \Carbon\Carbon::parse($this->stop_date)->diffForHumans();
    }
}
