<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_settings')) {
            return;
        }

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('scope_key')->unique(); // general | school:{id}
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete()->cascadeOnUpdate();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
