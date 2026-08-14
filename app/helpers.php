<?php

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

if (! function_exists('localized_setting')) {
    function localized_setting(array $item, string $field, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $key = match ($locale) {
            'zh' => "{$field}_zh",
            'zh-hant' => "{$field}_zh_hant",
            default => "{$field}_en",
        };

        return (string) ($item[$key] ?? $item["{$field}_en"] ?? $item[$field] ?? '');
    }
}

if (! function_exists('locale_url')) {
    function locale_url(string $path = '', ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $path = ltrim($path, '/');

        return $path === '' ? url("/{$locale}") : url("/{$locale}/{$path}");
    }
}

if (! function_exists('switch_locale_url')) {
    function switch_locale_url(string $targetLocale): string
    {
        $segments = request()->segments();
        if (! empty($segments) && in_array($segments[0], ['en', 'zh', 'zh-hant'], true)) {
            $segments[0] = $targetLocale;
        } else {
            array_unshift($segments, $targetLocale);
        }

        return url('/'.implode('/', $segments));
    }
}

if (! function_exists('public_storage_url')) {
    function public_storage_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('nav_categories')) {
    function nav_categories()
    {
        return once(fn () => Category::query()->active()->orderBy('sort_order')->get());
    }
}

if (! function_exists('site_contact')) {
    function site_contact(): array
    {
        return once(fn () => Setting::getValue('contact', []) ?: []);
    }
}

if (! function_exists('page_hero')) {
    function page_hero(string $page): array
    {
        $heroes = once(fn () => Setting::getValue('page_heroes', []) ?: []);

        return is_array($heroes[$page] ?? null) ? $heroes[$page] : [];
    }
}

if (! function_exists('homepage_heading')) {
    function homepage_heading(string $key, string $fallbackTitle = '', string $fallbackSubtitle = ''): array
    {
        $all = once(fn () => Setting::getValue('homepage_headings', []) ?: []);
        $item = is_array($all[$key] ?? null) ? $all[$key] : [];

        return [
            'title' => localized_setting($item, 'title') ?: $fallbackTitle,
            'subtitle' => localized_setting($item, 'subtitle') ?: $fallbackSubtitle,
        ];
    }
}

if (! function_exists('hero_settings')) {
    function hero_settings(): array
    {
        $defaults = [
            'autoplay' => true,
            'speed' => 5000,
        ];

        $hero = Setting::getValue('hero', []) ?: [];

        return array_merge($defaults, is_array($hero) ? $hero : []);
    }
}

if (! function_exists('google_maps_embed_src')) {
    function google_maps_embed_src(?string $raw): ?string
    {
        if (! filled($raw)) {
            return null;
        }

        $raw = trim($raw);

        if (preg_match('/src=["\']([^"\']+)["\']/i', $raw, $matches)) {
            $raw = html_entity_decode($matches[1], ENT_QUOTES);
        }

        if (! filter_var($raw, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = strtolower((string) parse_url($raw, PHP_URL_HOST));

        if ($host === '' || ! str_contains($host, 'google')) {
            return null;
        }

        return $raw;
    }
}
