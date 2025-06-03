<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('term_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');

            $table->text('principal_comment')->nullable();
            $table->text('class_teacher_comment')->nullable();

            $table->text('general_announcement')->nullable(); // For fees, resumption date, etc.
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
    }
};
