@extends('layouts.app')

@section('title', __('messages.nav_cases'))

@section('content')
    <section class="bg-primary-black pt-28">
        <div class="mx-auto max-w-7xl px-4 py-14 lg:px-8">
            <div class="text-sm font-semibold tracking-[0.28em] text-gold-accent">SINO GOOD</div>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white md:text-5xl">{{ __('messages.cases_title') }}</h1>
            <p class="mt-4 max-w-2xl text-white/65">{{ __('messages.cases_intro') }}</p>
        </div>
    </section>

    <section class="bg-charcoal">
        <div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
            @if($cases->isEmpty())
                <p class="text-white/55">{{ __('messages.no_cases') }}</p>
            @else
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($cases as $case)
                        <article class="group">
                            <div class="aspect-video overflow-hidden bg-primary-black">
                                @if($case->embedUrl())
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
