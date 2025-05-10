<?php
// database/migrations/2025_04_28_000002_create_overall_results_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('overall_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->integer('total_score')->nullable();
            $table->integer('average_score')->nullable();
            $table->integer('class_position')->nullable();
            $table->string('attendance_number')->nullable();
            $table->text('class_teacher_comment')->nullable();
            $table->text('principal_comment')->nullable();
            $table->boolean('approved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overall_results');
    }
};
