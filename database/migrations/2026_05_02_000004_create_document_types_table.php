<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('agency')->nullable();
            $table->string('category')->nullable();
            $table->boolean('required')->default(true);
            $table->boolean('permanent_allowed')->default(false);
            $table->integer('validity_months')->nullable();
            $table->integer('sort_order')->nullable();
            $table->json('aliases')->nullable();
            $table->json('keywords')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
