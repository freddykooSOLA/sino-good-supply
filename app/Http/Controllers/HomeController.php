<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $slides = Setting::getValue('hero_slides', []);
        $hero = hero_settings();
        $stats = Setting::getValue('company_stats', []) ?: [];
        $brands = Setting::getValue('brand_logos', []) ?: [];
        $contact = Setting::getValue('contact', []) ?: [];
        $coreBusiness = Setting::getValue('core_business', []) ?: [];
        $orderProcess = Setting::getValue('order_process', []) ?: [];
        $advantages = Setting::getValue('advantages', []) ?: [];
        $facebookPosts = array_slice(Setting::getValue('facebook_posts', []) ?: [], 0, 3);
        $latestNews = Setting::getValue('latest_news', []) ?: [];
        $visibleCategoryIds = Setting::getValue('visible_category_ids', []) ?: [];

        $categoryQuery = Category::query()
            ->active()
            ->with('previewSeries', 'previewProduct')
            ->orderBy('sort_order');

        if (! empty($visibleCategoryIds)) {
            $categoryQuery->whereIn('id', $visibleCategoryIds);
        }

        $categories = $categoryQuery->get();
        $featuredCases = $this->resolveFeaturedCases(Setting::getValue('featured_cases', []) ?: []);

        return view('pages.home', compact(
            'slides',
            'hero',
            'stats',
            'brands',
            'contact',
            'categories',
            'coreBusiness',
            'featuredCases',
            'orderProcess',
            'advantages',
            'facebookPosts',
            'latestNews',
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function resolveFeaturedCases(array $items): array
    {
        $ids = collect($items)->pluck('case_id')->filter()->unique()->all();
        $cases = $ids
            ? CaseStudy::query()->whereIn('id', $ids)->get()->keyBy('id')
            : collect();

        return collect($items)
            ->map(function (array $item) use ($cases) {
                $case = $cases->get($item['case_id'] ?? null);

                if ($case instanceof CaseStudy) {
                    $item['resolved_title'] = localized_setting($item, 'title') ?: $case->localizedTitle();
                    $item['resolved_image'] = $item['image'] ?? null;
                    $item['resolved_link'] = $item['link'] ?? $case->youtube_url;
                    $item['resolved_thumb'] = $case->thumbnailUrl();
                    $item['resolved_embed'] = $case->embedUrl();
                    $item['resolved_video'] = $case->uploadedVideoUrl();
                } else {
                    $item['resolved_title'] = localized_setting($item, 'title');
                    $item['resolved_image'] = $item['image'] ?? null;
                    $item['resolved_link'] = $item['link'] ?? null;
                    $item['resolved_thumb'] = null;
                    $item['resolved_embed'] = null;
                    $item['resolved_video'] = null;
                }

                return $item;
            })
            ->filter(fn (array $item) => filled($item['resolved_title']) || filled($item['resolved_image']) || filled($item['resolved_thumb']))
            ->values()
            ->all();
    }
}
