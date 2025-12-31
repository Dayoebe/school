<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('academic_year_student_record', function (Blueprint $table) {
            $table->unique(['student_record_id', 'academic_year_id', 'my_class_id'], 'student_year_class_unique');
        });
    }
    
    public function down()
    {
        Schema::table('academic_year_student_record', function (Blueprint $table) {
            $table->dropUnique('student_year_class_unique');
        });
    }
};
