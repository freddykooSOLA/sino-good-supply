@extends('layouts.app')

@section('title', __('messages.contact_us'))

@section('content')
    @php
        $mediaType = $contact['media_type'] ?? 'none';
        $mapSrc = $mediaType === 'map' ? google_maps_embed_src($contact['google_maps_embed'] ?? null) : null;
        $mediaImage = ($mediaType === 'image' && ! empty($contact['media_image']))
            ? public_storage_url($contact['media_image'])
            : null;
        $hasMedia = ($mediaType === 'image' && $mediaImage) || ($mediaType === 'map' && $mapSrc);
    @endphp

    @include('partials.page-hero', [
        'image' => $pageHero['image'] ?? null,
        'title' => localized_setting($pageHero, 'title') ?: __('messages.get_in_touch'),
        'subtitle' => localized_setting($pageHero, 'subtitle') ?: __('messages.tagline'),
    ])

    <section class="bg-charcoal">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 py-16 lg:grid-cols-2 lg:px-8">
            <div>
                <h2 class="text-xl font-medium text-white">{{ localized_setting($contact, 'company') ?: __('messages.company_footer') }}</h2>
                <div class="mt-6 space-y-4 text-sm text-white/70">
                    @if(localized_setting($contact, 'address'))
                        <p>{{ localized_setting($contact, 'address') }}</p>
                    @endif
                    @if(!empty($contact['phone']))
                        <p><a class="hover:text-gold-accent" href="tel:{{ $contact['phone'] }}">{{ $contact['phone'] }}</a></p>
                    @endif
                    @if(!empty($contact['email']))
                        <p><a class="hover:text-gold-accent" href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></p>
                    @endif
                </div>

                @if($hasMedia)
                    <div class="mt-10 overflow-hidden bg-primary-black">
                        @if($mediaType === 'map' && $mapSrc)
                            <iframe
                                src="{{ $mapSrc }}"
                                class="aspect-[4/3] w-full border-0"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen
                                title="Google Map"
                            ></iframe>
                        @elseif($mediaImage)
                            <img
                                src="{{ $mediaImage }}"
                                alt=""
                                class="aspect-[4/3] w-full object-cover"
                            >
                        @endif
                    </div>
                @endif
            </div>

            <div>
                @if(session('success'))
                    <div class="mb-6 border border-gold-accent/40 bg-gold-accent/10 px-4 py-3 text-sm text-gold-accent">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.send', ['locale' => $currentLocale]) }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="mb-2 block text-xs uppercase tracking-wider text-white/45">{{ __('messages.your_name') }}</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-white/15 bg-primary-black px-4 py-3 text-sm text-white outline-none focus:border-gold-accent">
                        @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-xs uppercase tracking-wider text-white/45">{{ __('messages.your_email') }}</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-white/15 bg-primary-black px-4 py-3 text-sm text-white outline-none focus:border-gold-accent">
                        @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-xs uppercase tracking-wider text-white/45">{{ __('messages.your_company') }}</label>
                        <input type="text" name="company" value="{{ old('company') }}" class="w-full border border-white/15 bg-primary-black px-4 py-3 text-sm text-white outline-none focus:border-gold-accent">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs uppercase tracking-wider text-white/45">{{ __('messages.your_message') }}</label>
                        <textarea name="message" rows="5" required class="w-full border border-white/15 bg-primary-black px-4 py-3 text-sm text-white outline-none focus:border-gold-accent">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn-gold">{{ __('messages.send_message') }}</button>
                </form>
            </div>
        </div>
    </section>
@endsection
