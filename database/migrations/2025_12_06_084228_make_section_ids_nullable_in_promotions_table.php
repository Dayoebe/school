<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->foreignId('old_section_id')->nullable()->change();
            $table->foreignId('new_section_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->foreignId('old_section_id')->nullable(false)->change();
            $table->foreignId('new_section_id')->nullable(false)->change();
        });
    }
};