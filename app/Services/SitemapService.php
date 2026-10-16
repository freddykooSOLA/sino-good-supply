<?php

namespace App\Services;

use App\Models\CaseStudy;
use App\Models\Category;
use App\Models\Product;
use App\Models\Series;
use App\Models\Setting;
use Illuminate\Support\Carbon;

class SitemapService
{
    /**
     * @return list<array{loc: string, lastmod: string, changefreq: string, priority: string}>
     */
    public function urls(): array
    {
        $entries = [];
        $staticPaths = ['', 'about', 'faq', 'cases', 'contact', 'order-process', 'products'];

        foreach (SeoService::LOCALES as $locale) {
            foreach ($staticPaths as $path) {
                $entries[] = $this->entry(locale_url($path, $locale), now());
            }
        }

        $categories = Category::query()->active()->get();
        foreach ($categories as $category) {
            foreach (SeoService::LOCALES as $locale) {
                $entries[] = $this->entry(
                    locale_url('category/'.$category->localizedSlug($locale), $locale),
                    $category->updated_at ?? now()
                );
            }
        }

        $series = Series::query()->active()->get();
        foreach ($series as $item) {
            foreach (SeoService::LOCALES as $locale) {
                $entries[] = $this->entry(
                    locale_url('series/'.$item->localizedSlug($locale), $locale),
                    $item->updated_at ?? now()
                );
            }
        }

        $products = Product::query()->active()->get();
        foreach ($products as $item) {
            foreach (SeoService::LOCALES as $locale) {
                $entries[] = $this->entry(
                    locale_url('product/'.$item->localizedSlug($locale), $locale),
                    $item->updated_at ?? now()
                );
            }
        }

        $latestCaseAt = CaseStudy::query()->active()->max('updated_at');
        if ($latestCaseAt) {
            foreach (SeoService::LOCALES as $locale) {
                foreach ($entries as $index => $entry) {
                    if (str_ends_with($entry['loc'], '/'.$locale.'/cases')) {
                        $entries[$index]['lastmod'] = Carbon::parse($latestCaseAt)->toAtomString();
                    }
                }
            }
        }

        $news = Setting::getValue('latest_news', []) ?: [];
        foreach (is_array($news) ? $news : [] as $item) {
            if (! is_array($item) || empty($item['link'])) {
                continue;
            }
            $link = (string) $item['link'];
            if (! str_starts_with($link, url('/'))) {
                continue;
            }
            $entries[] = $this->entry($link, isset($item['date']) ? Carbon::parse($item['date']) : now());
        }

        return $entries;
    }

    public function xml(): string
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
        foreach ($this->urls() as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.e($url['loc']).'</loc>';
            $lines[] = '    <lastmod>'.e($url['lastmod']).'</lastmod>';
            $lines[] = '    <changefreq>'.e($url['changefreq']).'</changefreq>';
            $lines[] = '    <priority>'.e($url['priority']).'</priority>';
            $lines[] = '  </url>';
        }
        $lines[] = '</urlset>';

        return implode("\n", $lines);
    }

    /**
     * @return array{loc: string, lastmod: string, changefreq: string, priority: string}
     */
    protected function entry(string $loc, mixed $lastmod): array
    {
        $date = $lastmod instanceof Carbon ? $lastmod : Carbon::parse($lastmod);

        return [
            'loc' => $loc,
            'lastmod' => $date->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.7',
        ];
    }
}
