<?php

use App\Enums\ProcessingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vessel_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('upload_mode')->nullable()->index();
            $table->string('letter_number')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('issuer')->nullable();
            $table->boolean('is_permanent')->default(false);
            $table->string('validity_status')->nullable()->index();
            $table->string('processing_status')->default(ProcessingStatus::Pending->value)->index();
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->longText('ocr_text')->nullable();
            $table->decimal('classification_confidence', 5, 4)->nullable();
            $table->decimal('extraction_confidence', 5, 4)->nullable();
            $table->json('extracted_values')->nullable();
            $table->json('final_values')->nullable();
            $table->json('warnings')->nullable();
            $table->text('processing_error')->nullable();
            $table->text('external_link')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessel_documents');
    }
};
