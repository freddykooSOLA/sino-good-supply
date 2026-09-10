<?php

/**
 * SG-001: Wire real product images + intros into Series records.
 * Run: php scripts/integrate_series_images.php
 */

declare(strict_types=1);

use App\Models\Product;
use App\Models\Series;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

function logLine(array &$log, string $line): void
{
    $log[] = $line;
    echo $line.PHP_EOL;
}

$stagingRoot = storage_path('app/private/source-materials-staging');

/**
 * @return array<string, array{images: list<string>, intro_en: string, intro_zh: string, intro_zh_hant: string}>
 */
function seriesPlan(): array
{
    return [
        'boutique-guestroom-programme' => [
            'category' => 'hotel-supplies',
            'sources' => [
                'hotel-supplies/hotel-supplies_p0015_img1.jpg',
                'hotel-supplies/hotel-supplies_p0060_img1.jpg',
                'hotel-supplies/hotel-supplies_p0040_img1.jpg',
            ],
            'intro_en' => 'A coordinated guestroom programme from Guangdong Zhixincheng (K·SHORE / Tanina): biodegradable coffee-grounds amenity kits, hair-and-body-care miniatures (30–50ml), and Hilton Resorts Home dual-core memory-foam bedding — packaged as a private-label guestroom scheme for boutique hotels.',
            'intro_zh' => '广东挚心成（K·SHORE / 天丽悦焕）精品客房方案：可降解咖啡渣环保客房用品、30–50ML 洗护小瓶系列，以及双芯双感记忆棉床品，可按酒店品牌定制包装，成套交付客房配套。',
            'intro_zh_hant' => '廣東摯心成（K·SHORE / 天麗悅煥）精品客房方案：可降解咖啡渣環保客房用品、30–50ML 洗護小瓶系列，以及雙芯雙感記憶棉床品，可按酒店品牌定制包裝，成套交付客房配套。',
        ],
        'lobby-public-area-collection' => [
            'category' => 'hotel-supplies',
            'sources' => [
                'hotel-supplies/hotel-supplies_p0023_img1.jpg',
                'hotel-supplies/hotel-supplies_p0040_img1.jpg',
                'hotel-supplies/hotel-supplies_p0015_img1.jpg',
            ],
            'intro_en' => 'Public-area amenity upgrades for lobbies and lounge zones: acrylic-look translucent packaging for toothbrush kits, razors, combs and soap, coordinated with Zhixincheng\'s 28-year hotel-supplies manufacturing programme.',
            'intro_zh' => '面向大堂与公共休息区的客房用品升级线：亚克力质感通透包装的牙具、剃须刀、梳子与香皂套装，由挚心成 28 年酒店用品制造体系统筹供应。',
            'intro_zh_hant' => '面向大堂與公共休息區的客房用品升級線：亞克力質感通透包裝的牙具、剃鬚刀、梳子與香皂套裝，由摯心成 28 年酒店用品製造體系統籌供應。',
        ],
        'hotel-operating-supplies-series' => [
            'category' => 'hotel-supplies',
            'sources' => [
                'hotel-supplies/hotel-supplies_p0040_img1.jpg',
                'hotel-supplies/hotel-supplies_p0023_img1.jpg',
                'hotel-supplies/hotel-supplies_p0015_img1.jpg',
                'hotel-supplies/hotel-supplies_p0060_img1.jpg',
            ],
            'intro_en' => 'Repeatable hotel operating supplies from K·SHORE and Tanina: guest amenity kits, toiletry miniatures, slippers and bedding — QC, private-label packaging and export logistics under one programme.',
            'intro_zh' => 'K·SHORE / 天丽悦焕酒店营运物资：客房全套用品、洗护小瓶、拖鞋与床品，支持贴牌定制包装，品质管控与出口物流一体完成。',
            'intro_zh_hant' => 'K·SHORE / 天麗悅煥酒店營運物資：客房全套用品、洗護小瓶、拖鞋與床品，支持貼牌定制包裝，品質管控與出口物流一體完成。',
        ],
        'resort-pool-surround-series' => [
            'category' => 'swimming-pool',
            'sources' => [
                'pool/pool_p0026_img7.jpg',
                'pool/pool_p0012_img1.jpg',
            ],
            'intro_en' => 'MEXDA resort swim-spa systems for outdoor pool decks: acrylic shells roughly 10–12m with configurable jets, LED waterfall, temperature control, UV and ozone — specified as one outdoor leisure scheme for villa and resort surrounds.',
            'intro_zh' => '万事达 MEXDA 度假村户外泳池方案：约 10–12 米亚克力一体式游泳/按摩池，可配喷嘴、气泡按摩、带灯瀑布、温控、UV 与臭氧消毒，适用于别墅与度假酒店泳池周边工程。',
            'intro_zh_hant' => '萬事達 MEXDA 度假村戶外泳池方案：約 10–12 米亞克力一體式游泳/按摩池，可配噴嘴、氣泡按摩、帶燈瀑布、溫控、UV 與臭氧消毒，適用於別墅與度假酒店泳池周邊工程。',
        ],
        'indoor-leisure-pool-series' => [
            'category' => 'swimming-pool',
            'sources' => [
                'pool/pool_p0012_img1.jpg',
                'pool/pool_p0026_img7.jpg',
            ],
            'intro_en' => 'MEXDA swim-training and hydrotherapy hybrids for indoor wellness programmes: endless-pool lanes with air-bubble massage, pop-up jets and temperature control — tailored to climate-controlled hotel spa environments.',
            'intro_zh' => '万事达 MEXDA 室内休闲/康体泳池：游泳训练道与按摩理疗池一体，可配气泡按摩、升降喷嘴与温度控制，适用于温控环境下的酒店水疗项目。',
            'intro_zh_hant' => '萬事達 MEXDA 室內休閒/康體泳池：游泳訓練道與按摩理療池一體，可配氣泡按摩、升降噴嘴與溫度控制，適用於溫控環境下的酒店水療項目。',
        ],
        'hotel-corridor-panel-series' => [
            'category' => 'wall-panels',
            'sources' => [
                'wall-panel/wall-panel_p0039_img2.jpeg',
                'wall-panel/wall-panel_p0007_img3.jpg',
            ],
            'intro_en' => 'JIUWU corridor and lift-lobby wall systems: wood-powder and quartz-stone composite panels with food-contact-grade PP finish, ENF/E0 rated, U/V/seamless jointing for durable hotel circulation routes.',
            'intro_zh' => '九五护墙板酒店走廊系统：木粉+石英石粉基材、食品接触级 PP 饰面，ENF/E0 环保，U 缝/V 缝/无缝密拼，适用于走廊与电梯厅等高流量界面。',
            'intro_zh_hant' => '九五護牆板酒店走廊系統：木粉+石英石粉基材、食品接觸級 PP 飾面，ENF/E0 環保，U 縫/V 縫/無縫密拼，適用於走廊與電梯廳等高流量界面。',
        ],
        'feature-wall-collection' => [
            'category' => 'wall-panels',
            'sources' => [
                'wall-panel/wall-panel_p0007_img3.jpg',
                'wall-panel/wall-panel_p0039_img2.jpeg',
            ],
            'intro_en' => 'Statement feature walls with JIUWU panels: fabric-textured and wood-grain surfaces with backlit art inserts, fluted sections and smooth off-white finishes — customized to reception, restaurant and suite drawings.',
            'intro_zh' => '九五特色墙面方案：布纹与木纹护墙板、背光艺术画嵌入、格栅与纯色饰面组合，按接待处、餐厅与套房图纸定制，而非现货墙板推销。',
            'intro_zh_hant' => '九五特色牆面方案：布紋與木紋護牆板、背光藝術畫嵌入、格柵與純色飾面組合，按接待處、餐廳與套房圖紙定制，而非現貨牆板推銷。',
        ],
        'acoustic-interior-panel-series' => [
            'category' => 'wall-panels',
            'sources' => [
                'wall-panel/wall-panel_p0039_img2.jpeg',
                'wall-panel/wall-panel_p0007_img3.jpg',
            ],
            'intro_en' => 'JIUWU interior panels for quieter guest floors and meeting rooms: hollow and solid E-panels, carbon-crystal and A1 fire-rated boards with roughly five times standard impact resistance — coordinated as a decorative acoustic envelope.',
            'intro_zh' => '九五室内声学/装饰墙板：E 纯板、空心板、碳晶板与 A1 防火板，抗冲击约为普通板材五倍，可统筹用于客房楼层与会议室的更安静室内界面。',
            'intro_zh_hant' => '九五室內聲學/裝飾牆板：E 純板、空心板、碳晶板與 A1 防火板，抗衝擊約為普通板材五倍，可統籌用於客房樓層與會議室的更安靜室內界面。',
        ],
        'jw-custom-extrusion-series' => [
            'category' => 'jw-custom-profiles',
            'sources' => [
                'jw-profiles/jw-profiles_p0008_img1.jpeg',
            ],
            'intro_en' => 'JW custom extrusion profiles — moon-board (M50), half-moon (S7015), half-round (M128) and arc (S60) sections in wood-look finish, 3m lengths, for tea rooms, bedroom headboards and feature-wall framing.',
            'intro_zh' => 'JW 定制挤出型材：月牙板 M50、半月板 S7015、半圆板 M128、圆弧板 S60 等木纹饰面截面，标准 3 米/支，用于茶室、床头背景墙与电视墙收边。',
            'intro_zh_hant' => 'JW 定制擠出型材：月牙板 M50、半月板 S7015、半圓板 M128、圓弧板 S60 等木紋飾面截面，標準 3 米/支，用於茶室、床頭背景牆與電視牆收邊。',
        ],
        'architectural-trim-collection' => [
            'category' => 'jw-custom-profiles',
            'sources' => [
                'jw-profiles/jw-profiles_p0020_img1.jpeg',
            ],
            'intro_en' => 'JW architectural trim profiles — combination board (M196), flat/V board (M120V), wave board (M137) and mid board (M195) — supplied as matched perimeter trims for entry mirrors, marble accents and wet-area junctions.',
            'intro_zh' => 'JW 建筑收边型材：组合板 M196、平板/V 板 M120V、波浪板 M137、中板 M195 等，作为玄关镜面、大理石背景墙与湿区界面的配套收边组合。',
            'intro_zh_hant' => 'JW 建築收邊型材：組合板 M196、平板/V 板 M120V、波浪板 M137、中板 M195 等，作為玄關鏡面、大理石背景牆與濕區界面的配套收邊組合。',
        ],
        'luxury-bathroom-suite-series' => [
            'category' => 'bathroom-products',
            'sources' => [
                'bathroom/bathroom_slide004_img0.png',
                'bathroom/bathroom_slide006_img0.png',
            ],
            'intro_en' => 'OKASA luxury bathroom suites from Nanhai Gaotong (top-ten China brand): wood-slat floating double vanities, backlit LED mirrors, freestanding tubs and marble feature walls — luxury stone-art and Italian light-luxury series.',
            'intro_zh' => '欧凯莎奢华卫浴套系（南海高通，中国十大浴室柜品牌）：木格栅悬浮双盆柜、LED 背光镜、独立浴缸与大理石背景墙，涵盖奢石艺术与意式轻奢系列。',
            'intro_zh_hant' => '歐凱莎奢華衛浴套系（南海高通，中國十大浴室櫃品牌）：木格柵懸浮雙盆櫃、LED 背光鏡、獨立浴缸與大理石背景牆，涵蓋奢石藝術與意式輕奢系列。',
        ],
        'spa-bathroom-collection' => [
            'category' => 'bathroom-products',
            'sources' => [
                'bathroom/bathroom_slide006_img0.png',
                'bathroom/bathroom_slide004_img0.png',
            ],
            'intro_en' => 'OKASA spa-oriented bathrooms: burl-wood floating vanities with ceramic vessel basins, terracotta and marble wet-area finishes, backlit mirrors — specified for wellness hotels with fixed spec sheets per model.',
            'intro_zh' => '欧凯莎水疗卫浴：树瘤木悬浮浴室柜、陶瓷台上盆、赤陶与大理石湿区饰面、LED 背光镜，每款附标准参数表，适用于康体/水疗酒店项目。',
            'intro_zh_hant' => '歐凱莎水療衛浴：樹瘤木懸浮浴室櫃、陶瓷台上盆、赤陶與大理石濕區飾面、LED 背光鏡，每款附標準參數表，適用於康體/水療酒店項目。',
        ],
        'compact-hotel-bathroom-series' => [
            'category' => 'bathroom-products',
            'sources' => [
                'bathroom/bathroom_slide009_img0.png',
                'bathroom/bathroom_slide006_img0.png',
            ],
            'intro_en' => 'OKASA compact guest-room bathrooms: minimalist white schemes with black floating countertops, oval vessel basins, tall side cabinets and freestanding tubs — engineered for repeatable standard-room roll-outs.',
            'intro_zh' => '欧凯莎紧凑客房卫浴：极简黑白配色、悬浮台面、椭圆陶瓷盆、高柜与独立浴缸，围绕标准客房图纸与可复制开业交付来组织。',
            'intro_zh_hant' => '歐凱莎緊湊客房衛浴：極簡黑白配色、懸浮檯面、橢圓陶瓷盆、高櫃與獨立浴缸，圍繞標準客房圖紙與可複製開業交付來組織。',
        ],
        'terrace-lounge-series' => [
            'category' => 'indoor-outdoor-furniture',
            'sources' => [
                'unsorted-jiayi/jiayi-furniture_p0003_img1.jpeg',
                'unsorted-jiayi/jiayi-furniture_p0003_img2.jpeg',
                'unsorted-jiayi/jiayi-furniture_p0013_img1.jpeg',
                'unsorted-jiayi/jiayi-furniture_p0033_img2.jpeg',
                'unsorted-jiayi/jiayi-furniture_p0047_img1.jpeg',
                'unsorted-jiayi/jiayi-furniture_p0102_img2.jpeg',
            ],
            'intro_en' => 'JIAYI outdoor terrace lounge furniture — premium metal-rattan and cast-aluminium sofas, armchairs and chaise loungers (model codes JY-8xxx) for poolside decks, villa terraces and hotel outdoor bars.',
            'intro_zh' => '嘉翊 JIAYI 露台休闲户外家具：金属藤铁及压铸铝沙发、单椅与躺椅（型号 JY-8xxx 系列），适用于泳池甲板、别墅露台与酒店户外酒吧区。',
            'intro_zh_hant' => '嘉翊 JIAYI 露台休閒戶外家具：金屬藤鐵及壓鑄鋁沙發、單椅與躺椅（型號 JY-8xxx 系列），適用於泳池甲板、別墅露台與酒店戶外酒吧區。',
        ],
        // indoor-lobby-seating-series intentionally omitted — keep placeholders
    ];
}

