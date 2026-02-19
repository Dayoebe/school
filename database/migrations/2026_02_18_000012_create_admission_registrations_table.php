<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('my_class_id')->nullable()->constrained('my_classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();

            $table->string('reference_no')->unique();
            $table->string('student_name');
            $table->string('student_email')->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('birthday')->nullable();

            $table->string('guardian_name');
            $table->string('guardian_phone');
            $table->string('guardian_email')->nullable();
            $table->string('guardian_relationship')->nullable();

            $table->text('address')->nullable();
            $table->string('previous_school')->nullable();
            $table->text('notes')->nullable();

            $table->string('status')->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();

            $table->foreignId('enrolled_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('enrolled_student_record_id')->nullable()->constrained('student_records')->nullOnDelete();
            $table->timestamp('enrolled_at')->nullable();

            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_registrations');
    }
};
