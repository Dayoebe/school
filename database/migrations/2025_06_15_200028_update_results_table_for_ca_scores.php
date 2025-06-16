<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('results', function (Blueprint $table) {
            // Remove the old test_score column
            $table->dropColumn('test_score');
            
            // Add new CA columns
            $table->decimal('ca1_score', 5, 2)->default(0)->after('student_record_id');
            $table->decimal('ca2_score', 5, 2)->default(0)->after('ca1_score');
            $table->decimal('ca3_score', 5, 2)->default(0)->after('ca2_score');
            $table->decimal('ca4_score', 5, 2)->default(0)->after('ca3_score');
        });
    }
    
    public function down()
    {
        Schema::table('results', function (Blueprint $table) {
            $table->decimal('test_score', 5, 2)->default(0);
            $table->dropColumn(['ca1_score', 'ca2_score', 'ca3_score', 'ca4_score']);
        });
    }
};
