<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_status_histories', function (Blueprint $table) {
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

            $table->index(['school_id', 'to_status']);
            $table->index(['admission_registration_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_status_histories');
    }
};

