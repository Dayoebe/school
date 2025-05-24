<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Result;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentRecord extends Model
{
    use HasFactory;

    protected $fillable = ['admission_number', 'admission_date', 'my_class_id', 'section_id', 'user_id'];

    protected $casts = [
        'admission_date' => 'datetime:Y-m-d',
    ];

    protected static function booted()
    {
        static::addGlobalScope('notGraduated', function (Builder $builder) {
            $builder->where('is_graduated', 0);
        });

        static::created(function ($studentRecord) {
            $studentRecord->assignSubjectsAutomatically();
        });

        static::updated(function ($studentRecord) {
            $studentRecord->assignSubjectsAutomatically();
        });
    }

    public function getAdmissionDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }

    public function myClass()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function academicYears(): BelongsToMany
    {
        return $this->belongsToMany(AcademicYear::class)
            ->as('studentAcademicYearBasedRecords')
            ->using(AcademicYearStudentRecord::class)
            ->withPivot('my_class_id', 'section_id');
    }

    public function result()
    {
        return $this->belongsTo(Result::class);
    }

    public function currentAcademicYear()
    {
        return $this->academicYears()->wherePivot('academic_year_id', $this->user->school->academicYear->id);
    }
    public function studentSubjects()
    {
        return $this->belongsToMany(
            Subject::class,      // related model
            'student_subject',   // pivot table
            'user_id',        // foreign key on pivot table referencing StudentRecord (or User)
            'subject_id'         // foreign key on pivot table referencing Subject
        );
    }

    public function assignSubjectsAutomatically()
    {
        $subjects = Subject::where('my_class_id', $this->my_class_id)
            // ->where('section_id', $this->section_id) // remove this line
            ->pluck('id');

        $syncData = [];
        foreach ($subjects as $subjectId) {
            $syncData[$subjectId] = [
                'my_class_id' => $this->my_class_id,
                'section_id' => $this->section_id,
            ];
        }

        $this->studentSubjects()->syncWithoutDetaching($syncData);
    }
    public function results()
{
    return $this->hasMany(Result::class);
}

}
