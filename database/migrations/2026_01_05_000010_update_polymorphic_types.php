<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('timetable_time_slot_weekday')) {
            return;
        }

        // Update timetable_time_slot_weekday polymorphic types to use full namespaces
        DB::table('timetable_time_slot_weekday')
            ->where('timetable_time_slot_weekdayable_type', 'subject')
            ->update(['timetable_time_slot_weekdayable_type' => 'App\Models\Subject']);

        DB::table('timetable_time_slot_weekday')
            ->where('timetable_time_slot_weekdayable_type', 'customTimetableItem')
            ->update(['timetable_time_slot_weekdayable_type' => 'App\Models\CustomTimetableItem']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('timetable_time_slot_weekday')) {
            return;
        }

        // Revert from full namespace to short names
        DB::table('timetable_time_slot_weekday')
            ->where('timetable_time_slot_weekdayable_type', 'App\Models\Subject')
            ->update(['timetable_time_slot_weekdayable_type' => 'subject']);

        DB::table('timetable_time_slot_weekday')
            ->where('timetable_time_slot_weekdayable_type', 'App\Models\CustomTimetableItem')
            ->update(['timetable_time_slot_weekdayable_type' => 'customTimetableItem']);
    }
};
