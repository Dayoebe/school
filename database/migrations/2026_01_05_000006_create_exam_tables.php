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

        // Exams
        $createTable('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade')->onUpdate('cascade');
            $table->date('start_date');
            $table->date('stop_date');
            $table->boolean('active')->default(false);
            $table->boolean('publish_result')->default(false);
            $table->timestamps();
        });

        // Exam slots
        $createTable('exam_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('total_marks')->unsigned();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        // Exam records
        $createTable('exam_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('exam_slot_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->integer('student_marks')->unsigned()->nullable()->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'subject_id', 'section_id', 'exam_slot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_records');
        Schema::dropIfExists('exam_slots');
        Schema::dropIfExists('exams');
    }
};
