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
                if (!Schema::hasColumn('assessments', 'is_locked')) {
                    $table->boolean('is_locked')
                        ->default(false)
                        ->after('results_published_by');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessments')) {
            Schema::table('assessments', function (Blueprint $table) {
                if (Schema::hasColumn('assessments', 'is_locked')) {
                    $table->dropColumn('is_locked');
                }
            });
        }
    }
};
