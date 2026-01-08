<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
        // Revert from full namespace to short names
        DB::table('timetable_time_slot_weekday')
            ->where('timetable_time_slot_weekdayable_type', 'App\Models\Subject')
            ->update(['timetable_time_slot_weekdayable_type' => 'subject']);

        DB::table('timetable_time_slot_weekday')
            ->where('timetable_time_slot_weekdayable_type', 'App\Models\CustomTimetableItem')
            ->update(['timetable_time_slot_weekdayable_type' => 'customTimetableItem']);
    }
};
