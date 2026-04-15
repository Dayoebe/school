<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notices', function (Blueprint $table): void {
            $table->foreignId('created_by')->nullable()->after('school_id')->constrained('users')->nullOnDelete();
            $table->boolean('send_email')->default(false)->after('active');
            $table->string('email_subject')->nullable()->after('send_email');
            $table->text('email_body')->nullable()->after('email_subject');
            $table->json('email_recipient_roles')->nullable()->after('email_body');
            $table->timestamp('email_sent_at')->nullable()->after('email_recipient_roles');
            $table->unsignedInteger('email_recipient_count')->default(0)->after('email_sent_at');
        });

        Schema::create('notice_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['notice_id', 'user_id']);
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_reads');

        Schema::table('notices', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'created_by',
                'send_email',
                'email_subject',
                'email_body',
                'email_recipient_roles',
                'email_sent_at',
                'email_recipient_count',
            ]);
        });
    }
};
