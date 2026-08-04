<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            $table->string('video_type', 20)->default('youtube')->after('description_zh_hant');
            $table->string('video_path')->nullable()->after('youtube_id');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE case_studies MODIFY youtube_url VARCHAR(255) NULL');
        } else {
            Schema::table('case_studies', function (Blueprint $table) {
                $table->string('youtube_url')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE case_studies MODIFY youtube_url VARCHAR(255) NOT NULL');
        } else {
            Schema::table('case_studies', function (Blueprint $table) {
                $table->string('youtube_url')->nullable(false)->change();
            });
        }

        Schema::table('case_studies', function (Blueprint $table) {
            $table->dropColumn(['video_type', 'video_path']);
        });
    }
};
