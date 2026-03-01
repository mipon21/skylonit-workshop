<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Skylon-IT WorkShop') }} — {{ $title ?? 'Portal' }}</title>
    <link rel="icon" href="{{ $appFaviconUrl ?? asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script>
    (function(){
        var initial = window.__INITIAL_THEME__;
        if (!initial) { try { var s = localStorage.getItem('app_theme'); if (s === 'light' || s === 'dark') initial = s; } catch(e) {} }
        if (!initial) initial = 'light';
        document.documentElement.dataset.theme = initial;
    })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-color-overrides')
    <style id="sidebar-active-primary">
        .app-layout-sidebar-nav a.theme-sidebar-active,
        .guest-layout-sidebar-nav a.theme-sidebar-active {
            background-color: #EF8121 !important;
            color: #ffffff !important;
        }
        .app-layout-sidebar-nav a.theme-sidebar-active svg,
        .guest-layout-sidebar-nav a.theme-sidebar-active svg,
        .app-layout-sidebar-nav a.theme-sidebar-active span,
        .guest-layout-sidebar-nav a.theme-sidebar-active span,
        .app-layout-sidebar-nav a.theme-sidebar-active *,
        .guest-layout-sidebar-nav a.theme-sidebar-active * {
            color: #ffffff !important;
        }
    </style>
    <style>
        .guest-floating-start {
            box-shadow: 0 10px 40px -10px rgba(239, 129, 33, 0.5), 0 0 0 1px rgba(255,255,255,0.05);
        }
        .guest-floating-start:hover {
            box-shadow: 0 20px 50px -10px rgba(239, 129, 33, 0.6), 0 0 0 1px rgba(255,255,255,0.1);
        }
        /* Desktop: fixed sidebar + fixed header, only main scrolls */
        @media (min-width: 768px) {
            html, body { height: 100%; overflow: hidden !important; }
            .guest-layout-root { height: 100vh !important; overflow: hidden !important; }
            .guest-layout-sidebar { position: fixed !important; top: 0 !important; left: 0 !important; bottom: 0 !important; width: 16rem !important; z-index: 30 !important; display: flex !important; flex-direction: column !important; height: 100vh !important; overflow: hidden !important; }
            .guest-layout-sidebar-nav { flex: 1 1 0 !important; min-height: 0 !important; overflow-y: auto !important; }
            .guest-layout-header { position: fixed !important; top: 0 !important; left: 16rem !important; right: 0 !important; height: 3.5rem !important; z-index: 25 !important; background: var(--navbar-bg) !important; backdrop-filter: blur(8px); }
            .guest-layout-main-wrap { margin-left: 16rem !important; margin-top: 3.5rem !important; height: calc(100vh - 3.5rem) !important; overflow: auto !important; }
        }
        @media (max-width: 767px) {
            body.guest-on-dashboard #guest-floating-ctas .guest-floating-start {
                display: none !important;
            }
        }
        @media (max-width: 767px) {
            .guest-portal-mobile-header {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                height: 56px !important;
                z-index: 25 !important;
                background: var(--navbar-bg) !important;
                border-bottom: 1px solid var(--navbar-border) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            .guest-portal-mobile-main {
                padding-top: 56px !important;
            }
        }
    </style>
    <style id="header-clock-primary-styles">
        /* Header clock: primary color + border (inline so it always applies) */
        #header-clock {
            border: 2px solid #EF8121 !important;
            border-radius: 15px !important;
            padding: 6px 14px !important;
            background: rgba(239, 129, 33, 0.12) !important;
            color: #EF8121 !important;
        }
        #header-clock .header-clock-time,
        #header-clock .header-clock-ampm,
        #header-clock .header-clock-date,
        #header-clock .header-clock-sep {
            color: #EF8121 !important;
        }
        #header-clock:hover {
            background: rgba(239, 129, 33, 0.2) !important;
            box-shadow: 0 0 0 1px #EF8121 !important;
        }
        /* Dark theme: solid primary bg, white text */
        [data-theme="dark"] #header-clock {
            background: #EF8121 !important;
            border-color: #EF8121 !important;
            color: #fff !important;
        }
        [data-theme="dark"] #header-clock .header-clock-time,
        [data-theme="dark"] #header-clock .header-clock-ampm,
        [data-theme="dark"] #header-clock .header-clock-date,
        [data-theme="dark"] #header-clock .header-clock-sep {
            color: #fff !important;
        }
        [data-theme="dark"] #header-clock:hover {
            background: #e07a1e !important;
            box-shadow: 0 0 0 1px #EF8121 !important;
        }
        /* Mobile: smaller padding and font */
        @media (max-width: 639px) {
            #header-clock {
                padding: 5px 10px !important;
                border-radius: 12px !important;
                gap: 0.2rem 0.4rem !important;
                min-width: 0 !important;
                flex-shrink: 1 !important;
            }
            #header-clock .header-clock-time { font-size: 0.75rem !important; }
            #header-clock .header-clock-ampm { font-size: 0.5rem !important; }
            #header-clock .header-clock-date { font-size: 0.65rem !important; }
            #header-clock .header-clock-sep { font-size: 0.6rem !important; }
        }
        @media (max-width: 380px) {
            #header-clock {
                padding: 4px 8px !important;
                border-radius: 10px !important;
            }
            #header-clock .header-clock-time { font-size: 0.7rem !important; }
            #header-clock .header-clock-date { font-size: 0.6rem !important; }
        }
    </style>
