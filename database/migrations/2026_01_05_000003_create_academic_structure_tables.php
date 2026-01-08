<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Class groups
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('school_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['school_id', 'name']);
        });

        // My classes
        Schema::create('my_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('class_group_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['class_group_id', 'name']);
        });

        // Sections
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('my_class_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['name', 'my_class_id']);
        });

        // Academic years
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('start_year');
            $table->string('stop_year');
            $table->foreignId('school_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        // Semesters
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('academic_year_id')->nullable()->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('school_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('check_result')->default(false);
            $table->timestamps();
        });

        // Grade systems
        Schema::create('grade_systems', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('remark')->nullable();
            $table->string('grade_from');
            $table->string('grade_till');
            $table->foreignId('class_group_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        // Weekdays
        Schema::create('weekdays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Notices
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('attachment')->nullable();
            $table->date('start_date');
            $table->date('stop_date');
            $table->boolean('active')->default(true);
            $table->foreignId('school_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        // Term settings
        Schema::create('term_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->foreignId('my_class_id')->nullable()->constrained('my_classes')->onDelete('cascade');
            $table->text('general_announcement')->nullable();
            $table->date('resumption_date')->nullable();
            $table->boolean('is_global')->default(false);
            $table->timestamps();
            $table->unique(['academic_year_id', 'semester_id', 'my_class_id'], 'unique_term_class_settings');
        });

        // Class teacher pivot
        Schema::create('class_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('my_classes')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['class_id', 'teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_teacher');
        Schema::dropIfExists('term_settings');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('weekdays');
        Schema::dropIfExists('grade_systems');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('my_classes');
        Schema::dropIfExists('class_groups');
    }
};
