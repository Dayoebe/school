<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assessment_student_locks')) {
            Schema::create('assessment_student_locks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['assessment_id', 'user_id'], 'assessment_student_lock_unique');
                $table->index(['school_id', 'assessment_id'], 'assessment_student_lock_school_assessment_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_student_locks');
    }
};
