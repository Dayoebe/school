<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('term_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->foreignId('my_class_id')->nullable()->constrained('my_classes')->onDelete('cascade');
            $table->text('general_announcement')->nullable();
            $table->date('resumption_date')->nullable();
            $table->boolean('is_global')->default(false); // true = applies to all classes
            $table->timestamps();
            
            // Ensure unique combination
            $table->unique(['academic_year_id', 'semester_id', 'my_class_id'], 'unique_term_class_settings');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_settings');
    }
};