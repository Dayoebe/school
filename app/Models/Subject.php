<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'short_name',
        'school_id',
        'my_class_id',
    ];

    public function myClass()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'subject_user');
    }

    public function result()
    {
        return $this->belongsTo(Result::class);
    }

    public function timetableRecord(): MorphOne
    {
        return $this->morphOne(TimetableRecord::class, 'timetable_time_slot_weekdayable');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_subject')
            ->withTimestamps()
            ->withPivot('my_class_id', 'section_id');
    }
}
