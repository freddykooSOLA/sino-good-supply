<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('series', function (Blueprint $table) {
            $table->boolean('watermark_images')->default(true)->after('pdf_path');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('watermark_images')->default(true)->after('pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('watermark_images');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('watermark_images');
        });
    }
};
