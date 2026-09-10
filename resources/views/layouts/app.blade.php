<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale ?? app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SINO GOOD') — SINO GOOD QY Supply Chain</title>
    <meta name="description" content="@yield('meta_description', __('messages.tagline'))">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-charcoal text-white antialiased">
    @php
        $contact = site_contact();
        $categories = nav_categories();
    @endphp

    <header class="site-header absolute inset-x-0 top-0 z-50">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-5 lg:px-8">
            <a href="{{ locale_url() }}" class="brand-mark shrink-0 text-xl font-semibold tracking-[0.12em] text-white md:text-2xl">
                SINO GOOD
            </a>

            <nav class="hidden items-center gap-8 lg:flex" aria-label="Main">
                <a href="{{ locale_url() }}" class="nav-link">{{ __('messages.nav_home') }}</a>
                <div class="group relative">
                    <button type="button" class="nav-link inline-flex items-center gap-1">
                        {{ __('messages.nav_products') }}
                        <svg class="h-3.5 w-3.5 opacity-70" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"/></svg>
                    </button>
                    <div class="invisible absolute left-0 top-full z-50 min-w-[240px] translate-y-2 bg-primary-black/95 opacity-0 shadow-xl backdrop-blur transition group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">
                        <a href="{{ locale_url('products') }}" class="block border-b border-white/10 px-4 py-3 text-sm text-gold-accent hover:bg-white/5">{{ __('messages.all_series') }}</a>
                        @foreach($categories as $category)
                            <a href="{{ locale_url('category/'.$category->localizedSlug()) }}" class="block px-4 py-3 text-sm text-white/85 hover:bg-white/5 hover:text-gold-accent">
                                {{ $category->localizedName() }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ locale_url('cases') }}" class="nav-link">{{ __('messages.nav_cases') }}</a>
                <a href="{{ locale_url('order-process') }}" class="nav-link">{{ __('messages.nav_order_process') }}</a>
                <a href="{{ locale_url('about') }}" class="nav-link">{{ __('messages.nav_about') }}</a>
                <a href="{{ locale_url('contact') }}" class="nav-link">{{ __('messages.nav_contact') }}</a>
            </nav>

            <div class="flex items-center gap-3">
                <div class="hidden items-center gap-1 text-xs uppercase tracking-wider sm:flex">
                    @foreach($locales as $code => $label)
                        <a href="{{ switch_locale_url($code) }}"
                           class="px-2 py-1 transition {{ ($currentLocale ?? 'en') === $code ? 'text-gold-accent' : 'text-white/60 hover:text-white' }}">
                            {{ $code === 'en' ? 'EN' : ($code === 'zh' ? '简' : '繁') }}
                        </a>
                        @if(! $loop->last)<span class="text-white/30">|</span>@endif
                    @endforeach
                </div>
                <button type="button" id="mobile-menu-btn" class="inline-flex h-10 w-10 items-center justify-center border border-white/20 text-white lg:hidden" aria-label="Menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden border-t border-white/10 bg-primary-black/95 lg:hidden">
            <div class="space-y-1 px-4 py-4">
                <a href="{{ locale_url() }}" class="block py-2 text-white/90">{{ __('messages.nav_home') }}</a>
                <a href="{{ locale_url('products') }}" class="block py-2 text-white/90">{{ __('messages.nav_products') }}</a>
                @foreach($categories as $category)
                    <a href="{{ locale_url('category/'.$category->localizedSlug()) }}" class="block py-1.5 pl-4 text-sm text-white/70">{{ $category->localizedName() }}</a>
                @endforeach
                <a href="{{ locale_url('cases') }}" class="block py-2 text-white/90">{{ __('messages.nav_cases') }}</a>
                <a href="{{ locale_url('order-process') }}" class="block py-2 text-white/90">{{ __('messages.nav_order_process') }}</a>
                <a href="{{ locale_url('about') }}" class="block py-2 text-white/90">{{ __('messages.nav_about') }}</a>
                <a href="{{ locale_url('contact') }}" class="block py-2 text-white/90">{{ __('messages.nav_contact') }}</a>
                <div class="flex gap-3 pt-3 text-sm">
                    @foreach($locales as $code => $label)
                        <a href="{{ switch_locale_url($code) }}" class="{{ ($currentLocale ?? 'en') === $code ? 'text-gold-accent' : 'text-white/60' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-white/10 bg-primary-black">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 md:grid-cols-3 lg:px-8">
            <div>
                <div class="text-lg font-semibold tracking-[0.12em] text-gold-accent">SINO GOOD</div>
                <p class="mt-3 max-w-sm text-sm leading-relaxed text-white/65">
                    {{ localized_setting($contact, 'company') ?: __('messages.company_footer') }}
                </p>
            </div>
            <div>
                <div class="text-sm font-medium uppercase tracking-wider text-white/40">{{ __('messages.contact_us') }}</div>
                <div class="mt-3 space-y-2 text-sm text-white/75">
                    @if(!empty($contact['address_en']) || !empty($contact['address_zh']))
                        <p>{{ localized_setting($contact, 'address') }}</p>
                    @endif
                    @if(!empty($contact['phone']))
                        <p><a href="tel:{{ $contact['phone'] }}" class="hover:text-gold-accent">{{ $contact['phone'] }}</a></p>
                    @endif
                    @if(!empty($contact['whatsapp']) && whatsapp_url($contact['whatsapp']))
                        <p>
                            <a href="{{ whatsapp_url($contact['whatsapp']) }}" class="hover:text-gold-accent" target="_blank" rel="noopener">
                                {{ __('messages.whatsapp') }}: {{ $contact['whatsapp'] }}
                            </a>
                        </p>
                    @endif
                    @if(!empty($contact['email']))
                        <p><a href="mailto:{{ $contact['email'] }}" class="hover:text-gold-accent">{{ $contact['email'] }}</a></p>
                    @endif
                    @include('partials.social-links', ['contact' => $contact])
                </div>
            </div>
            <div>
                <div class="text-sm font-medium uppercase tracking-wider text-white/40">{{ __('messages.nav_products') }}</div>
                <div class="mt-3 space-y-2 text-sm">
                    @foreach($categories->take(6) as $category)
                        <a href="{{ locale_url('category/'.$category->localizedSlug()) }}" class="block text-white/70 hover:text-gold-accent">{{ $category->localizedName() }}</a>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="border-t border-white/10 py-5 text-center text-xs text-white/40">
            &copy; {{ date('Y') }} {{ localized_setting($contact, 'company') ?: 'SINO GOOD QY SUPPLY CHAIN CO., LTD' }}
        </div>
    </footer>

    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>
</html>
