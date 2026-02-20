<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('admission_registrations', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('notes');
            }
        });

        // Normalize legacy statuses to the new workflow.
        DB::table('admission_registrations')
            ->where('status', 'contacted')
            ->update(['status' => 'reviewed']);

        DB::table('admission_registrations')
            ->where('status', 'enrolled')
            ->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('admission_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('admission_registrations', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
        });

        DB::table('admission_registrations')
            ->where('status', 'reviewed')
            ->update(['status' => 'contacted']);

        DB::table('admission_registrations')
            ->where('status', 'approved')
            ->update(['status' => 'enrolled']);
    }
};

