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
        return once(fn () => Setting::getValue('contact', []));
    }
}
