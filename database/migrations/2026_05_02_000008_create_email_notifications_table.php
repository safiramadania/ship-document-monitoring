<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vessel_document_id')->nullable()->constrained()->nullOnDelete();
            $table->json('recipients');
            $table->json('cc')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->integer('threshold_days')->nullable();
            $table->date('sent_date')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(
                ['vessel_document_id', 'threshold_days', 'sent_date'],
                'email_notifications_document_threshold_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_notifications');
    }
};