</head>
<body class="font-sans antialiased theme-body min-h-screen {{ request()->routeIs('guest.dashboard') ? 'guest-on-dashboard' : '' }}">
    <div class="guest-layout-root flex min-h-screen" x-data="{ sidebarOpen: false }">
        {{-- Mobile backdrop --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm md:hidden" aria-hidden="true"></div>
        {{-- Sidebar: fixed on desktop (no scroll); on mobile fixed drawer --}}
        <aside class="guest-layout-sidebar w-64 shrink-0 theme-sidebar-bg flex flex-col max-md:fixed max-md:inset-y-0 max-md:left-0 max-md:z-40 max-md:transition-transform max-md:duration-200 max-md:ease-out max-md:-translate-x-full"
               :class="sidebarOpen && 'max-md:translate-x-0'">
            <div class="p-5 border-b theme-border">
                <a href="{{ route('guest.dashboard') }}" class="flex items-center gap-2.5" @click="sidebarOpen = false">
                    @if(!empty($appLogoUrl))
                        <img src="{{ $appLogoUrl }}" alt="{{ config('app.name') }}" class="h-10 w-auto max-w-full object-contain object-left">
                    @else
                        <span class="text-2xl font-bold bg-gradient-to-r from-orange-400 to-amber-500 bg-clip-text text-transparent">{{ config('app.name') }}</span>
                    @endif
                </a>
            </div>
            <nav class="guest-layout-sidebar-nav flex-1 p-3 space-y-0.5">
                <a href="{{ route('guest.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('guest.dashboard') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('guest.projects.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('guest.projects.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    Projects
                </a>
                <a href="{{ route('guest.links.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('guest.links.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Live Links / APK
                </a>
                <a href="{{ route('guest.contact') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('guest.contact') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Contact
                </a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 min-h-0">
            {{-- Header: fixed on desktop (stays visible); on mobile fixed via CSS --}}
            <header class="guest-layout-header guest-portal-mobile-header h-14 shrink-0 flex items-center justify-between gap-2 sm:gap-3 px-4 sm:px-6 border-b theme-navbar-bg">
                <div class="flex items-center shrink-0">
                <button type="button" @click="sidebarOpen = true" class="p-2 -ml-1 rounded-lg theme-sidebar-link-hover theme-sidebar-text theme-sidebar-text-hover transition md:hidden" aria-label="Open menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                </div>
                <div class="flex items-center justify-end gap-1.5 sm:gap-2 md:gap-3 min-w-0 flex-1">
                @php
                    $now = now();
                    $h = (int) $now->format('g');
                    $m = (int) $now->format('i');
                    $s = (int) $now->format('s');
                    $ampm = $now->format('A');
                    $timeStr = sprintf('%02d:%02d:%02d', $h, $m, $s);
                    $dateStr = $now->format('M j, Y');
                @endphp
                <div class="header-clock hidden sm:flex items-center shrink-0" id="header-clock" aria-live="polite" aria-label="Current time and date">
                    <span class="header-clock-time" id="header-clock-time">{{ $timeStr }}</span><span class="header-clock-ampm" id="header-clock-ampm">{{ $ampm }}</span>
                    <span class="header-clock-sep" aria-hidden="true">·</span>
                    <span class="header-clock-date" id="header-clock-date">{{ $dateStr }}</span>
                </div>
                <script>
                (function(){
                    var pad = function(n) { return (n < 10 ? '0' : '') + n; };
                    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    function update() {
                        var d = new Date();
                        var h = d.getHours(), m = d.getMinutes(), s = d.getSeconds();
                        var h12 = h % 12 || 12;
                        var timeEl = document.getElementById('header-clock-time');
                        var ampmEl = document.getElementById('header-clock-ampm');
                        var dateEl = document.getElementById('header-clock-date');
                        if (timeEl) timeEl.textContent = pad(h12) + ':' + pad(m) + ':' + pad(s);
                        if (ampmEl) ampmEl.textContent = h >= 12 ? 'PM' : 'AM';
                        if (dateEl) dateEl.textContent = months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
                    }
                    setInterval(update, 1000);
                })();
                </script>
                <button type="button" onclick="window.theme && window.theme.toggleTheme({})" class="p-2 rounded-lg theme-sidebar-link-hover theme-sidebar-text theme-sidebar-text-hover transition shrink-0" aria-label="Toggle theme" title="Toggle light/dark">
                    <svg class="w-5 h-5 theme-sun-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4  0 018 0z"/></svg>
                    <svg class="w-5 h-5 theme-moon-icon" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                <a href="{{ route('login') }}" class="px-2.5 py-1.5 sm:px-3 sm:py-2 rounded-lg theme-sidebar-link-hover theme-sidebar-text theme-sidebar-text-hover text-sm font-medium transition shrink-0">Login</a>
                </div>
            </header>

            <main class="guest-layout-main-wrap guest-portal-mobile-main flex-1 min-h-0 p-6 overflow-auto max-md:p-4 pb-28 max-md:pb-28">
                @if (session('success'))
                    <div class="mb-4 px-4 py-3 rounded-xl theme-status-success-bg border border-emerald-500/30 text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 px-4 py-3 rounded-xl theme-status-danger-bg border border-red-500/30 text-sm">
                        {{ session('error') }}
                    </div>
                @endif
                {{ $slot ?? '' }}
            </main>
            @include('layouts.partials.global-footer')
        </div>
    </div>

    {{-- Floating buttons: wrapper with inline critical styles so they always show (no overflow/z-index issues) --}}
    <div id="guest-floating-ctas" style="position:fixed;bottom:0;right:0;z-index:2147483647;pointer-events:none;display:flex;flex-direction:column;align-items:flex-end;gap:12px;padding:20px;padding-bottom:24px;">
        {{-- Start Project – above WhatsApp (hidden on Contact page and on Dashboard) --}}
        @unless(request()->routeIs('guest.contact') || request()->routeIs('guest.dashboard'))
        <a href="{{ route('guest.contact') }}" aria-label="Start Project" style="pointer-events:auto;display:inline-flex;align-items:center;gap:8px;padding:12px 20px;border-radius:9999px;font-weight:600;color:#fff;text-decoration:none;background:linear-gradient(to right,#EF8121,#e07a1e);box-shadow:0 10px 40px -10px rgba(239,129,33,0.5);"
           class="guest-floating-start hover:opacity-95 transition max-md:px-4 max-md:py-3">
            <svg class="w-5 h-5 max-md:w-4 max-md:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span class="max-md:sr-only">Start Project</span>
        </a>
        @endunless
        {{-- WhatsApp – bottom-right corner --}}
        <a href="https://wa.me/{{ $whatsappNumber ?? '8801743233833' }}" target="_blank" rel="noopener noreferrer" aria-label="Contact on WhatsApp"
           style="pointer-events:auto;display:flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;background:#10b981;color:#fff;text-decoration:none;box-shadow:0 10px 25px -5px rgba(0,0,0,.25);"
           class="hover:bg-emerald-600 transition max-md:w-12 max-md:h-12">
            <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24" class="max-md:w-6 max-md:h-6"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        </a>
    </div>
</body>
</html>
