<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\MyClass;
use App\Models\Subject;

class UserProgress extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'assessment_id',
        'is_completed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(MyClass::class, 'course_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Subject::class, 'lesson_id');
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
