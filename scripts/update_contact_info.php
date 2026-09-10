<?php

use App\Models\Setting;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$contact = Setting::getValue('contact', []) ?: [];

$updates = [
    'company_en' => 'SINO GOOD QY SUPPLY CHAIN CO., LTD',
    'company_zh' => '佛山兰悦君华贸易有限公司',
    'company_zh_hant' => '佛山蘭悅君華貿易有限公司',
    'address_en' => 'RM311, TOWER 4, HUACUI ROAD NO.26, H-T ECH CENTER',
    'address_zh' => '佛山市南海区桂城街道华翠南路26号汇泰创投中心4座3楼311',
    'address_zh_hant' => '佛山市南海區桂城街道華翠南路26號匯泰創投中心4座3樓311',
    'phone' => '+86 138 8993 1011',
    'whatsapp' => '+1 370 244 6468',
    'facebook_url' => 'https://www.facebook.com/search/top?q=SINO+GOOD+QY+SUPPLY+CHAIN',
    'instagram_url' => 'https://www.instagram.com/sino.good/',
    'youtube_url' => 'https://www.youtube.com/@Fionlam1105',
];

Setting::setValue('contact', array_merge($contact, $updates));

echo "contact-updated\n";
