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
        'is_general',
        'my_class_id',
    ];

    public function sections()
    {
        return $this->belongsToMany(Section::class);
    }
    public function myClass()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'subject_user');
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function timetableRecord(): MorphOne
    {
        return $this->morphOne(TimetableRecord::class, 'timetable_time_slot_weekdayable');
    }
public function assignToClassStudents($classId, $sectionId = null)
{
    $students = StudentRecord::where('my_class_id', $classId)
        ->when($sectionId, function ($query) use ($sectionId) {
            $query->where('section_id', $sectionId);
        })
        ->get();

    $syncData = [];
    foreach ($students as $student) {
        $syncData[$student->id] = [
            'my_class_id' => $classId,
            'section_id' => $sectionId,
        ];
    }

    $this->studentRecords()->syncWithoutDetaching($syncData);
}
    public function studentRecords()
    {
        return $this->belongsToMany(
            StudentRecord::class,
            'student_subject',
            'subject_id',
            'student_record_id'
        )->withPivot('my_class_id', 'section_id');
    }
    public function students()
    {
        return $this->belongsToMany(User::class, 'student_subject')
            ->withTimestamps()
            ->withPivot('my_class_id', 'section_id');
    }

}

