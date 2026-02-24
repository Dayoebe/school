<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_setting_versions')) {
            return;
        }

        Schema::create('site_setting_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_setting_id')->constrained('site_settings')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('scope_key');
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('version_number');
            $table->string('stage', 30); // draft_saved | published | rollback
            $table->json('settings');
            $table->json('meta')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['scope_key', 'version_number']);
            $table->index(['school_id', 'version_number']);
            $table->unique(['site_setting_id', 'version_number']);
        });

        if (Schema::hasTable('site_settings')) {
            DB::table('site_settings')
                ->orderBy('id')
                ->chunkById(200, function ($rows): void {
                    foreach ($rows as $row) {
                        if (empty($row->settings)) {
                            continue;
                        }

                        $version = (int) ($row->published_version ?? 0);
                        if ($version <= 0) {
                            $version = 1;
                        }

                        $exists = DB::table('site_setting_versions')
                            ->where('site_setting_id', $row->id)
                            ->where('version_number', $version)
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        DB::table('site_setting_versions')->insert([
                            'site_setting_id' => $row->id,
                            'scope_key' => $row->scope_key,
                            'school_id' => $row->school_id,
                            'version_number' => $version,
                            'stage' => 'published',
                            'settings' => $row->settings,
                            'meta' => json_encode(['seeded' => true]),
                            'changed_by' => $row->published_by,
                            'created_at' => $row->published_at ?? $row->updated_at ?? now(),
                            'updated_at' => $row->published_at ?? $row->updated_at ?? now(),
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_setting_versions');
    }
};
