@php
    $images = $product->imageUrls();
    $hasSlider = count($images) > 1;
    $detailUrl = locale_url('product/'.$product->localizedSlug());
@endphp

<article class="group">
    <div
        class="product-slider relative aspect-[4/3] overflow-hidden bg-primary-black"
        @if($hasSlider) data-product-slider @endif
    >
        @if(! empty($images))
            <div class="product-slider__track flex h-full transition-transform duration-300 ease-out" data-slider-track>
                @foreach($images as $index => $image)
                    <a
                        href="{{ $detailUrl }}"
                        class="block h-full w-full shrink-0"
                        tabindex="{{ $index === 0 ? 0 : -1 }}"
                        @if($index > 0) aria-hidden="true" @endif
                    >
                        <img
                            src="{{ $image }}"
                            alt="{{ $product->localizedName() }}"
                            class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                            loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                            draggable="false"
                        >
                    </a>
                @endforeach
            </div>

            @if($hasSlider)
                <button
                    type="button"
                    class="product-slider__nav left-2"
                    data-slider-prev
                    aria-label="Previous image"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M12.79 5.23a.75.75 0 01-.02 1.06L9.06 10l3.71 3.71a.75.75 0 11-1.06 1.06l-4.25-4.25a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.08.02z"/></svg>
                </button>
                <button
                    type="button"
                    class="product-slider__nav right-2"
                    data-slider-next
                    aria-label="Next image"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7.21 14.77a.75.75 0 01.02-1.06L10.94 10 7.23 6.29a.75.75 0 111.06-1.06l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.08-.02z"/></svg>
                </button>
                <div class="absolute bottom-3 left-0 right-0 z-10 flex justify-center gap-1.5" data-slider-dots>
                    @foreach($images as $index => $image)
                        <button
                            type="button"
                            class="product-slider__dot {{ $index === 0 ? 'is-active' : '' }}"
                            data-slider-dot="{{ $index }}"
                            aria-label="Image {{ $index + 1 }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        @else
            <a href="{{ $detailUrl }}" class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#2a2a2a] to-[#1a1a1a] text-xs tracking-widest text-white/30">
                SINO GOOD
            </a>
        @endif
    </div>

    <a href="{{ $detailUrl }}" class="block pt-4">
        @if($product->category)
            <div class="text-xs uppercase tracking-wider text-gold-accent/80">{{ $product->category->localizedName() }}</div>
        @endif
        <h3 class="mt-1 text-base font-medium text-white transition group-hover:text-gold-accent">{{ $product->localizedName() }}</h3>
        @if($product->localizedShortDesc())
            <p class="mt-2 line-clamp-2 text-sm text-white/55">{{ $product->localizedShortDesc() }}</p>
        @endif
        <span class="mt-3 inline-block text-sm text-gold-accent">{{ __('messages.view_details') }} →</span>
    </a>
</article>
