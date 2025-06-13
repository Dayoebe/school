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
    
    public function scopeOrderByName($query)
    {
        return $query->join('users', 'student_records.user_id', '=', 'users.id')
                    ->orderBy('users.name')
                    ->select('student_records.*');
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

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function currentAcademicYear()
    {
        return $this->academicYears()->wherePivot('academic_year_id', $this->user->school->academicYear->id);
    }

    public function studentSubjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'student_subject',
            'user_id',
            'subject_id'
        );
    }

    public function assignSubjectsAutomatically()
    {
        $subjects = Subject::where('my_class_id', $this->my_class_id)
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

    public function termReports()
    {
        return $this->hasMany(TermReport::class);
    }

    public function termReportFor($academicYearId, $semesterId)
    {
        return $this->termReports()
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->first();
    }
}
