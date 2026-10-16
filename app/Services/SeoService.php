<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Series;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeoService
{
    public const LOCALES = ['en', 'zh', 'zh-hant'];

    /**
     * @return array{title: string, description: string, canonical: string, og_image: ?string, robots: ?string, hreflang: array<string, string>, breadcrumbs: list<array{name: string, url: string}>}
     */
    public function resolve(Request $request): array
    {
        $locale = (string) ($request->route('locale') ?? 'en');
        $routeName = (string) ($request->route()?->getName() ?? '');
        $canonical = url($request->path());
        $breadcrumbs = [['name' => __('messages.breadcrumb_home'), 'url' => locale_url('', $locale)]];
        $ogImage = $this->defaultOgImage();
        $title = 'SINO GOOD';
        $description = (string) __('messages.tagline');

        match ($routeName) {
            'home' => [$title, $description] = $this->pageCopy('home', $locale),
            'about' => [$title, $description, $breadcrumbs] = $this->withCrumb($locale, 'about', __('messages.about_title'), __('messages.about_intro')),
            'faq' => [$title, $description, $breadcrumbs] = $this->withCrumb($locale, 'faq', __('messages.faq_title'), __('messages.faq_intro')),
            'cases.index' => [$title, $description, $breadcrumbs] = $this->withCrumb($locale, 'cases', __('messages.cases_title'), __('messages.cases_intro')),
            'contact' => [$title, $description, $breadcrumbs] = $this->withCrumb($locale, 'contact', __('messages.contact_us'), __('messages.get_in_touch')),
            'order-process' => [$title, $description, $breadcrumbs] = $this->withCrumb($locale, 'order-process', __('messages.order_process_title'), __('messages.order_process_intro')),
            'products.index', 'category.show' => [$title, $description, $breadcrumbs] = $this->productsMeta($request, $locale),
            'series.show' => [$title, $description, $breadcrumbs, $ogImage] = $this->seriesMeta($request, $locale),
            'product.show' => [$title, $description, $breadcrumbs, $ogImage] = $this->productMeta($request, $locale),
            default => null,
        };

        if (isset($title) && ! str_contains($title, 'SINO GOOD')) {
            $title = $title.' — SINO GOOD';
        }

        return [
            'title' => $title,
            'description' => Str::limit(strip_tags($description), 160, ''),
            'canonical' => $canonical,
            'og_image' => $ogImage,
            'robots' => null,
            'hreflang' => $this->hreflangAlternates($request),
            'breadcrumbs' => $breadcrumbs,
        ];
    }

    public function faqIsPublished(): bool
    {
        return (bool) Setting::getValue('faq_published', false);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function structuredData(Request $request, array $seo, mixed $pageContext = null): array
    {
        $graphs = [];
        $graphs[] = $this->organizationSchema();

        $routeName = (string) ($request->route()?->getName() ?? '');
        if ($routeName === 'home') {
            $graphs[] = $this->websiteSchema();
            $graphs = array_merge($graphs, $this->dailyArticleSchemas());
        }

        if (count($seo['breadcrumbs'] ?? []) > 1) {
            $graphs[] = $this->breadcrumbSchema($seo['breadcrumbs']);
        }

        if ($routeName === 'faq' && $this->faqIsPublished()) {
            $graphs[] = $this->faqPageSchema();
        }

        if ($routeName === 'series.show' && $pageContext instanceof Series) {
            $graphs[] = $this->productSchema($pageContext);
        }

        if (in_array($routeName, ['products.index', 'category.show'], true)) {
            $seriesList = $pageContext;
            if ($seriesList) {
                $graphs[] = $this->itemListSchema($seriesList);
            }
        }

        return $graphs;
    }

    /**
     * @return array<string, string>
     */
    public function hreflangAlternates(Request $request): array
    {
        $segments = $request->segments();
        if (! empty($segments) && in_array($segments[0], self::LOCALES, true)) {
            array_shift($segments);
        }
        $suffix = $segments === [] ? '' : '/'.implode('/', $segments);
        $alternates = [];
        foreach (self::LOCALES as $locale) {
            $alternates[$locale] = url('/'.$locale.$suffix);
        }
        $alternates['x-default'] = url('/en'.$suffix);

        return $alternates;
    }

    protected function pageCopy(string $page, string $locale): array
    {
        $title = (string) __("messages.seo.{$page}.title");
        $description = (string) __("messages.seo.{$page}.description");

        return [$title, $description];
    }

    /**
     * @return array{0: string, 1: string, 2: list<array{name: string, url: string}>}
     */
    protected function withCrumb(string $locale, string $path, string $title, string $description): array
    {
        return [
            $title,
            $description,
            [
                ['name' => __('messages.breadcrumb_home'), 'url' => locale_url('', $locale)],
                ['name' => $title, 'url' => locale_url($path, $locale)],
            ],
        ];
    }

    /**
     * @return array{0: string, 1: string, 2: list<array{name: string, url: string}>}
     */
    protected function productsMeta(Request $request, string $locale): array
    {
        $slug = $request->route('slug');
        $title = __('messages.all_series');
        $description = __('messages.tagline');
        $breadcrumbs = [
            ['name' => __('messages.breadcrumb_home'), 'url' => locale_url('', $locale)],
            ['name' => $title, 'url' => locale_url('products', $locale)],
        ];

        if ($slug) {
            $category = Category::query()->active()->where(function ($q) use ($slug, $locale) {
                $column = match ($locale) {
                    'zh' => 'slug_zh',
                    'zh-hant' => 'slug_zh_hant',
                    default => 'slug_en',
                };
                $q->where($column, $slug)->orWhere('slug_en', $slug);
            })->first();

            if ($category) {
                $title = $category->localizedName($locale);
                $description = __('messages.tagline');
                $breadcrumbs[] = ['name' => $title, 'url' => locale_url('category/'.$category->localizedSlug($locale), $locale)];
            }
        }

        return [$title, $description, $breadcrumbs];
    }

    /**
     * @return array{0: string, 1: string, 2: list<array{name: string, url: string}>, 3: ?string}
     */
    protected function seriesMeta(Request $request, string $locale): array
    {
        $slug = (string) $request->route('slug');
        $series = Series::query()->active()->findByAnySlug($slug)->first();
        $title = __('messages.all_series');
        $description = __('messages.tagline');
        $breadcrumbs = [
            ['name' => __('messages.breadcrumb_home'), 'url' => locale_url('', $locale)],
            ['name' => __('messages.all_series'), 'url' => locale_url('products', $locale)],
        ];
        $ogImage = $this->defaultOgImage();

        if ($series) {
            $title = $series->localizedName($locale);
            $description = $series->localizedIntro($locale) ?: $description;
            $ogImage = $this->absoluteImage($series->firstImagePath()) ?? $ogImage;
            if ($series->category) {
                $breadcrumbs[] = [
                    'name' => $series->category->localizedName($locale),
                    'url' => locale_url('category/'.$series->category->localizedSlug($locale), $locale),
                ];
            }
            $breadcrumbs[] = ['name' => $title, 'url' => locale_url('series/'.$series->localizedSlug($locale), $locale)];
        }

        return [$title, $description, $breadcrumbs, $ogImage];
    }

    /**
     * @return array{0: string, 1: string, 2: list<array{name: string, url: string}>, 3: ?string}
     */
    protected function productMeta(Request $request, string $locale): array
    {
        $slug = (string) $request->route('slug');
        $product = Product::query()->active()->where(function ($q) use ($slug) {
            $q->where('slug_en', $slug)->orWhere('slug_zh', $slug)->orWhere('slug_zh_hant', $slug);
        })->first();
        $title = __('messages.all_products');
        $description = __('messages.tagline');
        $breadcrumbs = [
            ['name' => __('messages.breadcrumb_home'), 'url' => locale_url('', $locale)],
            ['name' => __('messages.all_series'), 'url' => locale_url('products', $locale)],
        ];
        $ogImage = $this->defaultOgImage();

        if ($product) {
            $title = $product->localizedName($locale);
            $description = $product->localizedShortDesc($locale) ?: $description;
            $ogImage = $this->absoluteImage($product->firstImagePath()) ?? $ogImage;
            if ($product->series) {
                $breadcrumbs[] = [
                    'name' => $product->series->localizedName($locale),
                    'url' => locale_url('series/'.$product->series->localizedSlug($locale), $locale),
                ];
            }
            $breadcrumbs[] = ['name' => $title, 'url' => locale_url('product/'.$product->localizedSlug($locale), $locale)];
        }

        return [$title, $description, $breadcrumbs, $ogImage];
    }

    protected function defaultOgImage(): ?string
    {
        $slides = Setting::getValue('hero_slides', []) ?: [];
        $first = is_array($slides[0] ?? null) ? $slides[0] : null;
        if ($first && ! empty($first['image'])) {
            return $this->absoluteImage($first['image']);
        }

        $contact = site_contact();
        if (! empty($contact['media_image'])) {
            return $this->absoluteImage($contact['media_image']);
        }

        return url('/favicon.ico');
    }

    protected function absoluteImage(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $url = media_url($path);

        return $url && ! str_starts_with($url, 'http') ? url($url) : $url;
    }

    /**
     * @return array<string, mixed>
     */
    protected function organizationSchema(): array
    {
        $contact = site_contact();
        $sameAs = array_values(array_filter([
            $contact['facebook_url'] ?? null,
            $contact['instagram_url'] ?? null,
            $contact['youtube_url'] ?? null,
        ]));

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => localized_setting($contact, 'company') ?: 'SINO GOOD QY Supply Chain',
            'url' => url('/en'),
            'email' => $contact['email'] ?? null,
            'telephone' => $contact['phone'] ?? null,
            'sameAs' => $sameAs,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'SINO GOOD',
            'url' => url('/en'),
            'inLanguage' => ['en', 'zh', 'zh-Hant'],
        ];
    }

    /**
     * @param  list<array{name: string, url: string}>  $breadcrumbs
     * @return array<string, mixed>
     */
    protected function breadcrumbSchema(array $breadcrumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($breadcrumbs)->values()->map(fn (array $crumb, int $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function faqPageSchema(): array
    {
        $items = __('messages.faq_items');
        $entities = [];
        foreach (is_array($items) ? $items : [] as $item) {
            if (! is_array($item) || empty($item['q'])) {
                continue;
            }
            $entities[] = [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['a'] ?? '',
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function productSchema(Series $series): array
    {
        $locale = app()->getLocale();
        $images = collect($series->images ?? [])->map(fn ($img) => $this->absoluteImage(is_array($img) ? ($img[0] ?? null) : $img))->filter()->values()->all();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $series->localizedName($locale),
            'description' => $series->localizedIntro($locale),
            'image' => $images,
            'url' => locale_url('series/'.$series->localizedSlug($locale), $locale),
        ];
    }

    /**
     * @param  iterable<Series>  $seriesList
     * @return array<string, mixed>
     */
    protected function itemListSchema(iterable $seriesList): array
    {
        $locale = app()->getLocale();
        $elements = [];
        $position = 1;
        foreach ($seriesList as $series) {
            if (! $series instanceof Series) {
                continue;
            }
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'url' => locale_url('series/'.$series->localizedSlug($locale), $locale),
                'name' => $series->localizedName($locale),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function dailyArticleSchemas(): array
    {
        $news = Setting::getValue('latest_news', []) ?: [];
        $schemas = [];
        foreach (is_array($news) ? $news : [] as $item) {
            if (! is_array($item)) {
                continue;
            }
            $title = localized_setting($item, 'title');
            if ($title === '') {
                continue;
            }
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $title,
                'description' => localized_setting($item, 'excerpt'),
                'datePublished' => $item['date'] ?? null,
                'url' => $item['link'] ?? locale_url(),
                'author' => [
                    '@type' => 'Organization',
                    'name' => 'SINO GOOD',
                ],
            ];
        }

        return $schemas;
    }
}
