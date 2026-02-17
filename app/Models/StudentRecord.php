<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Result;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StudentRecord extends Model
{
    protected $appends = ['student_subjects_count'];
    use HasFactory;

    protected $fillable = [
        'admission_number', 
        'admission_date', 
        'my_class_id', 
        'section_id', 
        'user_id',
        'is_graduated'
    ];

    protected $casts = [
        'admission_date' => 'datetime:Y-m-d',
        'is_graduated' => 'boolean',
    ];

    /**
     * Get the graduation record for this student
     */
    public function graduation(): HasOne
    {
        return $this->hasOne(Graduation::class);
    }

    /**
     * Check if student is graduated
     */
    public function isGraduated(): bool
    {
        return $this->is_graduated === true;
    }

    /**
     * Get graduation details if exists
     */
    public function getGraduationDetails()
    {
        return $this->graduation()
            ->with(['academicYear', 'graduationClass', 'graduationSection'])
            ->first();
    }

    /**
     * Scope to include only non-graduated students
     */
    public function scopeActive($query)
    {
        return $query->where('is_graduated', false);
    }

    /**
     * Scope to include only graduated students (Alumni)
     */
    public function scopeGraduated($query)
    {
        return $query->where('is_graduated', true);
    }

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

    public function getClassForYear($academicYearId)
    {
        $pivot = \DB::table('academic_year_student_record')
            ->where('student_record_id', $this->id)
            ->where('academic_year_id', $academicYearId)
            ->first();
        
        return $pivot ? MyClass::find($pivot->my_class_id) : $this->myClass;
    }

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
            ->with('sections')
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

    public function getClassForAcademicYear($academicYearId)
    {
        $record = $this->academicYears()
            ->where('academic_year_id', $academicYearId)
            ->first();
        
        return $record ? MyClass::find($record->pivot->my_class_id) : null;
    }

    public function getSectionForAcademicYear($academicYearId)
    {
        $record = $this->academicYears()
            ->where('academic_year_id', $academicYearId)
            ->first();
        
        return $record && $record->pivot->section_id 
            ? Section::find($record->pivot->section_id) 
            : null;
    }

    public function getAllAcademicYearRecords()
    {
        $records = $this->academicYears()
            ->with(['semesters'])
            ->orderBy('start_year', 'desc')
            ->get();
        
        $classIds = $records->pluck('pivot.my_class_id')->unique();
        $sectionIds = $records->pluck('pivot.section_id')->filter()->unique();
        
        $classes = MyClass::whereIn('id', $classIds)->get()->keyBy('id');
        $sections = Section::whereIn('id', $sectionIds)->get()->keyBy('id');
        
        return $records->map(function($year) use ($classes, $sections) {
            return [
                'academic_year' => $year,
                'class' => $classes->get($year->pivot->my_class_id),
                'section' => $year->pivot->section_id ? $sections->get($year->pivot->section_id) : null,
            ];
        });
    }

    public function wasInClassDuringYear($classId, $academicYearId)
    {
        return $this->academicYears()
            ->where('academic_year_id', $academicYearId)
            ->wherePivot('my_class_id', $classId)
            ->exists();
    }

    public function getResultsForAcademicYear($academicYearId)
    {
        return $this->results()
            ->whereHas('semester', function($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            })
            ->with(['semester', 'subject', 'exam'])
            ->get();
    }

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
                    'was_graduated' => $this->wasGraduatedInYear($year->id),
                ];
            });
    }

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
     * Check if student was graduated in a specific academic year
     */
    public function wasGraduatedInYear($academicYearId)
    {
        return $this->graduation()
            ->where('academic_year_id', $academicYearId)
            ->exists();
    }

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

    public function getTermReportsForYear($academicYearId)
    {
        return $this->termReports()
            ->where('academic_year_id', $academicYearId)
            ->with(['semester'])
            ->orderBy('semester_id')
            ->get();
    }

    public function hasRecordsForAcademicYear($academicYearId)
    {
        return $this->academicYears()
            ->where('academic_year_id', $academicYearId)
            ->exists();
    }

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

    public function scopeWithActiveUser($query)
    {
        return $query->whereHas('user', function($q) {
            $q->whereNull('deleted_at');
        })->with(['user' => function($query) {
            $query->whereNull('deleted_at');
        }]);
    }

    public function scopeOrderByName($query)
    {
        return $query->select('student_records.*')
            ->join('users', 'student_records.user_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->orderBy('users.name');
    }
}