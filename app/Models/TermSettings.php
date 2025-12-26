<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'semester_id',
        'my_class_id',
        'general_announcement',
        'resumption_date',
        'is_global',
    ];

    protected $casts = [
        'resumption_date' => 'date',
        'is_global' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function myClass()
    {
        return $this->belongsTo(MyClass::class);
    }

    /**
     * Get settings for a specific term and class
     * Falls back to global settings if no class-specific settings exist
     */
    public static function getForTermAndClass($academicYearId, $semesterId, $classId = null)
    {
        // First, try to get class-specific settings
        if ($classId) {
            $classSettings = self::where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->where('my_class_id', $classId)
                ->first();
            
            if ($classSettings) {
                return $classSettings;
            }
        }

        // Fall back to global settings
        return self::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('is_global', true)
            ->whereNull('my_class_id')
            ->first();
    }
}