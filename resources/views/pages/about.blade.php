@extends('layouts.app')

@section('title', __('messages.about_title'))

@section('content')
    <section class="relative min-h-[55vh] overflow-hidden bg-primary-black pt-28">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_rgba(201,168,76,0.18),_transparent_60%)]"></div>
        <div class="relative mx-auto flex min-h-[45vh] max-w-7xl flex-col justify-end px-4 pb-16 lg:px-8">
            <div class="text-sm font-semibold tracking-[0.28em] text-gold-accent">SINO GOOD</div>
            <h1 class="mt-4 max-w-3xl text-4xl font-semibold tracking-tight text-white md:text-5xl">{{ __('messages.about_title') }}</h1>
            <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/70 md:text-lg">{{ __('messages.about_intro') }}</p>
        </div>
    </section>

    <section class="bg-charcoal">
        <div class="section-pad">
            <h2 class="section-title">{{ __('messages.our_advantages') }}</h2>
            <div class="mt-12 grid gap-10 md:grid-cols-2">
                @foreach([
                    ['title' => __('messages.advantage_qc'), 'desc' => __('messages.advantage_qc_desc')],
                    ['title' => __('messages.advantage_logistics'), 'desc' => __('messages.advantage_logistics_desc')],
                    ['title' => __('messages.advantage_custom'), 'desc' => __('messages.advantage_custom_desc')],
                    ['title' => __('messages.advantage_years'), 'desc' => __('messages.advantage_years_desc')],
                ] as $item)
                    <div class="border-l border-gold-accent/50 pl-6">
                        <h3 class="text-xl font-medium text-white">{{ $item['title'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-white/60">{{ $item['desc'] }}</p>
                    </div>
                @endforeach
            </div>

            @if(!empty($stats))
                <div class="mt-16 grid grid-cols-2 gap-8 border-t border-white/10 pt-12 md:grid-cols-4">
                    @foreach($stats as $stat)
                        <div>
                            <div class="text-3xl font-semibold text-gold-accent">{{ $stat['value'] ?? '' }}</div>
                            <div class="mt-2 text-sm text-white/55">{{ localized_setting($stat, 'label') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
