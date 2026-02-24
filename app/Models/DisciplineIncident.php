<?php

namespace App\Models;

use App\Traits\InSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplineIncident extends Model
{
    use HasFactory;
    use InSchool;

    protected $fillable = [
        'school_id',
        'student_record_id',
        'incident_date',
        'category',
        'severity',
        'description',
        'action_taken',
        'parent_visible',
        'reported_by',
        'resolved_at',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'resolved_at' => 'datetime',
        'parent_visible' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function studentRecord(): BelongsTo
    {
        return $this->belongsTo(StudentRecord::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
