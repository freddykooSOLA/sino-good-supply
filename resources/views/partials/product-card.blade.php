<a href="{{ locale_url('product/'.$product->localizedSlug()) }}" class="group block">
    <div class="aspect-[4/3] overflow-hidden bg-primary-black">
        @if($product->thumbnailUrl())
            <img src="{{ $product->thumbnailUrl() }}" alt="{{ $product->localizedName() }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#2a2a2a] to-[#1a1a1a] text-xs tracking-widest text-white/30">SINO GOOD</div>
        @endif
    </div>
    <div class="pt-4">
        @if($product->category)
            <div class="text-xs uppercase tracking-wider text-gold-accent/80">{{ $product->category->localizedName() }}</div>
        @endif
        <h3 class="mt-1 text-base font-medium text-white transition group-hover:text-gold-accent">{{ $product->localizedName() }}</h3>
        @if($product->localizedShortDesc())
            <p class="mt-2 line-clamp-2 text-sm text-white/55">{{ $product->localizedShortDesc() }}</p>
        @endif
        <span class="mt-3 inline-block text-sm text-gold-accent">{{ __('messages.view_details') }} →</span>
    </div>
</a>
