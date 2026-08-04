@extends('layouts.app')

@section('title', $product->localizedName())

@section('content')
    <section class="bg-charcoal pt-28">
        <div class="mx-auto max-w-7xl px-4 py-8 text-sm text-white/50 lg:px-8">
            <a href="{{ locale_url() }}" class="hover:text-gold-accent">{{ __('messages.breadcrumb_home') }}</a>
            <span class="mx-2">/</span>
            @if($product->category)
                <a href="{{ locale_url('category/'.$product->category->localizedSlug()) }}" class="hover:text-gold-accent">{{ $product->category->localizedName() }}</a>
                <span class="mx-2">/</span>
            @endif
            <span class="text-white/80">{{ $product->localizedName() }}</span>
        </div>

        <div class="mx-auto grid max-w-7xl gap-12 px-4 pb-16 lg:grid-cols-2 lg:px-8">
            <div>
                @php $images = $product->imageUrls(); @endphp
                <div class="aspect-[4/3] overflow-hidden bg-primary-black">
                    @if(!empty($images[0]))
                        <img id="main-product-image" src="{{ $images[0] }}" alt="{{ $product->localizedName() }}" class="h-full w-full cursor-zoom-in object-cover" onclick="openLightbox(this.src)">
                    @else
                        <div class="flex h-full items-center justify-center text-white/30">SINO GOOD</div>
                    @endif
                </div>
                @if(count($images) > 1)
                    <div class="mt-3 grid grid-cols-4 gap-3">
                        @foreach($images as $image)
                            <button type="button" class="aspect-square overflow-hidden bg-primary-black" onclick="document.getElementById('main-product-image').src='{{ $image }}'">
                                <img src="{{ $image }}" alt="" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                @if($product->category)
                    <div class="text-xs uppercase tracking-wider text-gold-accent">{{ $product->category->localizedName() }}</div>
                @endif
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-white md:text-4xl">{{ $product->localizedName() }}</h1>
                @if($product->localizedShortDesc())
                    <p class="mt-5 text-base leading-relaxed text-white/70">{{ $product->localizedShortDesc() }}</p>
                @endif

                @if($product->localizedFullDesc())
                    <div class="prose prose-invert mt-8 max-w-none text-white/75">
                        {!! $product->localizedFullDesc() !!}
                    </div>
                @endif

                @if(!empty($product->specs))
                    <div class="mt-10">
                        <h2 class="text-lg font-medium text-white">{{ __('messages.specifications') }}</h2>
                        <table class="mt-4 w-full text-sm">
                            <tbody>
                                @foreach($product->specs as $spec)
                                    <tr class="border-b border-white/10">
                                        <th class="py-3 pr-4 text-left font-medium text-white/50">{{ $spec['key'] ?? '' }}</th>
                                        <td class="py-3 text-white/85">{{ $spec['value'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="mt-10 flex flex-wrap gap-4">
                    @if($product->pdfUrl())
                        <a href="{{ $product->pdfUrl() }}" target="_blank" class="btn-gold">{{ __('messages.download_pdf') }}</a>
                    @endif
                    <a href="{{ locale_url('contact') }}" class="btn-outline">{{ __('messages.contact_us') }}</a>
                </div>
            </div>
        </div>
    </section>

    @if($related->isNotEmpty())
        <section class="border-t border-white/10 bg-primary-black">
            <div class="section-pad">
                <h2 class="section-title">{{ __('messages.related_products') }}</h2>
                <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($related as $item)
                        @include('partials.product-card', ['product' => $item])
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
