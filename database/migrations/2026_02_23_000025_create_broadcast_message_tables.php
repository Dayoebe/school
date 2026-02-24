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

        $createTable('broadcast_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('target_type', 30)->default('school');
            $table->json('target_meta')->nullable();
            $table->boolean('send_portal')->default(true);
            $table->boolean('send_email')->default(false);
            $table->boolean('send_sms')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('sms_sent_at')->nullable();
            $table->string('sms_status', 30)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['school_id', 'created_at']);
            $table->index(['target_type', 'sent_at']);
        });

        $createTable('broadcast_message_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('broadcast_message_id')->constrained('broadcast_messages')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('channels')->nullable();
            $table->timestamp('portal_delivered_at')->nullable();
            $table->timestamp('email_delivered_at')->nullable();
            $table->timestamp('sms_delivered_at')->nullable();
            $table->string('sms_status', 30)->nullable();
            $table->timestamps();

            $table->unique(['broadcast_message_id', 'user_id'], 'broadcast_message_user_unique');
            $table->index(['user_id', 'portal_delivered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_message_recipients');
        Schema::dropIfExists('broadcast_messages');
    }
};
