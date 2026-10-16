<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\SeoService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();
        Gate::policy(User::class, UserPolicy::class);

        View::composer('layouts.app', function ($view) {
            $request = request();
            if (! $request->routeIs('home', 'about', 'faq', 'cases.index', 'contact', 'order-process', 'products.index', 'category.show', 'series.show', 'product.show')) {
                return;
            }

            $seoService = app(SeoService::class);
            $seo = $seoService->resolve($request);
            $context = $request->attributes->get('seo_page_context');
            $view->with('seo', $seo);
            $view->with('structuredData', $seoService->structuredData($request, $seo, $context));
        });
    }
}