function runSeriesImagesIntegration(): array
{
    $log = [];
    $logPath = storage_path('logs/series-integration.log');
    $stagingRoot = storage_path('app/private/source-materials-staging');

    logLine($log, '=== SG-001 Series Images Integration ===');
    logLine($log, 'Started: '.date('c'));

    try {
        Artisan::call('migrate', ['--force' => true]);
        logLine($log, 'Migrate output: '.trim(Artisan::output()));
    } catch (Throwable $e) {
        logLine($log, 'Migrate error: '.$e->getMessage());
    }

    $seriesCount = Series::query()->count();
    logLine($log, "Series count before seed: {$seriesCount}");
    if ($seriesCount === 0) {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\SeriesSeeder']);
        logLine($log, 'SeriesSeeder output: '.trim(Artisan::output()));
        $seriesCount = Series::query()->count();
        logLine($log, "Series count after seed: {$seriesCount}");
    }

    $productCount = Product::query()->count();
    logLine($log, "Products count: {$productCount} (legacy structure — not modified)");

    $publicStorage = public_path('storage');
    if (! is_link($publicStorage) && ! is_dir($publicStorage)) {
        Artisan::call('storage:link');
        logLine($log, 'storage:link output: '.trim(Artisan::output()));
    } else {
        logLine($log, 'public/storage already exists');
    }

    $plan = seriesPlan();
    $results = [];

    foreach ($plan as $slugEn => $config) {
        $series = Series::query()->where('slug_en', $slugEn)->first();
        if (! $series) {
            logLine($log, "WARN: Series not found: {$slugEn}");

            continue;
        }

        $categorySlug = $config['category'];
        $destDir = "series/{$categorySlug}/{$slugEn}";
        Storage::disk('public')->makeDirectory($destDir);

        $publicPaths = [];
        foreach ($config['sources'] as $sourceRelative) {
            $sourcePath = $stagingRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $sourceRelative);
            if (! is_file($sourcePath)) {
                logLine($log, "ERROR missing source: {$sourceRelative}");

                continue;
            }

            $basename = basename($sourcePath);
            $destRelative = "{$destDir}/{$basename}";

            if (! Storage::disk('public')->exists($destRelative)) {
                File::copy($sourcePath, Storage::disk('public')->path($destRelative));
            }

            $publicPaths[] = $destRelative;
            logLine($log, "Copied {$sourceRelative} -> {$destRelative}");
        }

        if ($publicPaths === []) {
            logLine($log, "SKIP update (no images): {$slugEn}");

            continue;
        }

        $series->update([
            'images' => $publicPaths,
            'intro_en' => $config['intro_en'],
            'intro_zh' => $config['intro_zh'],
            'intro_zh_hant' => $config['intro_zh_hant'],
        ]);

        $results[$slugEn] = $publicPaths;
        logLine($log, "Updated series: {$slugEn} (".count($publicPaths).' images)');
    }

    $lobby = Series::query()->where('slug_en', 'indoor-lobby-seating-series')->first();
    if ($lobby) {
        logLine($log, 'indoor-lobby-seating-series UNCHANGED (placeholder images retained): '.json_encode($lobby->images));
    }

    logLine($log, '=== Summary ===');
    logLine($log, 'Updated series: '.count($results));
    logLine($log, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    File::put($logPath, implode(PHP_EOL, $log).PHP_EOL);
    logLine($log, "Log written to {$logPath}");
    logLine($log, 'Finished: '.date('c'));

    return $results;
}

if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === realpath(__FILE__)) {
    require __DIR__.'/../vendor/autoload.php';
    $app = require __DIR__.'/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    runSeriesImagesIntegration();
}
