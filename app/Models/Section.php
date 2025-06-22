<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'my_class_id'];

    public function myClass()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function studentRecords()
    {
        return $this->hasMany(StudentRecord::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    public function students()
    {
        $students = User::students()->inSchool()->whereRelation('studentRecord.section', 'id', $this->id)->get();

        return $students;
    }
}
