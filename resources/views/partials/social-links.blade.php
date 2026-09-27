@php
    $links = array_filter([
        'Facebook' => $contact['facebook_url'] ?? null,
        'Instagram' => $contact['instagram_url'] ?? null,
        'YouTube' => $contact['youtube_url'] ?? null,
    ]);
    $icons = [
        'Facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 w-5"><path fill="#1877F2" d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h2.6l.4-3H13v-2c0-.6.4-1 1-1z"/></svg>',
        'Instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 w-5"><path fill="#E4405F" d="M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4zm5 4.5A4.5 4.5 0 1 0 16.5 12 4.5 4.5 0 0 0 12 7.5zm6.2-.9a1.1 1.1 0 1 0 1.1 1.1 1.1 1.1 0 0 0-1.1-1.1zM12 9.2A2.8 2.8 0 1 1 9.2 12 2.8 2.8 0 0 1 12 9.2z"/></svg>',
        'YouTube' => '<svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 w-5"><path fill="#FF0000" d="M23 12.2s0-3.2-.4-4.6a3 3 0 0 0-2.1-2.1C18.9 5 12 5 12 5s-6.9 0-8.5.5a3 3 0 0 0-2.1 2.1C1 9 1 12.2 1 12.2s0 3.2.4 4.6a3 3 0 0 0 2.1 2.1C5.1 19.4 12 19.4 12 19.4s6.9 0 8.5-.5a3 3 0 0 0 2.1-2.1c.4-1.4.4-4.6.4-4.6z"/><path fill="#ffffff" d="M9.8 15.5v-6.6l6.2 3.3z"/></svg>',
    ];
@endphp

@if(!empty($links))
    <div class="{{ $class ?? 'mt-4 flex flex-wrap gap-3' }}">
        @foreach($links as $label => $url)
            <a href="{{ $url }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white shadow-sm" target="_blank" rel="noopener" aria-label="{{ $label }}">
                {!! $icons[$label] !!}
            </a>
        @endforeach
    </div>
@endif
