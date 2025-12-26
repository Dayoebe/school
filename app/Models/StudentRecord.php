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
    protected $appends = ['student_subjects_count'];
    use HasFactory;

    protected $fillable = ['admission_number', 'admission_date', 'my_class_id', 'section_id', 'user_id'];

    protected $casts = [
        'admission_date' => 'datetime:Y-m-d',
    ];


    public function myClass()
    {
        // Get current academic year
        $currentYearId = auth()->user()?->school?->academic_year_id;
        
        if ($currentYearId) {
            // Check if there's a record in the pivot table
            $pivot = \DB::table('academic_year_student_record')
                ->where('student_record_id', $this->id)
                ->where('academic_year_id', $currentYearId)
                ->first();
            
            if ($pivot) {
                return $this->belongsTo(MyClass::class, 'my_class_id')->where('id', $pivot->my_class_id);
            }
        }
        
        // Fallback to original relationship
        return $this->belongsTo(MyClass::class);
    }

    /**
     * Override section to be academic-year-aware
     */
    public function section()
    {
        // Get current academic year
        $currentYearId = auth()->user()?->school?->academic_year_id;
        
        if ($currentYearId) {
            // Check if there's a record in the pivot table
            $pivot = \DB::table('academic_year_student_record')
                ->where('student_record_id', $this->id)
                ->where('academic_year_id', $currentYearId)
                ->first();
            
            if ($pivot && $pivot->section_id) {
                return $this->belongsTo(Section::class, 'section_id')->where('id', $pivot->section_id);
            }
        }
        
        // Fallback to original relationship
        return $this->belongsTo(Section::class);
    }

    public function getAcademicYearClassAttribute()
    {
        $currentYearId = auth()->user()?->school?->academic_year_id;
        
        if (!$currentYearId) {
            return $this->myClass;
        }
        
        $pivot = \DB::table('academic_year_student_record')
            ->where('student_record_id', $this->id)
            ->where('academic_year_id', $currentYearId)
            ->first();
        
        return $pivot ? MyClass::find($pivot->my_class_id) : $this->myClass;
    }

    /**
     * Get the section for the current academic year
     */
    public function getAcademicYearSectionAttribute()
    {
        $currentYearId = auth()->user()?->school?->academic_year_id;
        
        if (!$currentYearId) {
            return $this->section;
        }
        
        $pivot = \DB::table('academic_year_student_record')
            ->where('student_record_id', $this->id)
            ->where('academic_year_id', $currentYearId)
            ->first();
        
        return $pivot && $pivot->section_id ? Section::find($pivot->section_id) : $this->section;
    }

    /**
 * Get class for a specific academic year
 */
public function getClassForYear($academicYearId)
{
    $pivot = \DB::table('academic_year_student_record')
        ->where('student_record_id', $this->id)
        ->where('academic_year_id', $academicYearId)
        ->first();
    
    return $pivot ? MyClass::find($pivot->my_class_id) : $this->myClass;
}

/**
 * Get section for a specific academic year
 */
public function getSectionForYear($academicYearId)
{
    $pivot = \DB::table('academic_year_student_record')
        ->where('student_record_id', $this->id)
        ->where('academic_year_id', $academicYearId)
        ->first();
    
    return $pivot && $pivot->section_id ? Section::find($pivot->section_id) : $this->section;
}
public function assignSubjectsAutomatically()
{
    $subjects = Subject::where('my_class_id', $this->my_class_id)
        ->when($this->section_id, function($query) {
            $query->where(function($q) {
                $q->where('is_general', true)
                  ->orWhereHas('sections', function($q) {
                      $q->where('sections.id', $this->section_id);
                  });
            });
        })
        ->get();

    $this->studentSubjects()->sync($subjects->pluck('id'));
}
/**
 * Get the student's class for a specific academic year
 */
public function getClassForAcademicYear($academicYearId)
{
    $record = $this->academicYears()
        ->where('academic_year_id', $academicYearId)
        ->first();
    
    return $record ? MyClass::find($record->pivot->my_class_id) : null;
}

/**
 * Get the student's section for a specific academic year
 */
public function getSectionForAcademicYear($academicYearId)
{
    $record = $this->academicYears()
        ->where('academic_year_id', $academicYearId)
        ->first();
    
    return $record && $record->pivot->section_id 
        ? Section::find($record->pivot->section_id) 
        : null;
}

/**
 * Get all academic years this student has records for
 */
public function getAllAcademicYearRecords()
{
    return $this->academicYears()
        ->with(['semesters'])
        ->orderBy('start_year', 'desc')
        ->get()
        ->map(function($year) {
            return [
                'academic_year' => $year,
                'class' => MyClass::find($year->pivot->my_class_id),
                'section' => $year->pivot->section_id ? Section::find($year->pivot->section_id) : null,
            ];
        });
}

/**
 * Check if student was in a specific class during an academic year
 */
public function wasInClassDuringYear($classId, $academicYearId)
{
    return $this->academicYears()
        ->where('academic_year_id', $academicYearId)
        ->wherePivot('my_class_id', $classId)
        ->exists();
}

/**
 * Get student's results for a specific academic year
 */
