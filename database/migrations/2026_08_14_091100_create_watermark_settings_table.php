<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watermark_settings', function (Blueprint $table) {
            $table->id();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('size')->default(140);
            $table->unsignedTinyInteger('opacity')->default(18);
            $table->string('pattern')->default('tiled');
            $table->unsignedInteger('spacing')->default(90);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watermark_settings');
    }
};
