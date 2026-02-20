<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessments')) {
            Schema::table('assessments', function (Blueprint $table) {
                if (!Schema::hasColumn('assessments', 'results_published_at')) {
                    $table->timestamp('results_published_at')->nullable()->after('shuffle_options');
                }

                if (!Schema::hasColumn('assessments', 'results_published_by')) {
                    $table->foreignId('results_published_by')
                        ->nullable()
                        ->after('results_published_at')
                        ->constrained('users')
                        ->nullOnDelete();
                }
            });
        }

        if (!Schema::hasTable('assessment_attempt_sessions')) {
            Schema::create('assessment_attempt_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->unsignedInteger('attempt_number');
                $table->unsignedInteger('current_question_index')->default(0);
                $table->string('status', 30)->default('in_progress');
                // Use datetime to avoid legacy MySQL timestamp default issues.
                $table->dateTime('started_at');
                $table->dateTime('expires_at');
                $table->dateTime('completed_at')->nullable();
                $table->dateTime('last_activity_at')->nullable();
                $table->json('question_order')->nullable();
                $table->json('answers_snapshot')->nullable();
                $table->json('flagged_question_ids')->nullable();
                $table->json('security_violations')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->unique(
                    ['assessment_id', 'user_id', 'attempt_number'],
                    'assessment_attempt_sessions_attempt_unique'
                );
                $table->index(['assessment_id', 'user_id', 'status'], 'assessment_attempt_sessions_lookup_idx');
                $table->index(['school_id', 'status'], 'assessment_attempt_sessions_school_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_attempt_sessions');

        if (Schema::hasTable('assessments')) {
            Schema::table('assessments', function (Blueprint $table) {
                if (Schema::hasColumn('assessments', 'results_published_by')) {
                    $table->dropConstrainedForeignId('results_published_by');
                }

                if (Schema::hasColumn('assessments', 'results_published_at')) {
                    $table->dropColumn('results_published_at');
                }
            });
        }
    }
};
