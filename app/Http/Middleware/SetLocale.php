<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const LOCALES = [
        'en' => 'English',
        'zh' => '简体中文',
        'zh-hant' => '繁體中文',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (! array_key_exists($locale, self::LOCALES)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        View::share('currentLocale', $locale);
        View::share('locales', self::LOCALES);

        return $next($request);
    }
}
