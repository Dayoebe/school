<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $createTable = static function (string $tableName, callable $callback): void {
            if (!Schema::hasTable($tableName)) {
                Schema::create($tableName, $callback);
            }
        };

        // Fee categories
        $createTable('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Fees
        $createTable('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_category_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('name', 1024);
            $table->longText('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Fee invoices
        $createTable('fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->date('issue_date');
            $table->date('due_date');
            $table->longText('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Fee invoice records (line items)
        $createTable('fee_invoice_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_invoice_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('fee_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->integer('amount');
            $table->integer('waiver')->default(0);
            $table->integer('fine')->default(0);
            $table->integer('paid')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_invoice_records');
        Schema::dropIfExists('fee_invoices');
        Schema::dropIfExists('fees');
        Schema::dropIfExists('fee_categories');
    }
};
