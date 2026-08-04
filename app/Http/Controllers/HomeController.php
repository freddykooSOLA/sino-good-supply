<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $slides = Setting::getValue('hero_slides', []);
        $stats = Setting::getValue('company_stats', []);
        $brands = Setting::getValue('brand_logos', []);
        $contact = Setting::getValue('contact', []);

        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $featuredProducts = Product::query()
            ->with('category')
            ->active()
            ->featured()
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        return view('pages.home', compact(
            'slides',
            'stats',
            'brands',
            'contact',
            'categories',
            'featuredProducts'
        ));
    }
}
