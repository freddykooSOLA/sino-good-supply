<?php

namespace Database\Seeders;

use App\Models\WatermarkSetting;
use Illuminate\Database\Seeder;

class WatermarkSettingsSeeder extends Seeder
{
    public function run(): void
    {
        WatermarkSetting::query()->firstOrCreate([], [
            'image_path' => null,
            'size' => 140,
            'opacity' => 18,
            'pattern' => WatermarkSetting::PATTERN_TILED,
            'spacing' => 90,
        ]);
    }
}
