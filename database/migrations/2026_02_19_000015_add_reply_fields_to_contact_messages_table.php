<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contact_messages')) {
            return;
        }

        Schema::table('contact_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_messages', 'response_note')) {
                $table->text('response_note')->nullable()->after('handled_by');
            }

            if (!Schema::hasColumn('contact_messages', 'response_sent_at')) {
                $table->timestamp('response_sent_at')->nullable()->after('response_note');
            }

            if (!Schema::hasColumn('contact_messages', 'response_sent_by')) {
                $table->foreignId('response_sent_by')
                    ->nullable()
                    ->after('response_sent_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('contact_messages')) {
            return;
        }

        Schema::table('contact_messages', function (Blueprint $table) {
            if (Schema::hasColumn('contact_messages', 'response_sent_by')) {
                $table->dropConstrainedForeignId('response_sent_by');
            }

            if (Schema::hasColumn('contact_messages', 'response_sent_at')) {
                $table->dropColumn('response_sent_at');
            }

            if (Schema::hasColumn('contact_messages', 'response_note')) {
                $table->dropColumn('response_note');
            }
        });
    }
};
