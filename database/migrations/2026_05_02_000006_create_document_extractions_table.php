<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_extractions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vessel_document_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->json('raw_ocr_response')->nullable();
            $table->json('classification_result')->nullable();
            $table->json('extracted_result')->nullable();
            $table->json('warnings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_extractions');
    }
};
