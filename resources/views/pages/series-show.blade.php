@extends('layouts.app')

@section('title', $series->localizedName())

@section('content')
    @include('partials.page-hero', [
        'image' => $pageHero['image'] ?? null,
        'title' => $series->localizedName(),
        'subtitle' => $series->localizedIntro() ?: (localized_setting($pageHero, 'subtitle') ?: ''),
        'compact' => true,
    ])

    <section class="bg-charcoal">
        <div class="mx-auto max-w-7xl px-4 py-8 text-sm text-white/50 lg:px-8">
            <a href="{{ locale_url() }}" class="hover:text-gold-accent">{{ __('messages.breadcrumb_home') }}</a>
            <span class="mx-2">/</span>
            @if($series->category)
                <a href="{{ locale_url('category/'.$series->category->localizedSlug()) }}" class="hover:text-gold-accent">{{ $series->category->localizedName() }}</a>
                <span class="mx-2">/</span>
            @endif
            <span class="text-white/80">{{ $series->localizedName() }}</span>
        </div>

        <div class="mx-auto grid max-w-7xl gap-12 px-4 pb-16 lg:grid-cols-2 lg:px-8">
            <div>
                @php
                    $images = $series->imageUrls();
                    $hasSlider = count($images) > 1;
                @endphp

                <div
                    class="product-slider relative aspect-[4/3] overflow-hidden bg-primary-black"
                    @if($hasSlider) data-product-slider @endif
                >
                    @if(! empty($images))
                        <div class="product-slider__track flex h-full transition-transform duration-300 ease-out" data-slider-track>
                            @foreach($images as $index => $image)
                                <button
                                    type="button"
                                    class="h-full w-full shrink-0"
                                    onclick="openLightbox('{{ $image }}')"
                                    aria-label="{{ $series->localizedName() }} image {{ $index + 1 }}"
                                >
                                    <img
                                        src="{{ $image }}"
                                        alt="{{ $series->localizedName() }}"
                                        class="h-full w-full cursor-zoom-in object-cover"
                                        loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                        draggable="false"
                                    >
                                </button>
                            @endforeach
                        </div>

                        @if($hasSlider)
                            <button type="button" class="product-slider__nav left-3" data-slider-prev aria-label="Previous image">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M12.79 5.23a.75.75 0 01-.02 1.06L9.06 10l3.71 3.71a.75.75 0 11-1.06 1.06l-4.25-4.25a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.08.02z"/></svg>
                            </button>
                            <button type="button" class="product-slider__nav right-3" data-slider-next aria-label="Next image">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7.21 14.77a.75.75 0 01.02-1.06L10.94 10 7.23 6.29a.75.75 0 111.06-1.06l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.08-.02z"/></svg>
                            </button>
                            <div class="absolute bottom-3 left-0 right-0 z-10 flex justify-center gap-1.5" data-slider-dots>
                                @foreach($images as $index => $image)
                                    <button type="button" class="product-slider__dot {{ $index === 0 ? 'is-active' : '' }}" data-slider-dot="{{ $index }}" aria-label="Image {{ $index + 1 }}"></button>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="flex h-full items-center justify-center text-white/30">SINO GOOD</div>
                    @endif
                </div>

                @if($hasSlider)
                    <div class="mt-3 grid grid-cols-5 gap-3" data-slider-thumbs>
                        @foreach($images as $index => $image)
                            <button
                                type="button"
                                class="product-slider__thumb aspect-square overflow-hidden bg-primary-black {{ $index === 0 ? 'is-active' : '' }}"
                                data-slider-dot="{{ $index }}"
                                aria-label="Show image {{ $index + 1 }}"
                            >
                                <img src="{{ $image }}" alt="" class="h-full w-full object-cover" loading="lazy" draggable="false">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                @if($series->category)
                    <div class="text-xs uppercase tracking-wider text-gold-accent">{{ $series->category->localizedName() }}</div>
                @endif
                <h1 class="mt-3 text-3xl font-semibold text-white">{{ $series->localizedName() }}</h1>
                @if($series->localizedIntro())
                    <p class="mt-5 text-base leading-relaxed text-white/70">{{ $series->localizedIntro() }}</p>
                @endif
                <div class="mt-8">
                    <a href="{{ locale_url('contact') }}" class="btn-outline">{{ __('messages.contact_us') }}</a>
                </div>
            </div>
        </div>
    </section>

    @if($series->hasPdf())
        <section class="border-t border-white/10 bg-primary-black">
            <div class="section-pad">
                <h2 class="section-title">{{ __('messages.pdf_preview') }}</h2>
                <p class="section-sub">{{ __('messages.pdf_preview_hint') }}</p>
                <div
                    class="pdf-protect mt-10 overflow-hidden border border-white/10 bg-charcoal"
                    data-pdf-preview
                    data-pdf-url="{{ route('series.pdf', ['locale' => $currentLocale, 'slug' => $series->localizedSlug()]) }}"
                    data-watermark='@json($watermark)'
                >
                    <div class="pdf-protect__toolbar">{{ __('messages.pdf_preview_protected') }}</div>
                    <div class="pdf-protect__stage" data-pdf-stage>
                        <p class="px-6 py-16 text-center text-sm text-white/50">{{ __('messages.pdf_loading') }}</p>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($related->isNotEmpty())
        <section class="border-t border-white/10 bg-charcoal">
            <div class="section-pad">
                <h2 class="section-title">{{ __('messages.related_series') }}</h2>
                <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($related as $item)
                        @include('partials.series-card', ['series' => $item])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <div id="lightbox" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-6" onclick="this.classList.add('hidden'); this.classList.remove('flex');">
        <img id="lightbox-img" src="" alt="" class="max-h-full max-w-full object-contain">
    </div>
@endsection

@push('scripts')
<script>
    function openLightbox(src) {
        const box = document.getElementById('lightbox');
        document.getElementById('lightbox-img').src = src;
        box.classList.remove('hidden');
        box.classList.add('flex');
    }
</script>
@endpush
