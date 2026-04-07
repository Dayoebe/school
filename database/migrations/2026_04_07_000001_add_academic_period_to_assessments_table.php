<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assessments')) {
            return;
        }

        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'academic_year_id')) {
                $table->foreignId('academic_year_id')
                    ->nullable()
                    ->after('lesson_id')
                    ->constrained('academic_years')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('assessments', 'semester_id')) {
                $table->foreignId('semester_id')
                    ->nullable()
                    ->after('academic_year_id')
                    ->constrained('semesters')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('assessments')) {
            return;
        }

        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'semester_id')) {
                $table->dropConstrainedForeignId('semester_id');
            }

            if (Schema::hasColumn('assessments', 'academic_year_id')) {
                $table->dropConstrainedForeignId('academic_year_id');
            }
        });
    }
};
