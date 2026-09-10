@php
    $links = array_filter([
        'Facebook' => $contact['facebook_url'] ?? null,
        'Instagram' => $contact['instagram_url'] ?? null,
        'YouTube' => $contact['youtube_url'] ?? null,
    ]);
@endphp

@if(!empty($links))
    <div class="{{ $class ?? 'mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm' }}">
        @foreach($links as $label => $url)
            <a href="{{ $url }}" class="text-white/70 hover:text-gold-accent" target="_blank" rel="noopener">{{ $label }}</a>
        @endforeach
    </div>
@endif
