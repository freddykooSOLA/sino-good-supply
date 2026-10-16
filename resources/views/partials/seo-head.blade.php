@php
    $seo = $seo ?? [
        'title' => trim($__env->yieldContent('title', 'SINO GOOD')).' — SINO GOOD',
        'description' => trim($__env->yieldContent('meta_description', __('messages.tagline'))),
        'canonical' => url()->current(),
        'og_image' => null,
        'hreflang' => [],
    ];
    $ogImage = $seo['og_image'] ?? app(\App\Services\OgImageService::class)->brandDefaultUrl();
    $ogImageWidth = (int) ($seo['og_image_width'] ?? 1200);
    $ogImageHeight = (int) ($seo['og_image_height'] ?? 630);
@endphp
<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">
@foreach($seo['hreflang'] ?? [] as $hreflang => $href)
    <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}">
@endforeach
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="{{ $ogImageWidth }}">
<meta property="og:image:height" content="{{ $ogImageHeight }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image" content="{{ $ogImage }}">
@stack('head')
