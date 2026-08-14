@extends('layouts.app')

@section('title', $activeCategory ? $activeCategory->localizedName() : __('messages.all_series'))

@section('content')
    @include('partials.page-hero', [
        'image' => $pageHero['image'] ?? null,
        'title' => $activeCategory
            ? $activeCategory->localizedName()
            : (localized_setting($pageHero, 'title') ?: __('messages.all_series')),
        'subtitle' => localized_setting($pageHero, 'subtitle') ?: __('messages.tagline'),
    ])

    <section class="bg-charcoal">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 lg:grid-cols-[240px_1fr] lg:px-8">
            <aside>
                <div class="text-xs font-medium uppercase tracking-wider text-white/40">{{ __('messages.filter_by_category') }}</div>
                <nav class="mt-4 space-y-1">
                    <a href="{{ locale_url('products') }}"
                       class="block py-2 text-sm {{ ! $activeCategory ? 'text-gold-accent' : 'text-white/70 hover:text-gold-accent' }}">
                        {{ __('messages.all_series') }}
                    </a>
                    @foreach($categories as $category)
                        <a href="{{ locale_url('category/'.$category->localizedSlug()) }}"
                           class="block py-2 text-sm {{ ($activeCategory?->id === $category->id) ? 'text-gold-accent' : 'text-white/70 hover:text-gold-accent' }}">
                            {{ $category->localizedName() }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div>
                @if($seriesList->isEmpty())
                    <p class="text-white/55">{{ __('messages.no_series') }}</p>
                @else
                    <div class="grid gap-8 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($seriesList as $series)
                            @include('partials.series-card', ['series' => $series])
                        @endforeach
                    </div>
                    <div class="mt-12">
                        {{ $seriesList->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
