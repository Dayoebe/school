<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_subject', function (Blueprint $table) {
            // Remove old foreign key
            $table->dropForeign(['user_id']);
            
            // Add new foreign key
            $table->foreignId('student_record_id')->nullable()->constrained()->onDelete('cascade');
            
            // Remove unique constraint
            $table->dropUnique(['user_id', 'subject_id']);
            
            // Add new unique constraint
            $table->unique(['student_record_id', 'subject_id']);
        });
        
        // Copy data from user_id to student_record_id
        DB::statement('UPDATE student_subject ss
            JOIN student_records sr ON ss.user_id = sr.user_id
            SET ss.student_record_id = sr.id');
        
        Schema::table('student_subject', function (Blueprint $table) {
            // Remove old column
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
