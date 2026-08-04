<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CaseStudy;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$report = [
    'php' => PHP_VERSION,
    'laravel' => app()->version(),
    'env' => app()->environment(),
    'app_url' => config('app.url'),
    'db_ok' => false,
    'tables' => [],
    'counts' => [],
    'storage_writable' => is_writable(storage_path()) && is_writable(storage_path('logs')),
    'build_manifest' => file_exists(public_path('build/manifest.json')),
    'storage_link' => is_link(public_path('storage')) || file_exists(public_path('storage')),
];

try {
    DB::connection()->getPdo();
    $report['db_ok'] = true;
    $report['counts'] = [
        'users' => User::count(),
        'categories' => Category::count(),
        'products' => Product::count(),
        'case_studies' => CaseStudy::count(),
        'settings' => Setting::count(),
    ];
    foreach (['users', 'categories', 'products', 'settings', 'case_studies', 'migrations', 'sessions', 'cache', 'jobs'] as $table) {
        $report['tables'][$table] = Schema::hasTable($table);
    }
} catch (Throwable $e) {
    $report['db_error'] = $e->getMessage();
}

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
