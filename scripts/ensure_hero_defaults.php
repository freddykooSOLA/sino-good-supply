<?php

use App\Models\Setting;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (! Setting::getValue('hero')) {
    Setting::setValue('hero', [
        'autoplay' => true,
        'speed' => 5000,
    ]);
}

if (! Setting::getValue('page_heroes')) {
    Setting::setValue('page_heroes', [
        'products' => [],
        'cases' => [],
        'about' => [],
        'contact' => [],
    ]);
}

$contact = Setting::getValue('contact', []) ?: [];
if (! array_key_exists('media_type', $contact)) {
    $contact['media_type'] = 'none';
    $contact['media_image'] = $contact['media_image'] ?? null;
    $contact['google_maps_embed'] = $contact['google_maps_embed'] ?? '';
    Setting::setValue('contact', $contact);
}

echo "defaults-ok\n";
