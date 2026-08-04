@extends('layouts.app')

@section('title', 'SINO GOOD')

@section('content')
    @php
        $slides = collect($slides ?? [])->filter(fn ($slide) => is_array($slide))->values();
        $hero = $hero ?? ['autoplay' => true, 'speed' => 5000];
        $autoplay = ! empty($hero['autoplay']) && $slides->count() > 1;
        $speed = max(2000, (int) ($hero['speed'] ?? 5000));
    @endphp

    {{-- Hero --}}
    <section
        class="relative min-h-screen overflow-hidden bg-primary-black"
        @if($slides->count() > 1)
            data-home-hero
            data-autoplay="{{ $autoplay ? '1' : '0' }}"
            data-speed="{{ $speed }}"
        @endif
    >
        <div class="absolute inset-0" data-hero-slides>
            @forelse($slides as $index => $slide)
                <div
                    class="hero-carousel-slide absolute inset-0 {{ $index === 0 ? 'is-active' : '' }}"
                    data-hero-slide
                    @if($index === 0) data-active="1" @endif
                >
                    @if(!empty($slide['image']))
                        <img
                            src="{{ public_storage_url($slide['image']) }}"
                            alt=""
                            class="h-full w-full object-cover opacity-55 {{ $autoplay ? 'hero-slide' : '' }}"
                        >
                    @else
                        <div class="h-full w-full bg-[radial-gradient(ellipse_at_top_right,_rgba(201,168,76,0.28),_transparent_55%),linear-gradient(160deg,#1a1a1a_0%,#2c2c2c_55%,#151515_100%)]"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-primary-black via-primary-black/55 to-primary-black/25"></div>
                </div>
            @empty
                <div class="absolute inset-0">
                    <div class="h-full w-full bg-[radial-gradient(ellipse_at_top_right,_rgba(201,168,76,0.28),_transparent_55%),linear-gradient(160deg,#1a1a1a_0%,#2c2c2c_55%,#151515_100%)]"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-primary-black via-primary-black/55 to-primary-black/25"></div>
                </div>
            @endforelse
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col justify-end px-4 pb-20 pt-32 lg:px-8 lg:pb-28">
            <div class="fade-up max-w-3xl" data-hero-copy>
                @forelse($slides as $index => $slide)
                    <div
                        class="hero-carousel-copy {{ $index === 0 ? 'is-active' : '' }}"
                        data-hero-copy-item
                        @if($index !== 0) hidden @endif
                    >
                        <div class="mb-5 text-sm font-semibold tracking-[0.28em] text-gold-accent">SINO GOOD</div>
                        <h1 class="text-3xl font-semibold leading-tight tracking-tight text-white md:text-5xl lg:text-6xl">
                            {{ $slide['title_en'] ?? 'SINO GOOD' }}
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/75 md:text-lg">
                            {{ $slide['subtitle_en'] ?? __('messages.tagline') }}
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="{{ $slide['link'] ?? locale_url('products') }}" class="btn-gold">
                                {{ $slide['button_text_en'] ?? __('messages.explore') }}
                            </a>
                            <a href="{{ locale_url('contact') }}" class="btn-outline">{{ __('messages.contact_us') }}</a>
                        </div>
                    </div>
                @empty
                    <div>
                        <div class="mb-5 text-sm font-semibold tracking-[0.28em] text-gold-accent">SINO GOOD</div>
                        <h1 class="text-3xl font-semibold leading-tight tracking-tight text-white md:text-5xl lg:text-6xl">SINO GOOD</h1>
                        <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/75 md:text-lg">{{ __('messages.tagline') }}</p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="{{ locale_url('products') }}" class="btn-gold">{{ __('messages.explore') }}</a>
                            <a href="{{ locale_url('contact') }}" class="btn-outline">{{ __('messages.contact_us') }}</a>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($slides->count() > 1)
                <div class="mt-10 flex items-center gap-4">
                    <button type="button" class="product-slider__nav !static !translate-y-0 !opacity-100" data-hero-prev aria-label="Previous slide">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M12.79 5.23a.75.75 0 01-.02 1.06L9.06 10l3.71 3.71a.75.75 0 11-1.06 1.06l-4.25-4.25a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.08.02z"/></svg>
                    </button>
                    <div class="flex gap-1.5" data-hero-dots>
                        @foreach($slides as $index => $slide)
                            <button
                                type="button"
                                class="product-slider__dot {{ $index === 0 ? 'is-active' : '' }}"
                                data-hero-dot="{{ $index }}"
                                aria-label="Slide {{ $index + 1 }}"
                            ></button>
                        @endforeach
                    </div>
                    <button type="button" class="product-slider__nav !static !translate-y-0 !opacity-100" data-hero-next aria-label="Next slide">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7.21 14.77a.75.75 0 01.02-1.06L10.94 10 7.23 6.29a.75.75 0 111.06-1.06l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.08-.02z"/></svg>
                    </button>
                </div>
            @endif
        </div>
    </section>

    {{-- Categories --}}
    <section class="bg-charcoal">
        <div class="section-pad">
            <div class="fade-in">
                <h2 class="section-title">{{ __('messages.our_categories') }}</h2>
                <p class="section-sub">{{ __('messages.tagline') }}</p>
            </div>
            <div class="mt-12 grid gap-px bg-white/10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($categories as $index => $category)
                    <a href="{{ locale_url('category/'.$category->localizedSlug()) }}"
                       class="group relative min-h-[220px] overflow-hidden bg-primary-black p-8 transition hover:bg-[#222]"
                       style="animation-delay: {{ $index * 0.08 }}s">
                        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-accent/60 to-transparent opacity-0 transition group-hover:opacity-100"></div>
                        <div class="text-xs uppercase tracking-[0.2em] text-gold-accent/80">0{{ $index + 1 }}</div>
                        <div class="mt-8 text-xl font-medium text-white transition group-hover:text-gold-accent">
                            {{ $category->localizedName() }}
                        </div>
                        <div class="mt-4 text-sm text-white/50 transition group-hover:text-white/70">{{ __('messages.view_details') }} →</div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Advantages --}}
    <section class="border-y border-white/10 bg-primary-black">
        <div class="section-pad">
            <h2 class="section-title">{{ __('messages.our_advantages') }}</h2>
            <div class="mt-12 grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['title' => __('messages.advantage_qc'), 'desc' => __('messages.advantage_qc_desc')],
                    ['title' => __('messages.advantage_logistics'), 'desc' => __('messages.advantage_logistics_desc')],
                    ['title' => __('messages.advantage_custom'), 'desc' => __('messages.advantage_custom_desc')],
                    ['title' => __('messages.advantage_years'), 'desc' => __('messages.advantage_years_desc')],
                ] as $item)
                    <div>
                        <div class="mb-4 h-px w-10 bg-gold-accent"></div>
                        <h3 class="text-lg font-medium text-white">{{ $item['title'] }}</h3>
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

    {{-- Featured products --}}
    @if($featuredProducts->isNotEmpty())
        <section class="bg-charcoal">
            <div class="section-pad">
                <div class="flex items-end justify-between gap-6">
                    <h2 class="section-title">{{ __('messages.featured_products') }}</h2>
                    <a href="{{ locale_url('products') }}" class="hidden text-sm text-gold-accent hover:underline md:inline">{{ __('messages.all_products') }} →</a>
                </div>
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($featuredProducts as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Brands --}}
    @if(!empty($brands))
        <section class="border-t border-white/10 bg-primary-black">
            <div class="section-pad">
                <h2 class="section-title">{{ __('messages.partner_brands') }}</h2>
                <div class="mt-10 flex flex-wrap items-center gap-10 opacity-80">
                    @foreach($brands as $brand)
                        @if(!empty($brand['image']))
                            <img src="{{ public_storage_url($brand['image']) }}" alt="{{ $brand['name'] ?? '' }}" class="h-10 w-auto object-contain grayscale transition hover:grayscale-0">
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
