<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('questions')) {
            return;
        }

        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'question_media_disk')) {
                $table->string('question_media_disk')->nullable()->after('explanation');
            }

            if (!Schema::hasColumn('questions', 'question_media_path')) {
                $table->string('question_media_path')->nullable()->after('question_media_disk');
            }

            if (!Schema::hasColumn('questions', 'question_media_original_name')) {
                $table->string('question_media_original_name')->nullable()->after('question_media_path');
            }

            if (!Schema::hasColumn('questions', 'question_media_mime_type')) {
                $table->string('question_media_mime_type')->nullable()->after('question_media_original_name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('questions')) {
            return;
        }

        Schema::table('questions', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('questions', 'question_media_mime_type') ? 'question_media_mime_type' : null,
                Schema::hasColumn('questions', 'question_media_original_name') ? 'question_media_original_name' : null,
                Schema::hasColumn('questions', 'question_media_path') ? 'question_media_path' : null,
                Schema::hasColumn('questions', 'question_media_disk') ? 'question_media_disk' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
