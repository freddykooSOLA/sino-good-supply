@extends('layouts.app')

@section('title', $activeCategory ? $activeCategory->localizedName() : __('messages.all_products'))

@section('content')
    <section class="border-b border-white/10 bg-primary-black pt-28">
        <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
            <h1 class="text-3xl font-semibold tracking-tight text-white md:text-4xl">
                {{ $activeCategory ? $activeCategory->localizedName() : __('messages.all_products') }}
            </h1>
            <p class="mt-3 max-w-2xl text-white/60">{{ __('messages.tagline') }}</p>
        </div>
    </section>

    <section class="bg-charcoal">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 lg:grid-cols-[240px_1fr] lg:px-8">
            <aside>
                <div class="text-xs font-medium uppercase tracking-wider text-white/40">{{ __('messages.filter_by_category') }}</div>
                <nav class="mt-4 space-y-1">
                    <a href="{{ locale_url('products') }}"
                       class="block py-2 text-sm {{ ! $activeCategory ? 'text-gold-accent' : 'text-white/70 hover:text-gold-accent' }}">
                        {{ __('messages.all_products') }}
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
                @if($products->isEmpty())
                    <p class="text-white/55">{{ __('messages.no_products') }}</p>
                @else
                    <div class="grid gap-8 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($products as $product)
                            @include('partials.product-card', ['product' => $product])
                        @endforeach
                    </div>
                    <div class="mt-12">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
