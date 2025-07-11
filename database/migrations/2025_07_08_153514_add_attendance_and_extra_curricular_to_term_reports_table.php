<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('term_reports', function (Blueprint $table) {
            // Add columns for attendance
            $table->integer('present_days')->nullable()->after('principal_comment');
            $table->integer('absent_days')->nullable()->after('present_days');

            // Add JSON columns for psychomotor, affective, and co-curricular traits
            // These will store arrays/objects as JSON strings
            $table->json('psychomotor_traits')->nullable()->after('absent_days');
            $table->json('affective_traits')->nullable()->after('psychomotor_traits');
            $table->json('co_curricular_activities')->nullable()->after('affective_traits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('term_reports', function (Blueprint $table) {
            $table->dropColumn([
                'present_days',
                'absent_days',
                'psychomotor_traits',
                'affective_traits',
                'co_curricular_activities',
            ]);
        });
    }
};

