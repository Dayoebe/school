<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'admission_status_histories';
    private const IDX_SCHOOL_STATUS = 'adm_hist_school_status_idx';
    private const IDX_REG_CREATED = 'adm_hist_reg_created_idx';

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->id();
                $table->foreignId('admission_registration_id')
                    ->constrained('admission_registrations')
                    ->cascadeOnDelete();
                $table->foreignId('school_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->text('note')->nullable();
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('changed_at')->nullable();
                $table->timestamps();

                // Keep index names short for MySQL identifier limits.
                $table->index(['school_id', 'to_status'], self::IDX_SCHOOL_STATUS);
                $table->index(['admission_registration_id', 'created_at'], self::IDX_REG_CREATED);
            });
        }

        // Recovery path for partially-applied migration (table exists, indexes missing).
        Schema::table(self::TABLE, function (Blueprint $table) {
            if (!$this->indexExists(self::TABLE, self::IDX_SCHOOL_STATUS)) {
                $table->index(['school_id', 'to_status'], self::IDX_SCHOOL_STATUS);
            }

            if (!$this->indexExists(self::TABLE, self::IDX_REG_CREATED)) {
                $table->index(['admission_registration_id', 'created_at'], self::IDX_REG_CREATED);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            return DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('index_name', $indexName)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }
};