public function getResultsForAcademicYear($academicYearId)
{
    return $this->results()
        ->whereHas('semester', function($q) use ($academicYearId) {
            $q->where('academic_year_id', $academicYearId);
        })
        ->with(['semester', 'subject', 'exam'])
        ->get();
}

/**
 * Get student's complete academic history
 */
public function getAcademicHistory()
{
    return $this->academicYears()
        ->with(['semesters.exams'])
        ->orderBy('start_year', 'asc')
        ->get()
        ->map(function($year) {
            $classInfo = MyClass::find($year->pivot->my_class_id);
            $sectionInfo = $year->pivot->section_id ? Section::find($year->pivot->section_id) : null;
            
            return [
                'year' => $year->name,
                'year_id' => $year->id,
                'class' => $classInfo?->name,
                'section' => $sectionInfo?->name,
                'semesters' => $year->semesters->count(),
                'was_promoted' => $this->wasPromotedInYear($year->id),
            ];
        });
}

/**
 * Check if student was promoted during an academic year
 */
public function wasPromotedInYear($academicYearId)
{
    return Promotion::where('academic_year_id', $academicYearId)
        ->where(function($query) {
            $query->whereJsonContains('students', $this->user_id)
                  ->orWhereJsonContains('students', (string)$this->user_id);
        })
        ->exists();
}

/**
 * Get the promotion record for a specific academic year
 */
public function getPromotionForYear($academicYearId)
{
    return Promotion::where('academic_year_id', $academicYearId)
        ->where(function($query) {
            $query->whereJsonContains('students', $this->user_id)
                  ->orWhereJsonContains('students', (string)$this->user_id);
        })
        ->with(['oldClass', 'newClass', 'oldSection', 'newSection'])
        ->first();
}

/**
 * Get student's term reports for a specific academic year
 */
public function getTermReportsForYear($academicYearId)
{
    return $this->termReports()
        ->where('academic_year_id', $academicYearId)
        ->with(['semester'])
        ->orderBy('semester_id')
        ->get();
}

/**
 * Check if student has any records for an academic year
 */
public function hasRecordsForAcademicYear($academicYearId)
{
    return $this->academicYears()
        ->where('academic_year_id', $academicYearId)
        ->exists();
}

/**
 * Get student's subjects for a specific academic year
 * (subjects may vary by class/section)
 */
public function getSubjectsForAcademicYear($academicYearId)
{
    $record = $this->academicYears()
        ->where('academic_year_id', $academicYearId)
        ->first();
    
    if (!$record) {
        return collect();
    }
    
    $classId = $record->pivot->my_class_id;
    $sectionId = $record->pivot->section_id;
    
    $query = Subject::where('my_class_id', $classId);
    
    if ($sectionId) {
        $query->where(function($q) use ($sectionId) {
            $q->where('is_general', true)
              ->orWhereHas('sections', function($q) use ($sectionId) {
                  $q->where('sections.id', $sectionId);
              });
        });
    }
    
    return $query->get();
}


public function getStudentSubjectsCountAttribute()
{
    return $this->studentSubjects->count();
}
public function getSubjectsListAttribute()
{
    return $this->studentSubjects
        ->pluck('name')
        ->join(', ');
}

    public function studentSubjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'student_subject',
            'student_record_id',
            'subject_id'
        )->withPivot('my_class_id', 'section_id');
    }

    public function getFilteredSubjects()
    {
        if (!$this->section_id) {
            return Subject::where('my_class_id', $this->my_class_id)->get();
        }

        return Subject::where('my_class_id', $this->my_class_id)
            ->where(function($query) {
                $query->where('is_general', true)
                      ->orWhereHas('sections', function($q) {
                          $q->where('sections.id', $this->section_id);
                      });
            })
            ->get();
    }

    protected static function booted()
    {
        static::addGlobalScope('notGraduated', function (Builder $builder) {
            $builder->where('is_graduated', 0);
        });

        
        static::created(function ($studentRecord) {
            $studentRecord->assignSubjectsAutomatically();
        });
    
        static::updated(function ($studentRecord) {
            if ($studentRecord->isDirty(['my_class_id', 'section_id'])) {
                $studentRecord->assignSubjectsAutomatically();
            }
        });
    }
    public function getAdmissionDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
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
    /**
 * Scope to include only students with non-deleted users
 */
public function scopeWithActiveUser($query)
{
    return $query->whereHas('user', function($q) {
        $q->whereNull('deleted_at');
    })->with(['user' => function($query) {
        $query->whereNull('deleted_at');
    }]);
}
// /**
//  * Scope a query to order by student name.
//  */
// public function scopeOrderByName($query)
// {
//     return $query->select('student_records.*')
//                 ->join('users', function($join) {
//                     $join->on('student_records.user_id', '=', 'users.id')
//                          ->whereNull('users.deleted_at');
//                 })
//                 ->orderBy('users.name');
// }
public function scopeOrderByName($query)
{
    return $query->select('student_records.*')
        ->join('users', 'student_records.user_id', '=', 'users.id')
        ->whereNull('users.deleted_at')
        ->orderBy('users.name');
}
}
