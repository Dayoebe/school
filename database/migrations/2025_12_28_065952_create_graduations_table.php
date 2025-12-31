<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('graduations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('graduation_class_id')->constrained('my_classes')->onDelete('cascade');
            $table->foreignId('graduation_section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->date('graduation_date');
            $table->string('certificate_number')->unique();
            $table->text('remarks')->nullable();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id']);
            $table->index('student_record_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduations');
    }
};