<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if users table exists and add missing columns
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Add columns if they don't exist
                if (!Schema::hasColumn('users', 'gender')) {
                    $table->string('gender')->nullable()->after('email');
                }
                
                if (!Schema::hasColumn('users', 'birthday')) {
                    $table->date('birthday')->nullable()->after('gender');
                }
                
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone')->nullable()->after('birthday');
                }
                
                if (!Schema::hasColumn('users', 'address')) {
                    $table->text('address')->nullable()->after('phone');
                }
                
                if (!Schema::hasColumn('users', 'blood_group')) {
                    $table->string('blood_group')->nullable()->after('address');
                }
                
                if (!Schema::hasColumn('users', 'religion')) {
                    $table->string('religion')->nullable()->after('blood_group');
                }
                
                if (!Schema::hasColumn('users', 'nationality')) {
                    $table->string('nationality')->nullable()->after('religion');
                }
                
                if (!Schema::hasColumn('users', 'state')) {
                    $table->string('state')->nullable()->after('nationality');
                }
                
                if (!Schema::hasColumn('users', 'city')) {
                    $table->string('city')->nullable()->after('state');
                }
                
                if (!Schema::hasColumn('users', 'school_id')) {
                    $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
                }
                
                if (!Schema::hasColumn('users', 'locked')) {
                    $table->boolean('locked')->default(false)->after('school_id');
                }
                
                if (!Schema::hasColumn('users', 'profile_photo_path')) {
                    $table->string('profile_photo_path')->nullable()->after('locked');
                }
                
                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop columns in down method to preserve data
    }
};