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
                if (!Schema::hasColumn('assessments', 'exam_published_at')) {
                    $table->timestamp('exam_published_at')->nullable();
                }

                if (!Schema::hasColumn('assessments', 'exam_published_by')) {
                    $table->foreignId('exam_published_by')
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessments')) {
            Schema::table('assessments', function (Blueprint $table) {
                if (Schema::hasColumn('assessments', 'exam_published_by')) {
                    $table->dropConstrainedForeignId('exam_published_by');
                }

                if (Schema::hasColumn('assessments', 'exam_published_at')) {
                    $table->dropColumn('exam_published_at');
                }
            });
        }
    }
};
