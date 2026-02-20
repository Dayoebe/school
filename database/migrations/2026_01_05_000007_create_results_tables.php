<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $createTable = static function (string $tableName, callable $callback): void {
            if (!Schema::hasTable($tableName)) {
                Schema::create($tableName, $callback);
            }
        };

        // Results (with CA scores)
        $createTable('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_record_id')->constrained('student_records')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');

            // CA scores (Continuous Assessment)
            $table->decimal('ca1_score', 5, 2)->default(0);
            $table->decimal('ca2_score', 5, 2)->default(0);
            $table->decimal('ca3_score', 5, 2)->default(0);
            $table->decimal('ca4_score', 5, 2)->default(0);

            // Exam score
            $table->decimal('exam_score', 5, 2)->default(0);

            // Computed fields
            $table->decimal('total_score', 5, 2)->default(0);
            $table->string('grade', 2)->nullable();
            $table->integer('class_position')->nullable();

            // Comments and approval
            $table->string('teacher_comment')->nullable();
            $table->boolean('approved')->default(false);

            $table->timestamps();

            $table->unique([
                'student_record_id',
                'subject_id',
                'academic_year_id',
                'semester_id'
            ], 'unique_result_entry');
        });

        // Term reports (end of term summary)
        $createTable('term_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');

            // Comments
            $table->text('principal_comment')->nullable();
            $table->text('class_teacher_comment')->nullable();

            // Attendance
            $table->integer('present_days')->nullable();
            $table->integer('absent_days')->nullable();

            // Behavioral assessments (stored as JSON)
            $table->json('psychomotor_traits')->nullable();
            $table->json('affective_traits')->nullable();
            $table->json('co_curricular_activities')->nullable();

            // General info
            $table->text('general_announcement')->nullable();
            $table->date('resumption_date')->nullable();

            $table->timestamps();

            $table->unique([
                'student_record_id',
                'academic_year_id',
                'semester_id',
            ], 'unique_term_report');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_reports');
        Schema::dropIfExists('results');
    }
};
