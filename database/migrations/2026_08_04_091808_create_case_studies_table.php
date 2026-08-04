<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('title_en');
            $table->string('title_zh');
            $table->string('title_zh_hant');
            $table->text('description_en')->nullable();
            $table->text('description_zh')->nullable();
            $table->text('description_zh_hant')->nullable();
            $table->string('youtube_url');
            $table->string('youtube_id')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_studies');
    }
};
