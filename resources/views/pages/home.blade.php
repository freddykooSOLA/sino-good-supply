@extends('layouts.app')

@section('title', 'SINO GOOD')

@section('content')
    {{-- Hero --}}
    <section class="relative min-h-screen overflow-hidden bg-primary-black">
        @php $firstSlide = $slides[0] ?? null; @endphp
        <div class="absolute inset-0">
            @if(!empty($firstSlide['image']))
                <img src="{{ public_storage_url($firstSlide['image']) }}" alt="" class="hero-slide h-full w-full object-cover opacity-55">
            @else
                <div class="h-full w-full bg-[radial-gradient(ellipse_at_top_right,_rgba(201,168,76,0.28),_transparent_55%),linear-gradient(160deg,#1a1a1a_0%,#2c2c2c_55%,#151515_100%)]"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-primary-black via-primary-black/55 to-primary-black/25"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col justify-end px-4 pb-20 pt-32 lg:px-8 lg:pb-28">
            <div class="fade-up max-w-3xl">
                <div class="mb-5 text-sm font-semibold tracking-[0.28em] text-gold-accent">SINO GOOD</div>
                <h1 class="text-3xl font-semibold leading-tight tracking-tight text-white md:text-5xl lg:text-6xl">
                    {{ $firstSlide['title_en'] ?? 'SINO GOOD' }}
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/75 md:text-lg">
                    {{ $firstSlide['subtitle_en'] ?? __('messages.tagline') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ $firstSlide['link'] ?? locale_url('products') }}" class="btn-gold">
                        {{ $firstSlide['button_text_en'] ?? __('messages.explore') }}
                    </a>
                    <a href="{{ locale_url('contact') }}" class="btn-outline">{{ __('messages.contact_us') }}</a>
                </div>
            </div>
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
