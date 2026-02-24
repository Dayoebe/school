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

        $createTable('attendance_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('my_class_id')->constrained('my_classes')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete()->cascadeOnUpdate();
            $table->text('notes')->nullable();
            $table->foreignId('taken_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['school_id', 'attendance_date']);
            $table->index(['my_class_id', 'section_id']);
        });

        $createTable('attendance_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained('attendance_sessions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('student_record_id')->constrained('student_records')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status', 20)->default('present');
            $table->text('remark')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->unique(['attendance_session_id', 'student_record_id'], 'attendance_session_student_unique');
            $table->index(['student_record_id', 'status']);
        });

        $createTable('discipline_incidents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('student_record_id')->constrained('student_records')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('incident_date');
            $table->string('category', 120);
            $table->string('severity', 30)->default('medium');
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->boolean('parent_visible')->default(true);
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'incident_date']);
            $table->index(['severity', 'parent_visible']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_incidents');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');
    }
};
