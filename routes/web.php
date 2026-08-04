<?php

use App\Http\Controllers\CaseStudyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/en');

Route::prefix('{locale}')
    ->whereIn('locale', ['en', 'zh', 'zh-hant'])
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/about', [PageController::class, 'about'])->name('about');
        Route::get('/cases', [CaseStudyController::class, 'index'])->name('cases.index');
        Route::get('/contact', [PageController::class, 'contact'])->name('contact');
        Route::post('/contact', [PageController::class, 'sendContact'])->name('contact.send');
        Route::get('/category/{slug}', [ProductController::class, 'index'])->name('category.show');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
    });
