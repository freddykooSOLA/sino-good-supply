<?php

use App\Http\Controllers\SeoAssetController;
use App\Http\Controllers\WatermarkedImageController;
use App\Http\Controllers\CaseStudyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SeriesController;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ShareSeoData;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/en');

Route::get('/sitemap.xml', [SeoAssetController::class, 'sitemap'])->name('sitemap');
Route::get('/llms.txt', [SeoAssetController::class, 'llms'])->name('llms');
Route::get('/robots.txt', [SeoAssetController::class, 'robots'])->name('robots');

Route::get('/media/wm/{encoded}', [WatermarkedImageController::class, 'show'])
    ->where('encoded', '[A-Za-z0-9\-_]+')
    ->name('media.watermarked');

Route::prefix('{locale}')
    ->whereIn('locale', ['en', 'zh', 'zh-hant'])
    ->middleware([SetLocale::class, ShareSeoData::class])
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/about', [PageController::class, 'about'])->name('about');
        Route::get('/faq', [PageController::class, 'faq'])->name('faq');
        Route::get('/cases', [CaseStudyController::class, 'index'])->name('cases.index');
        Route::get('/contact', [PageController::class, 'contact'])->name('contact');
        Route::post('/contact', [PageController::class, 'sendContact'])->name('contact.send');
        Route::get('/order-process', [PageController::class, 'orderProcess'])->name('order-process');
        Route::get('/category/{slug}', [SeriesController::class, 'index'])->name('category.show');
        Route::get('/products', [SeriesController::class, 'index'])->name('products.index');
        Route::get('/series/{slug}/pdf', [SeriesController::class, 'pdf'])->name('series.pdf');
        Route::get('/series/{slug}', [SeriesController::class, 'show'])->name('series.show');
        Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
    });
