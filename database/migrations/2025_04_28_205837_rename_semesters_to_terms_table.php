<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameSemestersToTermsTable extends Migration
{
    public function up(): void
    {
        Schema::rename('semesters', 'terms');
    }

    public function down(): void
    {
        Schema::rename('terms', 'semesters');
    }
}
