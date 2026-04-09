<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_START_YEAR = '2025';
    private const LEGACY_STOP_YEAR = '2026';
    private const LEGACY_TERM_NAME = 'second term';
    private const LEGACY_PERIOD_START = '2026-03-01 00:00:00';
    private const LEGACY_PERIOD_END = '2026-03-31 23:59:59';

    public function up(): void
    {
        if (!$this->hasRequiredSchema()) {
            return;
        }

        // Legacy CBT exams created before period-scoping was introduced all belong
        // to the 2025/2026 second term in the existing production data.
        DB::statement(<<<'SQL'
            UPDATE assessments AS assessments
            INNER JOIN my_classes AS my_classes
                ON my_classes.id = assessments.course_id
            INNER JOIN class_groups AS class_groups
                ON class_groups.id = my_classes.class_group_id
            INNER JOIN academic_years AS academic_years
                ON academic_years.school_id = class_groups.school_id
               AND academic_years.start_year = ?
               AND academic_years.stop_year = ?
            INNER JOIN semesters AS semesters
                ON semesters.school_id = class_groups.school_id
               AND semesters.academic_year_id = academic_years.id
               AND LOWER(TRIM(semesters.name)) = ?
            SET
                assessments.academic_year_id = academic_years.id,
                assessments.semester_id = semesters.id
            WHERE assessments.type = 'quiz'
              AND assessments.section_id IS NULL
              AND assessments.deleted_at IS NULL
              AND assessments.academic_year_id IS NULL
              AND assessments.semester_id IS NULL
              AND assessments.created_at BETWEEN ? AND ?
        SQL, [
            self::LEGACY_START_YEAR,
            self::LEGACY_STOP_YEAR,
            self::LEGACY_TERM_NAME,
            self::LEGACY_PERIOD_START,
            self::LEGACY_PERIOD_END,
        ]);
    }

    public function down(): void
    {
        if (!$this->hasRequiredSchema()) {
            return;
        }

        DB::statement(<<<'SQL'
            UPDATE assessments AS assessments
            INNER JOIN my_classes AS my_classes
                ON my_classes.id = assessments.course_id
            INNER JOIN class_groups AS class_groups
                ON class_groups.id = my_classes.class_group_id
            INNER JOIN academic_years AS academic_years
                ON academic_years.id = assessments.academic_year_id
               AND academic_years.school_id = class_groups.school_id
               AND academic_years.start_year = ?
               AND academic_years.stop_year = ?
            INNER JOIN semesters AS semesters
                ON semesters.id = assessments.semester_id
               AND semesters.school_id = class_groups.school_id
               AND semesters.academic_year_id = academic_years.id
               AND LOWER(TRIM(semesters.name)) = ?
            SET
                assessments.academic_year_id = NULL,
                assessments.semester_id = NULL
            WHERE assessments.type = 'quiz'
              AND assessments.section_id IS NULL
              AND assessments.deleted_at IS NULL
              AND assessments.created_at BETWEEN ? AND ?
        SQL, [
            self::LEGACY_START_YEAR,
            self::LEGACY_STOP_YEAR,
            self::LEGACY_TERM_NAME,
            self::LEGACY_PERIOD_START,
            self::LEGACY_PERIOD_END,
        ]);
    }

    protected function hasRequiredSchema(): bool
    {
        return Schema::hasTable('assessments')
            && Schema::hasTable('academic_years')
            && Schema::hasTable('semesters')
            && Schema::hasTable('my_classes')
            && Schema::hasTable('class_groups')
            && Schema::hasColumn('assessments', 'academic_year_id')
            && Schema::hasColumn('assessments', 'semester_id');
    }
};
