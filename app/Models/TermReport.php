<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TermReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_record_id',
        'academic_year_id',
        'semester_id',
        'class_teacher_comment',
        'principal_comment',
        'general_announcement',
        'resumption_date',
        'present_days',
        'absent_days',
        'psychomotor_traits',
        'affective_traits',
        'co_curricular_activities',
    ];

    public static $rules = [
        'student_record_id' => 'required|exists:student_records,id',
        'academic_year_id' => 'required|exists:academic_years,id',
        'semester_id' => 'required|exists:semesters,id',
        'class_teacher_comment' => 'nullable|string|max:500',
        'general_announcement' => 'nullable|string|max:500',
        'resumption_date' => 'nullable|date',
        'presentDays' => 'nullable|integer|min:0',
        'absentDays' => 'nullable|integer|min:0',
        'extraCurricularMark' => 'nullable|numeric|min:0|max:100',
        'psychomotorScores.*' => 'nullable|integer|min:0|max:5', // Changed from min:1 to min:0
        'affectiveScores.*' => 'nullable|integer|min:0|max:5',   // Changed from min:1 to min:0
        'coCurricularScores.*' => 'nullable|integer|min:0|max:5', // Changed from min:1 to min:0
        'psychomotor_traits' => 'nullable|json',
        'affective_traits' => 'nullable|json',
        'co_curricular_activities' => 'nullable|json',
    ];
    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the psychomotor traits as an array, decoding from JSON.
     *
     * @param  string|null  $value
     * @return array
     */
    public function getPsychomotorTraitsAttribute($value)
    {
        return $value ? json_decode($value, true) : $this->getDefaultPsychomotorScores();
    }

    /**
     * Get the affective traits as an array, decoding from JSON.
     *
     * @param  string|null  $value
     * @return array
     */
    public function getAffectiveTraitsAttribute($value)
    {
        return $value ? json_decode($value, true) : $this->getDefaultAffectiveScores();
    }

    /**
     * Get the co-curricular activities as an array, decoding from JSON.
     *
     * @param  string|null  $value
     * @return array
     */
    public function getCoCurricularActivitiesAttribute($value)
    {
        return $value ? json_decode($value, true) : $this->getDefaultCoCurricularScores();
    }

    /**
     * Initialize default psychomotor score structure.
     *
     * @return array
     */
    public static function getDefaultPsychomotorScores()
    {
        return [
            'Handwriting' => null,
            'Verbal Fluency' => null,
            'Game/Sports' => null,
            'Handling Tools' => null,
        ];
    }

    /**
     * Initialize default affective score structure.
     *
     * @return array
     */
    public static function getDefaultAffectiveScores()
    {
        return [
            'Punctuality' => null,
            'Neatness' => null,
            'Politeness' => null,
            'Leadership' => null,
        ];
    }

    /**
     * Initialize default co-curricular activities score structure.
     *
     * @return array
     */
    public static function getDefaultCoCurricularScores()
    {
        return [
            'Athletics' => null,
            'Football' => null,
            'Volley Ball' => null,
            'Table Tennis' => null,
        ];
    }
}
