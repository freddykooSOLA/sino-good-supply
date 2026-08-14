@extends('layouts.app')

@section('title', __('messages.order_process_title'))

@section('content')
    @php
        $heading = homepage_heading('order_process', __('messages.order_process_title'), __('messages.order_process_intro'));
        $flowchart = $orderProcess['flowchart_image'] ?? null;
        $steps = collect($orderProcess['steps'] ?? [])->filter(fn ($step) => is_array($step))->values();
    @endphp

    @include('partials.page-hero', [
        'image' => $pageHero['image'] ?? null,
        'title' => localized_setting($pageHero, 'title') ?: $heading['title'],
        'subtitle' => localized_setting($pageHero, 'subtitle') ?: $heading['subtitle'],
    ])

    <section class="bg-charcoal">
        <div class="section-pad">
            @if($flowchart)
                <div class="overflow-hidden border border-white/10 bg-primary-black">
                    <img src="{{ public_storage_url($flowchart) }}" alt="{{ $heading['title'] }}" class="h-auto w-full">
                </div>
            @endif

            @if($steps->isNotEmpty())
                <ol class="{{ $flowchart ? 'mt-16' : '' }} grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($steps as $index => $step)
                        <li class="border-l border-gold-accent/50 pl-5">
                            <div class="text-xs tracking-[0.2em] text-gold-accent">
                                {{ str_pad((string) ($step['step'] ?? ($index + 1)), 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <h2 class="mt-3 text-lg font-medium text-white">{{ localized_setting($step, 'title') }}</h2>
                            @if(localized_setting($step, 'description'))
                                <p class="mt-3 text-sm leading-relaxed text-white/60">{{ localized_setting($step, 'description') }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @elseif(! $flowchart)
                <p class="text-white/55">{{ __('messages.order_process_empty') }}</p>
            @endif
        </div>
    </section>
@endsection
