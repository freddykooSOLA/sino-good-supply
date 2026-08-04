@extends('layouts.app')

@section('title', __('messages.nav_cases'))

@section('content')
    @include('partials.page-hero', [
        'image' => $pageHero['image'] ?? null,
        'title' => localized_setting($pageHero, 'title') ?: __('messages.cases_title'),
        'subtitle' => localized_setting($pageHero, 'subtitle') ?: __('messages.cases_intro'),
    ])

    <section class="bg-charcoal">
        <div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
            @if($cases->isEmpty())
                <p class="text-white/55">{{ __('messages.no_cases') }}</p>
            @else
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($cases as $case)
                        <article class="group">
                            <div class="aspect-video overflow-hidden bg-primary-black">
                                @if($case->isUploadedVideo() && $case->uploadedVideoUrl())
                                    <video
                                        class="h-full w-full object-cover"
                                        controls
                                        preload="metadata"
                                        playsinline
                                        src="{{ $case->uploadedVideoUrl() }}"
                                    >
                                        {{ $case->localizedTitle() }}
                                    </video>
                                @elseif($case->embedUrl())
                                    <iframe
                                        class="h-full w-full"
                                        src="{{ $case->embedUrl() }}"
                                        title="{{ $case->localizedTitle() }}"
                                        loading="lazy"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen
                                        referrerpolicy="strict-origin-when-cross-origin"
                                    ></iframe>
                                @elseif($case->thumbnailUrl())
                                    <a href="{{ $case->youtube_url }}" target="_blank" rel="noopener">
                                        <img src="{{ $case->thumbnailUrl() }}" alt="{{ $case->localizedTitle() }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    </a>
                                @else
                                    <div class="flex h-full items-center justify-center text-xs tracking-widest text-white/30">SINO GOOD</div>
                                @endif
                            </div>
                            <h2 class="mt-4 text-lg font-medium text-white">{{ $case->localizedTitle() }}</h2>
                            @if($case->localizedDescription())
                                <p class="mt-2 text-sm leading-relaxed text-white/60">{{ $case->localizedDescription() }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $cases->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
