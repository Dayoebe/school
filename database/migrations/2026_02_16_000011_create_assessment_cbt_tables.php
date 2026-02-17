<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assessments')) {
            Schema::create('assessments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id')->nullable();
                $table->unsignedBigInteger('section_id')->nullable();
                $table->unsignedBigInteger('lesson_id')->nullable();
                $table->string('title');
                $table->string('slug')->index();
                $table->text('description')->nullable();
                $table->string('type')->default('quiz');
                $table->unsignedInteger('pass_percentage')->default(70);
                $table->unsignedInteger('estimated_duration_minutes')->default(60);
                $table->timestamp('deadline')->nullable();
                $table->string('project_type')->nullable();
                $table->json('required_skills')->nullable();
                $table->json('deliverables')->nullable();
                $table->json('resources')->nullable();
                $table->boolean('is_mandatory')->default(false);
                $table->decimal('weight', 8, 2)->nullable();
                $table->boolean('allows_collaboration')->default(false);
                $table->json('evaluation_criteria')->nullable();
                $table->timestamp('due_date')->nullable();
                $table->unsignedInteger('max_score')->default(100);
                $table->longText('instructions')->nullable();
                $table->unsignedInteger('order')->default(1);
                $table->unsignedInteger('max_attempts')->nullable();
                $table->boolean('shuffle_questions')->default(false);
                $table->boolean('shuffle_options')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['type', 'section_id', 'lesson_id'], 'assessments_type_scope_idx');
            });
        }

        if (!Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
                $table->longText('question_text');
                $table->string('question_type')->default('multiple_choice');
                $table->json('options')->nullable();
                $table->json('correct_answers')->nullable();
                $table->decimal('points', 8, 2)->default(1);
                $table->longText('explanation')->nullable();
                $table->boolean('is_required')->default(true);
                $table->unsignedInteger('time_limit')->nullable();
                $table->unsignedInteger('order')->default(1);
                $table->string('difficulty_level')->nullable();
                $table->json('tags')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['assessment_id', 'order'], 'questions_assessment_order_idx');
            });
        }

        if (!Schema::hasTable('student_answers')) {
            Schema::create('student_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
                $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
                $table->unsignedInteger('attempt_number')->default(1);
                $table->text('answer')->nullable();
                $table->decimal('points_earned', 8, 2)->default(0);
                $table->boolean('is_correct')->default(false);
                $table->unsignedInteger('time_spent_seconds')->default(0);
                $table->timestamp('submitted_at')->nullable();
                $table->json('question_order')->nullable();
                $table->json('exam_data')->nullable();
                $table->timestamps();

                $table->unique(
                    ['user_id', 'assessment_id', 'question_id', 'attempt_number'],
                    'student_answers_attempt_question_unique'
                );
                $table->index(['assessment_id', 'attempt_number'], 'student_answers_assessment_attempt_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_answers');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('assessments');
    }
};
