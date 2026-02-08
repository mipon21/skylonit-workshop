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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .guest-floating-start {
            box-shadow: 0 10px 40px -10px rgba(6, 182, 212, 0.5), 0 0 0 1px rgba(255,255,255,0.05);
        }
        .guest-floating-start:hover {
            box-shadow: 0 20px 50px -10px rgba(6, 182, 212, 0.6), 0 0 0 1px rgba(255,255,255,0.1);
        }
        /* Desktop: fixed sidebar + fixed header, only main scrolls */
        @media (min-width: 768px) {
            html, body { height: 100%; overflow: hidden !important; }
            .guest-layout-root { height: 100vh !important; overflow: hidden !important; }
            .guest-layout-sidebar { position: fixed !important; top: 0 !important; left: 0 !important; bottom: 0 !important; width: 16rem !important; z-index: 30 !important; display: flex !important; flex-direction: column !important; height: 100vh !important; overflow: hidden !important; }
            .guest-layout-sidebar-nav { flex: 1 1 0 !important; min-height: 0 !important; overflow-y: auto !important; }
            .guest-layout-header { position: fixed !important; top: 0 !important; left: 16rem !important; right: 0 !important; height: 3.5rem !important; z-index: 25 !important; background: rgb(15 23 42 / 0.95) !important; backdrop-filter: blur(8px); }
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
                background: #020617 !important;
                border-bottom: 1px solid #1e293b !important;
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
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-200 min-h-screen {{ request()->routeIs('guest.dashboard') ? 'guest-on-dashboard' : '' }}">
    <div class="guest-layout-root flex min-h-screen" x-data="{ sidebarOpen: false }">
        {{-- Mobile backdrop --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm md:hidden" aria-hidden="true"></div>
        {{-- Sidebar: fixed on desktop (no scroll); on mobile fixed drawer --}}
        <aside class="guest-layout-sidebar w-64 shrink-0 bg-slate-900/95 border-r border-slate-700/50 flex flex-col max-md:fixed max-md:inset-y-0 max-md:left-0 max-md:z-40 max-md:transition-transform max-md:duration-200 max-md:ease-out max-md:-translate-x-full"
               :class="sidebarOpen && 'max-md:translate-x-0'">
            <div class="p-5 border-b border-slate-700/50">
                <a href="{{ route('guest.dashboard') }}" class="flex items-center gap-2.5" @click="sidebarOpen = false">
                    @if(!empty($appLogoUrl))
                        <img src="{{ $appLogoUrl }}" alt="{{ config('app.name') }}" class="h-10 w-auto max-w-full object-contain object-left">
                    @else
                        <span class="text-2xl font-bold bg-gradient-to-r from-cyan-400 to-sky-500 bg-clip-text text-transparent">{{ config('app.name') }}</span>
                    @endif
                </a>
            </div>
            <nav class="guest-layout-sidebar-nav flex-1 p-3 space-y-0.5">
                <a href="{{ route('guest.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800/80 hover:text-white transition {{ request()->routeIs('guest.dashboard') ? 'bg-sky-500/20 text-sky-400' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('guest.projects.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800/80 hover:text-white transition {{ request()->routeIs('guest.projects.*') ? 'bg-sky-500/20 text-sky-400' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    Projects
                </a>
                <a href="{{ route('guest.links.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800/80 hover:text-white transition {{ request()->routeIs('guest.links.*') ? 'bg-sky-500/20 text-sky-400' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Live Links / APK
                </a>
                <a href="{{ route('guest.contact') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800/80 hover:text-white transition {{ request()->routeIs('guest.contact') ? 'bg-sky-500/20 text-sky-400' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Contact
                </a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 min-h-0">
            {{-- Header: fixed on desktop (stays visible); on mobile fixed via CSS --}}
            <header class="guest-layout-header guest-portal-mobile-header h-14 shrink-0 flex items-center justify-end px-6 border-b border-slate-800/80 bg-slate-900/50">
                <button type="button" @click="sidebarOpen = true" class="p-2 rounded-lg hover:bg-slate-800/80 text-slate-300 hover:text-white transition md:hidden" aria-label="Open menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg hover:bg-slate-800/80 text-slate-300 hover:text-white text-sm font-medium transition">Login</a>
            </header>

            <main class="guest-layout-main-wrap guest-portal-mobile-main flex-1 min-h-0 p-6 overflow-auto max-md:p-4 pb-28 max-md:pb-28">
                @if (session('success'))
                    <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 px-4 py-3 rounded-xl bg-red-500/20 border border-red-500/30 text-red-400 text-sm">
                        {{ session('error') }}
                    </div>
                @endif
                {{ $slot ?? '' }}
            </main>
        </div>
    </div>

    {{-- Floating buttons: wrapper with inline critical styles so they always show (no overflow/z-index issues) --}}
    <div id="guest-floating-ctas" style="position:fixed;bottom:0;right:0;z-index:2147483647;pointer-events:none;display:flex;flex-direction:column;align-items:flex-end;gap:12px;padding:20px;padding-bottom:24px;">
        {{-- Start Project – above WhatsApp (hidden on Contact page and on Dashboard) --}}
        @unless(request()->routeIs('guest.contact') || request()->routeIs('guest.dashboard'))
        <a href="{{ route('guest.contact') }}" aria-label="Start Project" style="pointer-events:auto;display:inline-flex;align-items:center;gap:8px;padding:12px 20px;border-radius:9999px;font-weight:600;color:#fff;text-decoration:none;background:linear-gradient(to right,#06b6d4,#0ea5e9);box-shadow:0 10px 40px -10px rgba(6,182,212,0.5);"
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
