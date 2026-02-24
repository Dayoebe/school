<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table): void {
            if (!Schema::hasColumn('site_settings', 'draft_settings')) {
                $table->json('draft_settings')->nullable()->after('settings');
            }

            if (!Schema::hasColumn('site_settings', 'published_version')) {
                $table->unsignedBigInteger('published_version')->default(0)->after('draft_settings');
            }

            if (!Schema::hasColumn('site_settings', 'draft_version')) {
                $table->unsignedBigInteger('draft_version')->default(0)->after('published_version');
            }

            if (!Schema::hasColumn('site_settings', 'draft_updated_at')) {
                $table->timestamp('draft_updated_at')->nullable()->after('draft_version');
            }

            if (!Schema::hasColumn('site_settings', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('draft_updated_at');
            }

            if (!Schema::hasColumn('site_settings', 'draft_updated_by')) {
                $table->foreignId('draft_updated_by')->nullable()->after('published_at')
                    ->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            }

            if (!Schema::hasColumn('site_settings', 'published_by')) {
                $table->foreignId('published_by')->nullable()->after('draft_updated_by')
                    ->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            }
        });

        DB::table('site_settings')
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $settings = null;

                    if (!empty($row->settings)) {
                        $decoded = json_decode((string) $row->settings, true);
                        if (is_array($decoded)) {
                            $settings = $decoded;
                        }
                    }

                    DB::table('site_settings')
                        ->where('id', $row->id)
                        ->update([
                            'draft_settings' => $settings ? json_encode($settings) : null,
                            'published_version' => $settings ? max((int) $row->published_version, 1) : (int) $row->published_version,
                            'draft_version' => $settings ? max((int) $row->draft_version, 1) : (int) $row->draft_version,
                            'draft_updated_at' => $row->draft_updated_at ?? $row->updated_at,
                            'published_at' => $row->published_at ?? ($settings ? ($row->updated_at ?? now()) : null),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('site_settings', 'published_by')) {
                $table->dropConstrainedForeignId('published_by');
            }

            if (Schema::hasColumn('site_settings', 'draft_updated_by')) {
                $table->dropConstrainedForeignId('draft_updated_by');
            }

            foreach (['published_at', 'draft_updated_at', 'draft_version', 'published_version', 'draft_settings'] as $column) {
                if (Schema::hasColumn('site_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
