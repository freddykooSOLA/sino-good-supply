<?php

/**
 * SG-005: replace every slide/page screenshot or slide crop on the site with the
 * WHOLE picture from inside the source deck (see scripts/extract_whole_images.py).
 *
 * Touches only image fields; intros, titles and other admin edits are kept.
 * Idempotent. Run after the whole cuts exist in
 * storage/app/private/source-materials-staging/whole-cuts/.
 *
 *   php scripts/sg005_replace_slide_crops.php            (series + matching banners/heroes)
 *   php scripts/sg005_replace_slide_crops.php --local-preview
 *        (local only: also point the local hero slide + page heroes at the whole
 *         banners, mirroring production, so the hero layout can be checked)
 */

declare(strict_types=1);

use App\Models\Series;
use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cuts = storage_path('app/private/source-materials-staging/whole-cuts');
$disk = Storage::disk('public');

$copy = function (string $file, string $destDir) use ($cuts, $disk): string {
    $src = $cuts.DIRECTORY_SEPARATOR.$file;
    if (! is_file($src)) {
        throw new RuntimeException("Missing whole cut: {$src}");
    }
    $dest = "{$destDir}/{$file}";
    $disk->makeDirectory($destDir);
    File::copy($src, $disk->path($dest));

    return $dest;
};

// 1. Series that showed whole catalogue pages (hotel supplies) or page composites (JW profiles).
$series = [
    'boutique-guestroom-programme' => ['hotel-supplies', ['hotel-supplies_coffee-grounds-amenities.jpg', 'hotel-supplies_hilton-memory-foam-pillow.jpg', 'hotel-supplies_coffee-grounds-toiletries.jpg']],
    'lobby-public-area-collection' => ['hotel-supplies', ['hotel-supplies_acrylic-amenity-kit.jpg', 'hotel-supplies_coffee-grounds-toiletries.jpg', 'hotel-supplies_coffee-grounds-amenities.jpg']],
    'hotel-operating-supplies-series' => ['hotel-supplies', ['hotel-supplies_acrylic-amenity-kit.jpg', 'hotel-supplies_coffee-grounds-amenities.jpg', 'hotel-supplies_coffee-grounds-toiletries.jpg', 'hotel-supplies_hilton-memory-foam-pillow.jpg']],
    'jw-custom-extrusion-series' => ['jw-custom-profiles', ['jw-profiles_p08-room1.jpg', 'jw-profiles_p08-room2.jpg', 'jw-profiles_p08-room3.jpg', 'jw-profiles_p08-room4.jpg']],
    'architectural-trim-collection' => ['jw-custom-profiles', ['jw-profiles_p20-room1.jpg', 'jw-profiles_p20-room2.jpg', 'jw-profiles_p20-room3.jpg', 'jw-profiles_p20-room4.jpg']],
];

foreach ($series as $slug => [$category, $files]) {
    $record = Series::query()->where('slug_en', $slug)->first();
    if (! $record) {
        echo "WARN series not found: {$slug}".PHP_EOL;

        continue;
    }
    $paths = array_map(fn ($f) => $copy($f, "series/{$category}/{$slug}"), $files);
    $record->update(['images' => $paths]);
    echo "series {$slug}: ".count($paths).' whole images'.PHP_EOL;
}

// 2. Production banners / page heroes uploaded as slide screenshots -> whole deck pictures.
//    Keyed by the uploaded file's basename (production storage/banners, storage/page-heroes).
$heroMap = [
    '01KZ6CY3N9NV9ANM6457WTNPZ9.png' => 'banner_villa-dusk.jpg',
    '01KZ6DT4W1ZA6A8H94RRK5GQWD.png' => 'banner_villa-dusk.jpg',
    '01KZ6CY3NA9VY6HW5AMTRQF3MQ.png' => 'banner_rattan-lounge.jpg',
    '01KZ6DW8AF0NBRDN5PBVCACWHX.png' => 'banner_rattan-lounge.jpg',
    '01KZ6CY3NCVQN87N1CPTH81WH4.png' => 'banner_wall-panel-living.jpg',
    '01KZ6DT4W0M94RTM4AC9EEP0S8.png' => 'banner_wall-panel-living.jpg',
    '01KZ6CY3NB5KFG0V08H3CGY1ZM.png' => 'banner_infinity-pool.jpg',
    '01KZ6DW8AH0S73S0C9V63HVSGS.png' => 'banner_infinity-pool.jpg',
];

$swap = function (?string $path, string $dir) use ($heroMap, $copy): ?string {
    if (! $path || ! isset($heroMap[basename($path)])) {
        return $path;
    }

    return $copy($heroMap[basename($path)], $dir);
};

$slides = Setting::getValue('hero_slides', []) ?: [];
foreach ($slides as $i => $slide) {
    $slides[$i]['image'] = $swap($slide['image'] ?? null, 'banners');
}

$heroes = Setting::getValue('page_heroes', []) ?: [];
foreach ($heroes as $page => $hero) {
    if (is_array($hero)) {
        $heroes[$page]['image'] = $swap($hero['image'] ?? null, 'page-heroes');
    }
}

if (in_array('--local-preview', $argv, true)) {
    if (app()->environment('production')) {
        throw new RuntimeException('--local-preview is for local only');
    }
    $local = ['banner_villa-dusk.jpg', 'banner_rattan-lounge.jpg', 'banner_wall-panel-living.jpg', 'banner_infinity-pool.jpg'];
    foreach ($slides as $i => $slide) {
        $slides[$i]['image'] ??= $copy($local[$i % count($local)], 'banners');
    }
    foreach (['about' => 0, 'cases' => 2, 'products' => 1, 'contact' => 3] as $page => $n) {
        $heroes[$page] = array_merge(is_array($heroes[$page] ?? null) ? $heroes[$page] : [], [
            'image' => $copy($local[$n], 'page-heroes'),
        ]);
    }
}

Setting::setValue('hero_slides', $slides);
Setting::setValue('page_heroes', $heroes);
echo 'hero_slides / page_heroes images: '.json_encode([
    array_column($slides, 'image'),
    array_map(fn ($h) => is_array($h) ? ($h['image'] ?? null) : null, $heroes),
], JSON_UNESCAPED_SLASHES).PHP_EOL;
