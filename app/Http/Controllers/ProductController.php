<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, string $locale, ?string $slug = null): View
    {
        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $activeCategory = null;

        $query = Product::query()
            ->with('category')
            ->active()
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($slug) {
            $activeCategory = Category::query()
                ->active()
                ->where(function ($q) use ($slug, $locale) {
                    $column = match ($locale) {
                        'zh' => 'slug_zh',
                        'zh-hant' => 'slug_zh_hant',
                        default => 'slug_en',
                    };
                    $q->where($column, $slug)->orWhere('slug_en', $slug);
                })
                ->firstOrFail();

            $query->where('category_id', $activeCategory->id);
        }

        $products = $query->paginate(12)->withQueryString();
        $pageHero = page_hero('products');

        return view('pages.products', compact('products', 'categories', 'activeCategory', 'pageHero'));
    }

    public function show(string $locale, string $slug): View
    {
        $product = Product::query()
            ->with('category')
            ->active()
            ->findByLocalizedSlug($slug, $locale)
            ->first();

        if (! $product) {
            $product = Product::query()
                ->with('category')
                ->active()
                ->where('slug_en', $slug)
                ->firstOrFail();
        }

        $related = Product::query()
            ->with('category')
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $pageHero = page_hero('products');

        return view('pages.single-product', compact('product', 'related', 'pageHero'));
    }
}
