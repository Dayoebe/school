<?php

namespace App\Models;

use App\Traits\InSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionStatusHistory extends Model
{
    use HasFactory;
    use InSchool;

    protected $fillable = [
        'admission_registration_id',
        'school_id',
        'from_status',
        'to_status',
        'note',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function admissionRegistration(): BelongsTo
    {
        return $this->belongsTo(AdmissionRegistration::class);
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

