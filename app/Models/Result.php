<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_record_id',
        'subject_id',
        'academic_year_id',
        'semester_id',
        'test_score',
        'exam_score',
        'total_score',
        'subject_position',
        'teacher_comment',
        'approved',
    ];

    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
    public function validate()
{
    if (!$this->studentRecord || !$this->academicYearId || !$this->semesterId) {
        session()->flash('error', 'Invalid student record, academic year, or semester.');
        return false;
    }
    return true;
}
public static function calculateClassPositions($academicYearId, $semesterId, $classId)
{
    // Fetch all results for the given class, academic year, and semester
    $results = self::whereHas('studentRecord', function ($query) use ($classId) {
        $query->where('my_class_id', $classId);
    })
    ->where('academic_year_id', $academicYearId)
    ->where('semester_id', $semesterId)
    ->get();

    // Group results by student
    $studentTotals = $results->groupBy('student_record_id')->map(function ($studentResults) {
        return $studentResults->sum('total_score');
    });

    // Sort students by total score in descending order and assign positions
    $rankedStudents = $studentTotals->sortDesc()->values();

    $positions = [];
    $rankedStudents->each(function ($totalScore, $index) use (&$positions, $studentTotals) {
        $studentId = $studentTotals->search($totalScore);
        $positions[$studentId] = $index + 1; // Position starts from 1
    });

    // Update the positions in the database
    foreach ($results as $result) {
        $result->update(['subject_position' => $positions[$result->student_record_id]]);
    }

    return $positions;
}
}
