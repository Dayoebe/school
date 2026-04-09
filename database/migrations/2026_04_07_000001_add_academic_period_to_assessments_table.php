<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assessments')) {
            return;
        }

        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('lesson_id');
            }

            if (!Schema::hasColumn('assessments', 'semester_id')) {
                $table->unsignedBigInteger('semester_id')->nullable()->after('academic_year_id');
            }
        });

        $this->ensureIndexes();
        $this->ensureForeignKeys();
    }

    public function down(): void
    {
        if (!Schema::hasTable('assessments')) {
            return;
        }

        Schema::table('assessments', function (Blueprint $table) {
            if ($this->hasConstraint('assessments', 'assessments_semester_id_foreign')) {
                $table->dropForeign('assessments_semester_id_foreign');
            }

            if ($this->hasConstraint('assessments', 'assessments_academic_year_id_foreign')) {
                $table->dropForeign('assessments_academic_year_id_foreign');
            }

            if (Schema::hasColumn('assessments', 'academic_year_id')) {
                $table->dropColumn('academic_year_id');
            }

            if (Schema::hasColumn('assessments', 'semester_id')) {
                $table->dropColumn('semester_id');
            }
        });
    }

    protected function ensureIndexes(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (
                !$this->hasIndex('assessments', 'assessments_academic_year_id_foreign')
                && !$this->hasConstraint('assessments', 'assessments_academic_year_id_foreign')
            ) {
                $table->index('academic_year_id', 'assessments_academic_year_id_foreign');
            }

            if (
                !$this->hasIndex('assessments', 'assessments_semester_id_foreign')
                && !$this->hasConstraint('assessments', 'assessments_semester_id_foreign')
            ) {
                $table->index('semester_id', 'assessments_semester_id_foreign');
            }
        });
    }

    protected function ensureForeignKeys(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (!$this->hasConstraint('assessments', 'assessments_academic_year_id_foreign')) {
                $table->foreign('academic_year_id', 'assessments_academic_year_id_foreign')
                    ->references('id')
                    ->on('academic_years')
                    ->nullOnDelete();
            }

            if (!$this->hasConstraint('assessments', 'assessments_semester_id_foreign')) {
                $table->foreign('semester_id', 'assessments_semester_id_foreign')
                    ->references('id')
                    ->on('semesters')
                    ->nullOnDelete();
            }
        });
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }

    protected function hasConstraint(string $table, string $constraintName): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('constraint_name', $constraintName)
            ->exists();
    }
};
