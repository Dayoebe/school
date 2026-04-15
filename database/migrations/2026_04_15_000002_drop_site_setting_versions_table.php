<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('site_setting_versions');

        Schema::table('site_settings', function (Blueprint $table): void {
            foreach (['published_version', 'draft_version', 'pending_version'] as $column) {
                if (Schema::hasColumn('site_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            if (!Schema::hasColumn('site_settings', 'published_version')) {
                $table->unsignedBigInteger('published_version')->default(0)->after('draft_settings');
            }

            if (!Schema::hasColumn('site_settings', 'draft_version')) {
                $table->unsignedBigInteger('draft_version')->default(0)->after('published_version');
            }

            if (!Schema::hasColumn('site_settings', 'pending_version')) {
                $table->unsignedBigInteger('pending_version')->nullable()->after('draft_version');
                $table->index('pending_version');
            }
        });

        Schema::create('site_setting_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_setting_id')->constrained()->cascadeOnDelete();
            $table->string('scope_key');
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('version_number');
            $table->string('stage')->default('draft');
            $table->json('settings');
            $table->json('meta')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['scope_key', 'version_number']);
            $table->index(['school_id', 'version_number']);
            $table->unique(['site_setting_id', 'version_number']);
        });
    }
};
