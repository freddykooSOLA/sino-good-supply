@extends('layouts.app')

@section('title', __('messages.faq_title'))

@section('content')
    @include('partials.page-hero', [
        'image' => $pageHero['image'] ?? null,
        'title' => localized_setting($pageHero, 'title') ?: __('messages.faq_title'),
        'subtitle' => localized_setting($pageHero, 'subtitle') ?: __('messages.faq_intro'),
        'compact' => true,
    ])

    <section class="section-light">
        <div class="section-pad">
            <p class="max-w-3xl text-sm leading-relaxed text-white/55">
                {{ __('messages.faq_draft_notice') }}
            </p>

            <div class="mt-12 divide-y divide-white/10 border-y border-white/10">
                @foreach(__('messages.faq_items') as $item)
                    <details class="group py-6">
                        <summary class="flex cursor-pointer list-none items-start justify-between gap-4 text-lg font-medium text-white marker:content-none">
                            <span>{{ $item['q'] }}</span>
                            <span class="mt-1 shrink-0 text-gold-accent transition group-open:rotate-45">+</span>
                        </summary>
                        <p class="mt-4 max-w-3xl text-sm leading-relaxed text-white/65">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endsection
