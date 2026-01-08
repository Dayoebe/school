<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Student records
        Schema::create('student_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('admission_number')->nullable()->unique();
            $table->date('admission_date')->nullable();
            $table->foreignId('my_class_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('is_graduated')->default(false);
            $table->timestamps();
        });

        // Teacher records
        Schema::create('teacher_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        // Parent records
        Schema::create('parent_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'student_id']);
        });

        // Parent record user pivot (for multiple students per parent)
        Schema::create('parent_record_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_record_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['parent_record_id', 'user_id']);
            $table->timestamps();
        });

        // Academic year student record pivot
        Schema::create('academic_year_student_record', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('student_record_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('my_class_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
            $table->unique(['student_record_id', 'academic_year_id', 'my_class_id'], 'student_year_class_unique');
        });

        // Promotions
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_class_id')->constrained('my_classes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('new_class_id')->constrained('my_classes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('old_section_id')->nullable()->constrained('sections')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('new_section_id')->nullable()->constrained('sections')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade')->onUpdate('cascade');
            $table->json('students');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        // Graduations
        Schema::create('graduations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('graduation_class_id')->constrained('my_classes')->onDelete('cascade');
            $table->foreignId('graduation_section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->date('graduation_date');
            $table->string('certificate_number')->unique();
            $table->text('remarks')->nullable();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->index(['school_id', 'academic_year_id']);
            $table->index('student_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graduations');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('academic_year_student_record');
        Schema::dropIfExists('parent_record_user');
        Schema::dropIfExists('parent_records');
        Schema::dropIfExists('teacher_records');
        Schema::dropIfExists('student_records');
    }
};
