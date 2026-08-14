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
        $fbHeading = homepage_heading('facebook_posts', __('messages.facebook_latest'), '');
        $newsHeading = homepage_heading('latest_news', __('messages.latest_news'), '');
        $processImage = $orderProcess['flowchart_image'] ?? null;
        $processSteps = collect($orderProcess['steps'] ?? [])->filter(fn ($step) => is_array($step))->values();
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
                            {{ localized_setting($slide, 'title') ?: 'SINO GOOD' }}
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/75 md:text-lg">
                            {{ localized_setting($slide, 'subtitle') ?: __('messages.tagline') }}
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="{{ $slide['link'] ?? locale_url('products') }}" class="btn-gold">
                                {{ localized_setting($slide, 'button_text') ?: __('messages.explore') }}
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
        <section class="bg-charcoal">
            <div class="section-pad">
                <div class="fade-in">
                    <h2 class="section-title">{{ $coreHeading['title'] }}</h2>
                    @if($coreHeading['subtitle'])
                        <p class="section-sub">{{ $coreHeading['subtitle'] }}</p>
                    @endif
                </div>
                <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($coreBusiness as $item)
                        <article class="border border-white/10 bg-primary-black p-8">
                            @if(!empty($item['image']))
                                <img src="{{ public_storage_url($item['image']) }}" alt="" class="mb-6 h-12 w-12 object-contain">
                            @else
                                <div class="mb-6 h-px w-10 bg-gold-accent"></div>
                            @endif
                            <h3 class="text-lg font-medium text-white">{{ localized_setting($item, 'title') }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-white/60">{{ localized_setting($item, 'description') }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Featured cases --}}
    @if(!empty($featuredCases))
        <section class="border-y border-white/10 bg-primary-black">
            <div class="section-pad">
                <div class="flex items-end justify-between gap-6">
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
                        <article class="group">
                            <div class="aspect-video overflow-hidden bg-charcoal">
                                @if(!empty($item['resolved_video']))
                                    <video class="h-full w-full object-cover" controls preload="metadata" playsinline src="{{ $item['resolved_video'] }}"></video>
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
        <section class="bg-charcoal">
            <div class="section-pad">
                <div class="flex items-end justify-between gap-6">
                    <div>
                        <h2 class="section-title">{{ $processHeading['title'] }}</h2>
                        @if($processHeading['subtitle'])
                            <p class="section-sub">{{ $processHeading['subtitle'] }}</p>
                        @endif
                    </div>
                    <a href="{{ locale_url('order-process') }}" class="hidden text-sm text-gold-accent hover:underline md:inline">{{ __('messages.view_details') }} →</a>
                </div>
                @if($processImage)
                    <a href="{{ locale_url('order-process') }}" class="mt-10 block overflow-hidden border border-white/10 bg-primary-black">
                        <img src="{{ public_storage_url($processImage) }}" alt="{{ $processHeading['title'] }}" class="h-auto w-full">
                    </a>
                @else
                    <ol class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($processSteps->take(6) as $index => $step)
                            <li class="border-l border-gold-accent/50 pl-5">
                                <div class="text-xs tracking-[0.2em] text-gold-accent">{{ str_pad((string) ($step['step'] ?? ($index + 1)), 2, '0', STR_PAD_LEFT) }}</div>
                                <h3 class="mt-3 text-lg font-medium text-white">{{ localized_setting($step, 'title') }}</h3>
                                <p class="mt-3 text-sm leading-relaxed text-white/60">{{ localized_setting($step, 'description') }}</p>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>
    @endif

    {{-- Advantages --}}
    <section class="border-y border-white/10 bg-primary-black">
        <div class="section-pad">
            <h2 class="section-title">{{ $advHeading['title'] }}</h2>
            @if($advHeading['subtitle'])
                <p class="section-sub">{{ $advHeading['subtitle'] }}</p>
            @endif
            <div class="mt-12 grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                @forelse($advantages as $item)
                    <div>
                        @if(!empty($item['image']))
                            <img src="{{ public_storage_url($item['image']) }}" alt="" class="mb-4 h-10 w-10 object-contain">
                        @else
                            <div class="mb-4 h-px w-10 bg-gold-accent"></div>
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

    {{-- Categories --}}
    <section class="bg-charcoal">
        <div class="section-pad">
            <div class="fade-in">
                <h2 class="section-title">{{ $catHeading['title'] }}</h2>
                <p class="section-sub">{{ $catHeading['subtitle'] }}</p>
            </div>
            <div class="mt-12 grid gap-px bg-white/10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($categories as $index => $category)
                    @php $previewImage = $category->previewImageUrl(); @endphp
                    <a href="{{ locale_url('category/'.$category->localizedSlug()) }}"
                       class="group relative min-h-[260px] overflow-hidden bg-primary-black p-8 transition">
                        @if($previewImage)
                            <img
                                src="{{ $previewImage }}"
                                alt="{{ $category->localizedName() }}"
                                class="absolute inset-0 h-full w-full object-cover opacity-45 transition duration-700 group-hover:scale-105 group-hover:opacity-55"
                                loading="lazy"
                            >
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-primary-black via-primary-black/55 to-primary-black/20"></div>
                        <div class="relative z-10 flex h-full min-h-[196px] flex-col justify-end">
                            <div class="text-xs uppercase tracking-[0.2em] text-gold-accent/80">0{{ $index + 1 }}</div>
                            <div class="mt-4 text-xl font-medium text-white transition group-hover:text-gold-accent">
                                {{ $category->localizedName() }}
                            </div>
                            <div class="mt-3 text-sm text-white/50 transition group-hover:text-white/70">{{ __('messages.view_series') }} →</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Facebook --}}
    @if(!empty($facebookPosts))
        <section class="border-y border-white/10 bg-primary-black">
            <div class="section-pad">
                <h2 class="section-title">{{ $fbHeading['title'] }}</h2>
                @if($fbHeading['subtitle'])
                    <p class="section-sub">{{ $fbHeading['subtitle'] }}</p>
                @endif
                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    @foreach($facebookPosts as $post)
                        <a href="{{ $post['link'] ?? '#' }}" target="_blank" rel="noopener" class="group block border border-white/10 bg-charcoal transition hover:border-gold-accent/50">
                            @if(!empty($post['image']))
                                <img src="{{ public_storage_url($post['image']) }}" alt="" class="aspect-[4/3] w-full object-cover">
                            @endif
                            <div class="p-6">
                                <p class="text-sm leading-relaxed text-white/70">{{ localized_setting($post, 'text') }}</p>
                                <span class="mt-4 inline-block text-sm text-gold-accent">Facebook →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Latest news --}}
    @if(!empty($latestNews))
        <section class="bg-charcoal">
            <div class="section-pad">
                <h2 class="section-title">{{ $newsHeading['title'] }}</h2>
                @if($newsHeading['subtitle'])
                    <p class="section-sub">{{ $newsHeading['subtitle'] }}</p>
                @endif
                <div class="mt-12 divide-y divide-white/10 border-y border-white/10">
                    @foreach($latestNews as $news)
                        <article class="py-8">
                            @if(!empty($news['date']))
                                <div class="text-xs tracking-wider text-gold-accent/80">{{ $news['date'] }}</div>
                            @endif
                            <h3 class="mt-2 text-xl font-medium text-white">{{ localized_setting($news, 'title') }}</h3>
                            @if(localized_setting($news, 'excerpt'))
                                <p class="mt-3 max-w-3xl text-sm leading-relaxed text-white/60">{{ localized_setting($news, 'excerpt') }}</p>
                            @endif
                            @if(!empty($news['link']))
                                <a href="{{ $news['link'] }}" class="mt-4 inline-block text-sm text-gold-accent">{{ __('messages.view_details') }} →</a>
                            @endif
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
