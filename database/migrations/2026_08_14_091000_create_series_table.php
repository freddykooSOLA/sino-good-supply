<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_zh');
            $table->string('name_zh_hant');
            $table->string('slug_en')->unique();
            $table->string('slug_zh')->unique();
            $table->string('slug_zh_hant')->unique();
            $table->text('intro_en')->nullable();
            $table->text('intro_zh')->nullable();
            $table->text('intro_zh_hant')->nullable();
            $table->json('images')->nullable();
            $table->string('pdf_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
