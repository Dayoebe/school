<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_record_id')->constrained('student_records')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');

            $table->decimal('test_score', 5, 2)->default(0);   // max 40
            $table->decimal('exam_score', 5, 2)->default(0);   // max 60
            $table->decimal('total_score', 5, 2)->default(0); // computed in application logic
            $table->string('grade', 2)->nullable();
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
    }
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
    
};
