<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Series;
use App\Models\WatermarkSetting;
use App\Services\PdfWatermarkService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SeriesController extends Controller
{
    public function index(Request $request, string $locale, ?string $slug = null): View
    {
        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $activeCategory = null;

        $query = Series::query()
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

        $seriesList = $query->paginate(12)->withQueryString();
        $pageHero = page_hero('products');

        return view('pages.series-index', compact('seriesList', 'categories', 'activeCategory', 'pageHero'));
    }

    public function show(string $locale, string $slug): View
    {
        $series = Series::query()
            ->with('category')
            ->active()
            ->findByAnySlug($slug)
            ->firstOrFail();

        $related = Series::query()
            ->with('category')
            ->active()
            ->where('category_id', $series->category_id)
            ->where('id', '!=', $series->id)
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $pageHero = page_hero('products');
        $watermark = WatermarkSetting::current()->frontendConfig();

        return view('pages.series-show', compact('series', 'related', 'pageHero', 'watermark'));
    }

    public function pdf(string $locale, string $slug, PdfWatermarkService $service): BinaryFileResponse
    {
        $series = Series::query()
            ->active()
            ->findByAnySlug($slug)
            ->firstOrFail();

        abort_unless($series->hasPdf(), 404);

        $path = $service->generateWatermarkedPdf($series->pdfAbsolutePath());

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="catalogue-preview.pdf"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
