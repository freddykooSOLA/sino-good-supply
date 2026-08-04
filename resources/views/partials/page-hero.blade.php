@php
    $heroImage = $image ?? null;
    $heroTitle = $title ?? '';
    $heroSubtitle = $subtitle ?? '';
    $compact = ! empty($compact);
@endphp

<section class="relative overflow-hidden bg-primary-black {{ $compact ? 'min-h-[42vh] pt-28' : 'min-h-[55vh] pt-28' }}">
    <div class="absolute inset-0">
        @if($heroImage)
            <img
                src="{{ public_storage_url($heroImage) }}"
                alt=""
                class="h-full w-full object-cover opacity-50"
            >
        @else
            <div class="h-full w-full bg-[radial-gradient(ellipse_at_top_right,_rgba(201,168,76,0.22),_transparent_55%),linear-gradient(160deg,#1a1a1a_0%,#2c2c2c_55%,#151515_100%)]"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-primary-black via-primary-black/60 to-primary-black/30"></div>
    </div>

    <div class="relative mx-auto flex {{ $compact ? 'min-h-[30vh]' : 'min-h-[42vh]' }} max-w-7xl flex-col justify-end px-4 pb-14 lg:px-8 lg:pb-16">
        <div class="fade-up max-w-3xl">
            <div class="text-sm font-semibold tracking-[0.28em] text-gold-accent">SINO GOOD</div>
            @if($heroTitle)
                <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white md:text-5xl">{{ $heroTitle }}</h1>
            @endif
            @if($heroSubtitle)
                <p class="mt-4 max-w-2xl text-base leading-relaxed text-white/70 md:text-lg">{{ $heroSubtitle }}</p>
            @endif
        </div>
    </div>
</section>
