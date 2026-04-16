<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        $path = database_path('schema/baseline-schema.sql');

        if (! is_file($path)) {
            throw new RuntimeException("Missing baseline schema file: {$path}");
        }

        DB::unprepared(file_get_contents($path));
    }

    public function down(): void
    {
        $path = database_path('schema/baseline-schema.sql');

        if (! is_file($path)) {
            throw new RuntimeException("Missing baseline schema file: {$path}");
        }

        preg_match_all('/DROP TABLE IF EXISTS `([^`]+)`;/', file_get_contents($path), $matches);

        Schema::disableForeignKeyConstraints();

        foreach (array_reverse($matches[1]) as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }
};
