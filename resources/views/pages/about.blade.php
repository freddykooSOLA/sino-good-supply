@extends('layouts.app')

@section('title', __('messages.about_title'))

@section('content')
    @php
        $intro = localized_setting($aboutContent, 'about_intro') ?: __('messages.about_intro');
        $body = localized_setting($aboutContent, 'about_body');
    @endphp

    @include('partials.page-hero', [
        'image' => $pageHero['image'] ?? null,
        'title' => localized_setting($pageHero, 'title') ?: __('messages.about_title'),
        'subtitle' => localized_setting($pageHero, 'subtitle') ?: $intro,
    ])

    <section class="section-light">
        <div class="section-pad" data-reveal>
            @if($body)
                <div class="prose max-w-3xl text-primary-black/80" data-reveal-child>
                    {!! $body !!}
                </div>
            @else
                <div class="max-w-3xl text-base leading-relaxed text-primary-black/75" data-reveal-child>
                    <p>{{ $intro }}</p>
                    <p class="mt-6">{{ __('messages.about_body') }}</p>
                </div>
            @endif

            <h2 class="section-title mt-16">{{ __('messages.our_advantages') }}</h2>
            <div class="mt-12 grid gap-10 md:grid-cols-2">
                @forelse($advantages as $item)
                    <div class="border-l border-gold-accent/50 pl-6">
                        <h3 class="text-xl font-medium text-primary-black">{{ localized_setting($item, 'title') }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-primary-black/65">{{ localized_setting($item, 'description') }}</p>
                    </div>
                @empty
                    @foreach([
                        ['title' => __('messages.advantage_qc'), 'desc' => __('messages.advantage_qc_desc')],
                        ['title' => __('messages.advantage_logistics'), 'desc' => __('messages.advantage_logistics_desc')],
                        ['title' => __('messages.advantage_custom'), 'desc' => __('messages.advantage_custom_desc')],
                        ['title' => __('messages.advantage_years'), 'desc' => __('messages.advantage_years_desc')],
                    ] as $item)
                        <div class="border-l border-gold-accent/50 pl-6">
                            <h3 class="text-xl font-medium text-primary-black">{{ $item['title'] }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-primary-black/65">{{ $item['desc'] }}</p>
                        </div>
                    @endforeach
                @endforelse
            </div>

            @if(!empty($stats))
                <div class="mt-16 grid grid-cols-2 gap-8 border-t border-primary-black/10 pt-12 md:grid-cols-4">
                    @foreach($stats as $stat)
                        <div>
                            <div class="text-3xl font-semibold text-gold-accent" data-count="{{ $stat['value'] ?? '' }}">{{ $stat['value'] ?? '' }}</div>
                            <div class="mt-2 text-sm text-primary-black/60">{{ localized_setting($stat, 'label') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
