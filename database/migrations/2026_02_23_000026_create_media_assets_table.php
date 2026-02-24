<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('media_assets')) {
            return;
        }

        Schema::create('media_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('usage_area', 40)->default('general'); // home/about/gallery/seo/general
            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('disk', 40)->default('public');
            $table->string('path');
            $table->string('optimized_path')->nullable();
            $table->json('thumbnails')->nullable();
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['school_id', 'usage_area']);
            $table->index(['school_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
