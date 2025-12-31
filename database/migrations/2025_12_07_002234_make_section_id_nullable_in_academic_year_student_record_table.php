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
        // Make section_id nullable in academic_year_student_record pivot table
        Schema::table('academic_year_student_record', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_year_student_record', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable(false)->change();
        });
    }
};