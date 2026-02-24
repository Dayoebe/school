<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table): void {
            if (!Schema::hasColumn('site_settings', 'workflow_status')) {
                $table->string('workflow_status', 40)->default('draft')->after('draft_updated_by');
                $table->index('workflow_status');
            }

            if (!Schema::hasColumn('site_settings', 'pending_version')) {
                $table->unsignedBigInteger('pending_version')->nullable()->after('draft_version');
                $table->index('pending_version');
            }

            if (!Schema::hasColumn('site_settings', 'approval_requested_at')) {
                $table->timestamp('approval_requested_at')->nullable()->after('draft_updated_at');
            }

            if (!Schema::hasColumn('site_settings', 'approval_requested_by')) {
                $table->foreignId('approval_requested_by')->nullable()->after('published_by')
                    ->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            }

            if (!Schema::hasColumn('site_settings', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approval_requested_at');
            }

            if (!Schema::hasColumn('site_settings', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('approval_requested_by')
                    ->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            }

            if (!Schema::hasColumn('site_settings', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }

            if (!Schema::hasColumn('site_settings', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->after('approved_by')
                    ->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            }

            if (!Schema::hasColumn('site_settings', 'rejection_note')) {
                $table->text('rejection_note')->nullable()->after('rejected_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('site_settings', 'rejected_by')) {
                $table->dropConstrainedForeignId('rejected_by');
            }

            if (Schema::hasColumn('site_settings', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            if (Schema::hasColumn('site_settings', 'approval_requested_by')) {
                $table->dropConstrainedForeignId('approval_requested_by');
            }

            if (Schema::hasColumn('site_settings', 'pending_version')) {
                $table->dropIndex(['pending_version']);
            }

            if (Schema::hasColumn('site_settings', 'workflow_status')) {
                $table->dropIndex(['workflow_status']);
            }

            foreach ([
                'rejection_note',
                'rejected_at',
                'approved_at',
                'approval_requested_at',
                'pending_version',
                'workflow_status',
            ] as $column) {
                if (Schema::hasColumn('site_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
