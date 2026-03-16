<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admission_registrations')) {
            return;
        }

        Schema::table('admission_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('admission_registrations', 'document_path')) {
                $table->string('document_path')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('admission_registrations', 'document_name')) {
                $table->string('document_name')->nullable()->after('document_path');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('admission_registrations')) {
            return;
        }

        Schema::table('admission_registrations', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('admission_registrations', 'document_path')) {
                $columns[] = 'document_path';
            }

            if (Schema::hasColumn('admission_registrations', 'document_name')) {
                $columns[] = 'document_name';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
