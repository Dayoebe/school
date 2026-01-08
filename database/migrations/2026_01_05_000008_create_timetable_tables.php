<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Timetables
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->foreignId('semester_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('my_class_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        // Timetable time slots
        Schema::create('timetable_time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->time('start_time');
            $table->time('stop_time');
            $table->timestamps();
        });

        // Custom timetable items (for breaks, assemblies, etc.)
        Schema::create('custom_timetable_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('school_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['school_id', 'name']);
        });

        // Timetable time slot weekday pivot (polymorphic for subjects or custom items)
        Schema::create('timetable_time_slot_weekday', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_time_slot_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('weekday_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            
            // Polymorphic relationship - can be Subject or CustomTimetableItem
            $table->string('timetable_time_slot_weekdayable_type');
            $table->unsignedBigInteger('timetable_time_slot_weekdayable_id');
            
            $table->timestamps();
            $table->unique(['weekday_id', 'timetable_time_slot_id'], 'time_slot_weekday');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_time_slot_weekday');
        Schema::dropIfExists('custom_timetable_items');
        Schema::dropIfExists('timetable_time_slots');
        Schema::dropIfExists('timetables');
    }
};
