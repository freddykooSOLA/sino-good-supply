@extends('layouts.app')

@section('title', 'SINO GOOD')

@section('content')
    @php
        $slides = collect($slides ?? [])->filter(fn ($slide) => is_array($slide))->values();
        $hero = $hero ?? ['autoplay' => true, 'speed' => 5000];
        $autoplay = ! empty($hero['autoplay']) && $slides->count() > 1;
        $speed = max(2000, (int) ($hero['speed'] ?? 5000));
        $coreHeading = homepage_heading('core_business', __('messages.core_business'), __('messages.core_business_intro'));
        $casesHeading = homepage_heading('featured_cases', __('messages.nav_cases'), __('messages.cases_intro'));
        $processHeading = homepage_heading('order_process', __('messages.order_process_title'), __('messages.order_process_intro'));
        $advHeading = homepage_heading('advantages', __('messages.our_advantages'), '');
        $catHeading = homepage_heading('product_categories', __('messages.our_categories'), __('messages.tagline'));
        $socialHeading = homepage_heading('facebook_posts', __('messages.social_feed'), '');
        $newsHeading = homepage_heading('latest_news', __('messages.latest_news'), '');
        $processImage = $orderProcess['flowchart_image'] ?? null;
        $processSteps = collect($orderProcess['steps'] ?? [])->filter(fn ($step) => is_array($step))->values();
        $marqueeItems = $categories->map(fn ($category) => $category->localizedName())->filter()->values();
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
                        @php $heroSrc = hero_image_sources($slide['image']); @endphp
                        <picture>
                            @if($heroSrc['webp'])
                                <source srcset="{{ $heroSrc['webp'] }}" type="image/webp">
                            @endif
                            <img
                                src="{{ $heroSrc['src'] }}"
                                @if($heroSrc['width']) width="{{ $heroSrc['width'] }}" @endif
                                @if($heroSrc['height']) height="{{ $heroSrc['height'] }}" @endif
                                alt=""
                                class="hero-slide h-full w-full object-cover opacity-80"
                                @if($index === 0) fetchpriority="high" loading="eager" @else loading="lazy" @endif
                                decoding="async"
                            >
                        </picture>
                    @else
                        <div class="h-full w-full bg-[radial-gradient(ellipse_at_top_right,_rgba(201,168,76,0.28),_transparent_55%),linear-gradient(160deg,#1a1a1a_0%,#2c2c2c_55%,#151515_100%)]"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-primary-black/95 via-primary-black/40 to-primary-black/10"></div>
                </div>
            @empty
                <div class="absolute inset-0">
                    <div class="h-full w-full bg-[radial-gradient(ellipse_at_top_right,_rgba(201,168,76,0.28),_transparent_55%),linear-gradient(160deg,#1a1a1a_0%,#2c2c2c_55%,#151515_100%)]"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-primary-black/95 via-primary-black/40 to-primary-black/10"></div>
                </div>
            @endforelse
        </div>

        <div class="relative z-10 mx-auto flex min-h-screen max-w-7xl flex-col justify-end px-4 pb-24 pt-32 lg:px-8 lg:pb-28">
            <div class="fade-up hero-copy-panel max-w-3xl" data-hero-copy>
                @forelse($slides as $index => $slide)
                    <div
                        class="hero-carousel-copy {{ $index === 0 ? 'is-active' : '' }}"
                        data-hero-copy-item
                        @if($index !== 0) hidden @endif
                    >
                        <div class="mb-5 text-sm font-semibold tracking-[0.28em] text-gold-accent">SINO GOOD</div>
                        <h1 class="hero-headline hero-headline-display" data-hero-headline>
                            {{ localized_setting($slide, 'title') ?: 'SINO GOOD' }}
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/75 md:text-lg">
                            {{ localized_setting($slide, 'subtitle') ?: __('messages.tagline') }}
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3 sm:gap-4">
                            <a href="{{ $slide['link'] ?? locale_url('products') }}" class="btn-gold" data-magnetic>
                                {{ localized_setting($slide, 'button_text') ?: __('messages.explore') }}
                            </a>
                            <a href="{{ locale_url('contact') }}" class="btn-outline">{{ __('messages.contact_us') }}</a>
                        </div>
                    </div>
                @empty
                    <div>
                        <div class="mb-5 text-sm font-semibold tracking-[0.28em] text-gold-accent">SINO GOOD</div>
                        <h1 class="hero-headline hero-headline-display" data-hero-headline>SINO GOOD</h1>
                        <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/75 md:text-lg">{{ __('messages.tagline') }}</p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="{{ locale_url('products') }}" class="btn-gold" data-magnetic>{{ __('messages.explore') }}</a>
                            <a href="{{ locale_url('contact') }}" class="btn-outline">{{ __('messages.contact_us') }}</a>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($slides->count() > 1)
                <div class="hero-carousel-controls relative z-20 mt-12 flex items-center gap-4 border-t border-white/10 pt-6 sm:mt-10 sm:border-0 sm:pt-0">
                    <button type="button" class="product-slider__nav !static !translate-y-0 !opacity-100" data-hero-prev aria-label="Previous slide">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M12.79 5.23a.75.75 0 01-.02 1.06L9.06 10l3.71 3.71a.75.75 0 11-1.06 1.06l-4.25-4.25a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.08.02z"/></svg>
                    </button>
                    <div class="flex gap-1.5" data-hero-dots>
                        @foreach($slides as $index => $slide)
                            <button type="button" class="product-slider__dot {{ $index === 0 ? 'is-active' : '' }}" data-hero-dot="{{ $index }}" aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                    <button type="button" class="product-slider__nav !static !translate-y-0 !opacity-100" data-hero-next aria-label="Next slide">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7.21 14.77a.75.75 0 01.02-1.06L10.94 10 7.23 6.29a.75.75 0 111.06-1.06l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.08-.02z"/></svg>
                    </button>
                </div>
            @endif
        </div>
    </section>

    {{-- Core business --}}
    @if(!empty($coreBusiness))
        <section class="section-light">
            <div class="section-pad" data-reveal>
                <div data-reveal-child>
                    <h2 class="section-title">{{ $coreHeading['title'] }}</h2>
                    @if($coreHeading['subtitle'])
                        <p class="section-sub">{{ $coreHeading['subtitle'] }}</p>
                    @endif
                </div>
                <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($coreBusiness as $index => $item)
                        @php
                            $coreIcons = ['briefcase', 'cube', 'truck'];
                            $coreIcon = $coreIcons[$index % count($coreIcons)];
                        @endphp
                        <article class="overflow-hidden border border-primary-black/10 bg-white shadow-sm" data-reveal-child>
                            @if(!empty($item['image']))
                                <div class="aspect-[16/10] overflow-hidden">
                                    <img src="{{ public_storage_url($item['image']) }}" alt="" class="h-full w-full object-cover transition duration-700 hover:scale-105" loading="lazy">
                                </div>
                            @endif
                            <div class="p-8">
                                @if(empty($item['image']))
                                    <div class="mb-6 text-gold-accent">@include('partials.line-icon', ['name' => $coreIcon])</div>
                                @endif
                                <h3 class="text-lg font-medium text-primary-black">{{ localized_setting($item, 'title') }}</h3>
                                <p class="mt-3 text-sm leading-relaxed text-primary-black/65">{{ localized_setting($item, 'description') }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Featured cases --}}
    @if(!empty($featuredCases))
        <section class="section-dark border-y border-white/10">
            <div class="section-pad" data-reveal>
                <div class="flex items-end justify-between gap-6" data-reveal-child>
                    <div>
                        <h2 class="section-title">{{ $casesHeading['title'] }}</h2>
                        @if($casesHeading['subtitle'])
                            <p class="section-sub">{{ $casesHeading['subtitle'] }}</p>
                        @endif
                    </div>
                    <a href="{{ locale_url('cases') }}" class="hidden text-sm text-gold-accent hover:underline md:inline">{{ __('messages.nav_cases') }} →</a>
                </div>
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($featuredCases as $item)
                        <article class="group" data-reveal-child>
                            <div class="card-image aspect-video bg-charcoal">
                                @if(!empty($item['resolved_video']))
                                    <video class="h-full w-full object-cover" controls preload="metadata" playsinline muted data-video-poster src="{{ $item['resolved_video'] }}"></video>
                                @elseif(!empty($item['resolved_embed']))
                                    <iframe class="h-full w-full" src="{{ $item['resolved_embed'] }}" title="{{ $item['resolved_title'] }}" loading="lazy" allowfullscreen></iframe>
                                @elseif(!empty($item['resolved_image']))
                                    <img src="{{ public_storage_url($item['resolved_image']) }}" alt="{{ $item['resolved_title'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @elseif(!empty($item['resolved_thumb']))
                                    <img src="{{ $item['resolved_thumb'] }}" alt="{{ $item['resolved_title'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @endif
                            </div>
                            <h3 class="mt-4 text-lg font-medium text-white">{{ $item['resolved_title'] }}</h3>
                            @if(!empty($item['resolved_link']))
                                <a href="{{ $item['resolved_link'] }}" class="mt-2 inline-block text-sm text-gold-accent" @if(\Illuminate\Support\Str::startsWith($item['resolved_link'], 'http')) target="_blank" rel="noopener" @endif>{{ __('messages.view_details') }} →</a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Order process --}}
    @if($processImage || $processSteps->isNotEmpty())
        <section class="section-gold">
            <div class="section-pad" data-reveal>
                <div class="flex items-end justify-between gap-6" data-reveal-child>
                    <div>
                        <h2 class="section-title">{{ $processHeading['title'] }}</h2>
                        @if($processHeading['subtitle'])
                            <p class="section-sub">{{ $processHeading['subtitle'] }}</p>
                        @endif
                    </div>
                    <a href="{{ locale_url('order-process') }}" class="hidden text-sm font-medium text-primary-black/80 hover:text-primary-black md:inline">{{ __('messages.view_details') }} →</a>
                </div>
                @if($processImage)
                    <a href="{{ locale_url('order-process') }}" class="mt-10 block overflow-hidden border border-primary-black/10 bg-primary-black/5" data-reveal-child>
                        <img src="{{ public_storage_url($processImage) }}" alt="{{ $processHeading['title'] }}" class="h-auto w-full" loading="lazy">
                    </a>
                @elseif($processSteps->isNotEmpty())
                    <div class="process-pin mt-12 hidden lg:block" data-process-pin data-reveal-child>
                        <div class="process-pin__track" data-process-track>
                            @foreach($processSteps->take(6) as $index => $step)
                                <article class="process-pin__step" data-process-step>
                                    <div class="text-xs font-semibold tracking-[0.2em] text-primary-black/70">{{ str_pad((string) ($step['step'] ?? ($index + 1)), 2, '0', STR_PAD_LEFT) }}</div>
                                    <h3 class="mt-4 text-xl font-semibold text-primary-black">{{ localized_setting($step, 'title') }}</h3>
                                    <p class="mt-3 text-sm leading-relaxed text-primary-black/70">{{ localized_setting($step, 'description') }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                    <ol class="mt-12 grid gap-8 lg:hidden">
                        @foreach($processSteps->take(6) as $index => $step)
                            <li class="border-l-2 border-primary-black/30 pl-5" data-reveal-child>
                                <div class="text-xs font-semibold tracking-[0.2em] text-primary-black/70">{{ str_pad((string) ($step['step'] ?? ($index + 1)), 2, '0', STR_PAD_LEFT) }}</div>
                                <h3 class="mt-3 text-lg font-semibold text-primary-black">{{ localized_setting($step, 'title') }}</h3>
                                <p class="mt-3 text-sm leading-relaxed text-primary-black/70">{{ localized_setting($step, 'description') }}</p>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>
    @endif

    {{-- Advantages --}}
    <section class="section-dark border-y border-white/10">
        <div class="section-pad" data-reveal>
            <h2 class="section-title" data-reveal-child>{{ $advHeading['title'] }}</h2>
            @if($advHeading['subtitle'])
                <p class="section-sub">{{ $advHeading['subtitle'] }}</p>
            @endif
            <div class="mt-12 grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                @forelse($advantages as $index => $item)
                    @php
                        $advIcons = ['shield-check', 'truck', 'wrench-screwdriver', 'globe-alt'];
                        $advIcon = $advIcons[$index % count($advIcons)];
                    @endphp
                    <div data-reveal-child>
                        @if(!empty($item['image']))
                            <div class="mb-4 aspect-[4/3] max-w-[200px] overflow-hidden rounded-sm">
                                <img src="{{ public_storage_url($item['image']) }}" alt="" class="h-full w-full object-cover" loading="lazy">
                            </div>
                        @else
                            <div class="mb-4">@include('partials.line-icon', ['name' => $advIcon, 'class' => 'h-7 w-7 text-gold-accent'])</div>
                        @endif
                        <h3 class="text-lg font-medium text-white">{{ localized_setting($item, 'title') }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-white/60">{{ localized_setting($item, 'description') }}</p>
                    </div>
                @empty
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
                @endforelse
            </div>

            @if(!empty($stats))
                <div class="mt-16 border-t border-white/10 pt-12" data-reveal-child>
                    <div class="mb-8 flex items-center gap-3 text-white/50">
                        @include('partials.line-icon', ['name' => 'chart-bar', 'class' => 'h-5 w-5 text-gold-accent/80'])
                        <span class="h-px flex-1 bg-gradient-to-r from-gold-accent/40 to-transparent"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-8 md:grid-cols-4 md:gap-6">
                        @foreach($stats as $stat)
                            <div class="md:border-l md:border-white/10 md:pl-6 first:md:border-l-0 first:md:pl-0">
                                <div class="text-4xl font-semibold text-gold-accent md:text-5xl" data-count="{{ $stat['value'] ?? '' }}">{{ $stat['value'] ?? '' }}</div>
                                <div class="mt-2 text-sm text-white/55">{{ localized_setting($stat, 'label') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Categories --}}
    <section class="section-light">
        <div class="section-pad" data-reveal>
            <div data-reveal-child>
                <h2 class="section-title">{{ $catHeading['title'] }}</h2>
                <p class="section-sub">{{ $catHeading['subtitle'] }}</p>
            </div>
            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($categories as $index => $category)
                    @php $previewImage = $category->previewImageUrl(); @endphp
                    <a href="{{ locale_url('category/'.$category->localizedSlug()) }}"
                       class="card-image group relative min-h-[280px] overflow-hidden bg-primary-black"
                       data-reveal-child>
                        @if($previewImage)
                            <img
                                src="{{ $previewImage }}"
                                alt="{{ $category->localizedName() }}"
                                class="card-image__media absolute inset-0 opacity-90"
                                loading="lazy"
                            >
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-primary-black via-charcoal to-gold-accent/30"></div>
                        @endif
                        <div class="card-image__overlay"></div>
                        <div class="relative z-10 flex h-full min-h-[280px] flex-col justify-end p-8">
                            <div class="text-xs uppercase tracking-[0.2em] text-gold-accent">0{{ $index + 1 }}</div>
                            <div class="mt-3 text-2xl font-semibold text-white">
                                {{ $category->localizedName() }}
                            </div>
                            <span class="card-image__arrow mt-4">{{ __('messages.view_series') }} →</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @if($marqueeItems->isNotEmpty())
        <section class="section-dark border-y border-white/10 py-10">
            <p class="mb-6 text-center text-xs font-semibold uppercase tracking-[0.28em] text-white/45">{{ __('messages.marquee_label') }}</p>
            <div class="marquee" data-marquee>
                <div class="marquee__track" data-marquee-track>
                    @foreach($marqueeItems as $label)
                        <span class="marquee__item">{{ $label }}</span>
                        <span class="marquee__item text-gold-accent">•</span>
                    @endforeach
                    <span class="marquee__item">{{ __('messages.marquee_europe') }}</span>
                    <span class="marquee__item text-gold-accent">•</span>
                </div>
            </div>
        </section>
    @endif

    {{-- Social feed --}}
    @if(!empty($socialPosts))
        <section class="border-y border-white/10 bg-primary-black">
            <div class="section-pad">
                <h2 class="section-title">{{ $socialHeading['title'] }}</h2>
                @if($socialHeading['subtitle'])
                    <p class="section-sub">{{ $socialHeading['subtitle'] }}</p>
                @endif
                <div class="mt-12 grid gap-6 md:grid-cols-3">
                    @foreach($socialPosts as $post)
                        @php
                            $platform = $post['platform'] ?? 'manual';
                            $platformLabel = match ($platform) {
                                'instagram' => 'Instagram',
                                'youtube' => 'YouTube',
                                'facebook' => 'Facebook',
                                default => 'SINO GOOD',
                            };
                        @endphp
                        <a href="{{ $post['link'] ?? '#' }}" target="_blank" rel="noopener" class="group flex flex-col border border-white/10 bg-charcoal transition hover:border-gold-accent/50">
                            @if(!empty($post['image']))
                                <img src="{{ media_url($post['image']) }}" alt="" class="aspect-[4/3] w-full object-cover" loading="lazy" decoding="async">
                            @endif
                            <div class="flex flex-1 flex-col p-6">
                                <div class="text-xs font-semibold uppercase tracking-wider text-gold-accent">{{ $platformLabel }}</div>
                                @if(!empty($post['created_at']))
                                    <time class="mt-1 text-xs text-white/45" datetime="{{ $post['created_at'] }}">{{ \Illuminate\Support\Carbon::parse($post['created_at'])->format('Y-m-d') }}</time>
                                @endif
                                <p class="mt-3 text-sm leading-relaxed text-white/70">{{ $post['text'] ?? '' }}</p>
                                <span class="mt-4 inline-block text-sm text-gold-accent">{{ $platformLabel }} →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Latest news --}}
    @if(!empty($latestNews))
        <section class="section-light">
            <div class="section-pad" data-reveal>
                <div data-reveal-child>
                    <h2 class="section-title">{{ $newsHeading['title'] }}</h2>
                    @if($newsHeading['subtitle'])
                        <p class="section-sub">{{ $newsHeading['subtitle'] }}</p>
                    @endif
                </div>
                <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($latestNews as $news)
                        @php
                            $newsImages = collect($news['images'] ?? [])
                                ->map(fn ($path) => media_url(is_array($path) ? ($path[0] ?? null) : $path))
                                ->filter()
                                ->values();
                            $coverImage = $newsImages->first();
                        @endphp
                        <article class="news-card" data-reveal-child>
                            @if($coverImage)
                                <div class="card-image aspect-[4/3]">
                                    <img src="{{ $coverImage }}" alt="{{ localized_setting($news, 'title') }}" class="card-image__media h-full w-full" loading="lazy">
                                    <div class="card-image__overlay"></div>
                                </div>
                            @endif
                            <div class="flex flex-1 flex-col p-6">
                                @if(!empty($news['date']))
                                    <time class="text-xs font-medium uppercase tracking-wider text-gold-accent">{{ $news['date'] }}</time>
                                @endif
                                <h3 class="mt-2 text-lg font-semibold text-primary-black">{{ localized_setting($news, 'title') }}</h3>
                                @if(localized_setting($news, 'excerpt'))
                                    <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-primary-black/65">{{ localized_setting($news, 'excerpt') }}</p>
                                @endif
                                @if(!empty($news['link']))
                                    <a href="{{ $news['link'] }}" class="card-image__arrow mt-5 text-primary-black">{{ __('messages.view_details') }} →</a>
                                @endif
                            </div>
                        </article>
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
