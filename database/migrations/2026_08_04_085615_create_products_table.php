<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_zh');
            $table->string('name_zh_hant');
            $table->string('slug_en')->unique();
            $table->string('slug_zh')->unique();
            $table->string('slug_zh_hant')->unique();
            $table->text('short_desc_en')->nullable();
            $table->text('short_desc_zh')->nullable();
            $table->text('short_desc_zh_hant')->nullable();
            $table->longText('full_desc_en')->nullable();
            $table->longText('full_desc_zh')->nullable();
            $table->longText('full_desc_zh_hant')->nullable();
            $table->json('specs')->nullable();
            $table->json('images')->nullable();
            $table->string('pdf_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
