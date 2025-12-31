<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create pivot table for subject-class relationships
        Schema::create('class_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('my_class_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Prevent duplicate assignments
            $table->unique(['subject_id', 'my_class_id']);
        });
        
        // Create new pivot for subject-teacher with class specificity
        Schema::create('subject_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('my_class_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->boolean('is_general')->default(false); // true = teaches all classes for this subject
            $table->timestamps();
            
            // A teacher can teach same subject for different classes
            $table->unique(['subject_id', 'user_id', 'my_class_id']);
        });
        
        // Add legacy marker to subjects table
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('is_legacy')->default(false)->after('my_class_id');
            $table->foreignId('merged_into_subject_id')->nullable()->constrained('subjects')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['merged_into_subject_id']);
            $table->dropColumn(['is_legacy', 'merged_into_subject_id']);
        });
        
        Schema::dropIfExists('subject_teacher');
        Schema::dropIfExists('class_subject');
    }
};