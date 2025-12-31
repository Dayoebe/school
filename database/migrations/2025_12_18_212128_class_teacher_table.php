<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('class_teacher')) {
            Schema::create('class_teacher', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_id')->constrained('my_classes')->onDelete('cascade');
                $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
                
                $table->unique(['class_id', 'teacher_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_teacher');
    }
};