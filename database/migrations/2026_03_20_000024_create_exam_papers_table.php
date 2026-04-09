<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_papers')) {
            Schema::create('exam_papers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('exam_id');
                $table->unsignedBigInteger('my_class_id');
                $table->unsignedBigInteger('subject_id');
                $table->string('title');
                $table->text('instructions')->nullable();
                $table->longText('typed_content')->nullable();
                $table->string('attachment_path')->nullable();
                $table->string('attachment_name')->nullable();
                $table->string('attachment_mime_type')->nullable();
                $table->unsignedBigInteger('attachment_size')->nullable();
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->unsignedBigInteger('published_by')->nullable();
                $table->timestamp('sealed_at')->nullable();
                $table->unsignedBigInteger('sealed_by')->nullable();
                $table->timestamps();
            });
        }

        $this->ensureIndexes();
        $this->ensureForeignKeys();
        $this->ensureUniqueConstraint();
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_papers');
    }

    protected function ensureIndexes(): void
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            if (
                !$this->hasIndex('exam_papers', 'exam_papers_exam_id_foreign')
                && !$this->hasConstraint('exam_papers', 'exam_papers_exam_id_foreign')
            ) {
                $table->index('exam_id', 'exam_papers_exam_id_foreign');
            }

            if (
                !$this->hasIndex('exam_papers', 'exam_papers_my_class_id_foreign')
                && !$this->hasConstraint('exam_papers', 'exam_papers_my_class_id_foreign')
            ) {
                $table->index('my_class_id', 'exam_papers_my_class_id_foreign');
            }

            if (
                !$this->hasIndex('exam_papers', 'exam_papers_subject_id_foreign')
                && !$this->hasConstraint('exam_papers', 'exam_papers_subject_id_foreign')
            ) {
                $table->index('subject_id', 'exam_papers_subject_id_foreign');
            }

            if (
                !$this->hasIndex('exam_papers', 'exam_papers_uploaded_by_foreign')
                && !$this->hasConstraint('exam_papers', 'exam_papers_uploaded_by_foreign')
            ) {
                $table->index('uploaded_by', 'exam_papers_uploaded_by_foreign');
            }

            if (
                !$this->hasIndex('exam_papers', 'exam_papers_published_by_foreign')
                && !$this->hasConstraint('exam_papers', 'exam_papers_published_by_foreign')
            ) {
                $table->index('published_by', 'exam_papers_published_by_foreign');
            }

            if (
                !$this->hasIndex('exam_papers', 'exam_papers_sealed_by_foreign')
                && !$this->hasConstraint('exam_papers', 'exam_papers_sealed_by_foreign')
            ) {
                $table->index('sealed_by', 'exam_papers_sealed_by_foreign');
            }
        });
    }

    protected function ensureForeignKeys(): void
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            if (!$this->hasConstraint('exam_papers', 'exam_papers_exam_id_foreign')) {
                $table->foreign('exam_id', 'exam_papers_exam_id_foreign')
                    ->references('id')
                    ->on('exams')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }

            if (!$this->hasConstraint('exam_papers', 'exam_papers_my_class_id_foreign')) {
                $table->foreign('my_class_id', 'exam_papers_my_class_id_foreign')
                    ->references('id')
                    ->on('my_classes')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }

            if (!$this->hasConstraint('exam_papers', 'exam_papers_subject_id_foreign')) {
                $table->foreign('subject_id', 'exam_papers_subject_id_foreign')
                    ->references('id')
                    ->on('subjects')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }

            if (!$this->hasConstraint('exam_papers', 'exam_papers_uploaded_by_foreign')) {
                $table->foreign('uploaded_by', 'exam_papers_uploaded_by_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            if (!$this->hasConstraint('exam_papers', 'exam_papers_published_by_foreign')) {
                $table->foreign('published_by', 'exam_papers_published_by_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            if (!$this->hasConstraint('exam_papers', 'exam_papers_sealed_by_foreign')) {
                $table->foreign('sealed_by', 'exam_papers_sealed_by_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }
        });
    }

    protected function ensureUniqueConstraint(): void
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            if (!$this->hasIndex('exam_papers', 'exam_papers_exam_class_subject_unique')) {
                $table->unique(['exam_id', 'my_class_id', 'subject_id'], 'exam_papers_exam_class_subject_unique');
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
