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

        // Subjects
        $createTable('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name');
            $table->foreignId('school_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('my_class_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_general')->default(false);
            $table->boolean('is_legacy')->default(false);
            $table->foreignId('merged_into_subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['name', 'my_class_id']);
        });

        // Section subject pivot (for section-specific subjects)
        $createTable('section_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Class subject pivot (for general subjects assigned to classes)
        $createTable('class_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('my_class_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['subject_id', 'my_class_id']);
        });

        // Subject teacher pivot (replaces subject_user with class specificity)
        $createTable('subject_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('my_class_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->boolean('is_general')->default(false);
            $table->timestamps();
            $table->unique(['subject_id', 'user_id', 'my_class_id']);
        });

        // Student subject enrollment
        $createTable('student_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('my_class_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['student_record_id', 'subject_id']);
        });

        // Syllabi
        $createTable('syllabi', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->string('file');
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syllabi');
        Schema::dropIfExists('student_subject');
        Schema::dropIfExists('subject_teacher');
        Schema::dropIfExists('class_subject');
        Schema::dropIfExists('section_subject');
        Schema::dropIfExists('subjects');
    }
};
