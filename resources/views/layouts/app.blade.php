<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Skylon-IT WorkShop') }} — {{ $title ?? 'Dashboard' }}</title>
    <link rel="icon" href="{{ $appFaviconUrl ?? asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @if(Auth::check())
    <script>window.__INITIAL_THEME__ = @json(Auth::user()->theme_preference ?? 'light');</script>
    @endif
    {{-- No-FOUC: set theme before first paint. Default = light; user can change via toggle. --}}
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
        /* Desktop: fixed sidebar + fixed header, only main scrolls */
        @media (min-width: 768px) {
            html, body { height: 100%; overflow: hidden !important; }
            .app-layout-root { height: 100vh !important; overflow: hidden !important; }
            .app-layout-sidebar { position: fixed !important; top: 0 !important; left: 0 !important; bottom: 0 !important; width: 16rem !important; z-index: 30 !important; display: flex !important; flex-direction: column !important; height: 100vh !important; overflow: hidden !important; }
            .app-layout-sidebar-nav { flex: 1 1 0 !important; min-height: 0 !important; overflow-y: auto !important; }
            .app-layout-header { position: fixed !important; top: 0 !important; left: 16rem !important; right: 0 !important; height: 3.5rem !important; z-index: 25 !important; background: var(--navbar-bg) !important; backdrop-filter: blur(8px); }
            .app-layout-main-wrap { margin-left: 16rem !important; margin-top: 3.5rem !important; height: calc(100vh - 3.5rem) !important; overflow: auto !important; }
        }
        @media (max-width: 767px) {
            .app-layout-mobile-header {
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
            .app-layout-mobile-main {
                padding-top: 56px !important;
            }
        }
    </style>
    <style id="header-clock-primary-styles">
        /* Header clock: solid primary bg + white text (light and dark) */
        #header-clock {
            border: 2px solid #EF8121 !important;
            border-radius: 12px !important;
            padding: 6px 14px !important;
            background: #EF8121 !important;
            color: #fff !important;
            min-width: 12rem !important;
            box-sizing: border-box !important;
        }
        #header-clock .header-clock-time,
        #header-clock .header-clock-ampm,
        #header-clock .header-clock-date,
        #header-clock .header-clock-sep {
            color: #fff !important;
        }
        #header-clock .header-clock-time { font-variant-numeric: tabular-nums !important; }
        #header-clock:hover {
            background: #e07a1e !important;
            box-shadow: 0 0 0 1px #EF8121 !important;
        }
        /* Dark theme: same look */
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
                min-width: 10rem !important;
                flex-shrink: 0 !important;
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
                min-width: 8.5rem !important;
            }
            #header-clock .header-clock-time { font-size: 0.7rem !important; }
            #header-clock .header-clock-date { font-size: 0.6rem !important; }
        }
        /* Header USD/BDT rate: solid primary bg + white text (same as clock) */
        #header-usd-bdt {
            border: 2px solid #EF8121 !important;
            border-radius: 12px !important;
            padding: 6px 12px !important;
            background: #EF8121 !important;
            color: #fff !important;
            font-weight: 600 !important;
            gap: 0.35rem 0.5rem !important;
        }
        #header-usd-bdt:hover { background: #e07a1e !important; box-shadow: 0 0 0 1px #EF8121 !important; }
        [data-theme="dark"] #header-usd-bdt { background: #EF8121 !important; border-color: #EF8121 !important; color: #fff !important; }
        [data-theme="dark"] #header-usd-bdt:hover { background: #e07a1e !important; }
        #header-usd-bdt .header-usd-bdt-label { font-weight: 700 !important; letter-spacing: 0.02em; opacity: 0.95; color: #fff !important; }
        #header-usd-bdt .header-usd-bdt-eq { opacity: 0.9; font-weight: 500; color: #fff !important; }
        #header-usd-bdt .header-usd-bdt-result { min-width: 2.5rem; text-align: right; font-variant-numeric: tabular-nums; color: #fff !important; }
        @media (max-width: 639px) {
            #header-usd-bdt { padding: 5px 10px !important; border-radius: 12px !important; font-size: 0.75rem !important; gap: 0.25rem 0.4rem !important; }
        }
        @media (max-width: 380px) {
            #header-usd-bdt { padding: 4px 8px !important; border-radius: 10px !important; font-size: 0.7rem !important; }
        }
        #header-usd-bdt .header-usd-input {
            width: 2.75rem !important; max-width: 4.5rem; font-size: inherit !important; font-weight: 600; background: transparent; border: 1px solid transparent; border-radius: 4px; color: #fff !important; padding: 0 3px; -moz-appearance: textfield;
        }
        #header-usd-bdt .header-usd-input::placeholder { color: rgba(255,255,255,0.7); }
        #header-usd-bdt .header-usd-input:hover, #header-usd-bdt .header-usd-input:focus { border-color: rgba(255,255,255,0.5); outline: none; }
        #header-usd-bdt .header-usd-input::-webkit-outer-spin-button, #header-usd-bdt .header-usd-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
    @if((request()->routeIs('projects.*') || request()->routeIs('dashboard')) && Auth::user()->isAdmin())
    <script>
    (function(){
        try {
            if (JSON.parse(localStorage.getItem('paymentBlur') || 'false')) {
                document.documentElement.classList.add('payment-blur-active');
            }
        } catch(e) {}
    })();
    </script>
    <style>html.payment-blur-active .payment-amount{filter:blur(5px)!important;user-select:none!important}</style>
    @endif
    {{-- Calculator popup: ensure grid layout and button styles --}}
    @if(Auth::user()->isAdmin())
    <style>
    .calculator-keypad { display: grid !important; grid-template-columns: repeat(4, 1fr) !important; }
    .calculator-keypad .calc-btn { border-radius: 0.75rem; font-size: 1.125rem; font-weight: 500; transition: transform 0.1s, opacity 0.15s; }
    .calculator-keypad .calc-btn:active { transform: scale(0.97); }
    .calculator-keypad .calc-btn-num { background: var(--btn-secondary-bg, rgba(0,0,0,0.06)); color: var(--text-primary, #1e293b); border: 1px solid var(--border-color, rgba(0,0,0,0.1)); }
    .calculator-keypad .calc-btn-num:hover { opacity: 0.9; }
    .calculator-keypad .calc-btn-op { background: rgba(239, 129, 33, 0.2); color: #EF8121; border: 1px solid rgba(239, 129, 33, 0.4); }
    .calculator-keypad .calc-btn-op:hover { background: rgba(239, 129, 33, 0.3); }
    .calculator-keypad .calc-btn-equals { background: #EF8121; color: #fff; border: none; font-weight: 600; }
    .calculator-keypad .calc-btn-equals:hover { background: #e07a1e; }
    [data-theme="dark"] .calculator-keypad .calc-btn-num { background: rgba(255,255,255,0.08); color: #e2e8f0; border-color: rgba(255,255,255,0.12); }
    /* Calculator popup: on desktop, position overlay to the right of sidebar so popup is centered in main content */
    @media (min-width: 768px) {
        .calculator-overlay { left: 16rem !important; }
    }
    </style>
    @endif
</head>
<body class="font-sans antialiased theme-body min-h-screen">
    <div class="app-layout-root flex min-h-screen" x-data="{ sidebarOpen: false, calculatorOpen: false }">
        {{-- Mobile backdrop: close sidebar on tap --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm md:hidden" aria-hidden="true"></div>
        {{-- Left Sidebar: fixed on desktop (no scroll); on mobile fixed drawer, hidden by default --}}
        <aside class="app-layout-sidebar w-64 shrink-0 theme-sidebar-bg flex flex-col max-md:fixed max-md:inset-y-0 max-md:left-0 max-md:z-40 max-md:transition-transform max-md:duration-200 max-md:ease-out max-md:-translate-x-full"
               :class="sidebarOpen && 'max-md:translate-x-0'">
            <div class="p-5 border-b theme-border">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5" @click="sidebarOpen = false">
                    @if(!empty($appLogoUrl))
                        <img src="{{ $appLogoUrl }}" alt="{{ config('app.name') }}" class="h-10 w-auto max-w-full object-contain object-left">
                    @else
                        <span class="text-2xl font-bold bg-gradient-to-r from-orange-400 to-amber-500 bg-clip-text text-transparent">{{ config('app.name') }}</span>
                    @endif
                </a>
            </div>
            <nav class="app-layout-sidebar-nav flex-1 p-3 space-y-0.5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('dashboard') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                @if(Auth::user()->isAdmin())
                <a href="{{ route('clients.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('clients.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Clients
                </a>
                @endif
                <a href="{{ route('projects.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('projects.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    Projects
                </a>
                @if((Auth::user()->isDeveloper() || Auth::user()->isSales()) && isset($sidebarPayouts) && $sidebarPayouts->isNotEmpty())
                <div class="pt-2 pb-1" x-data="{ paymentsOpen: true }">
                    <button type="button" @click="paymentsOpen = !paymentsOpen" class="flex items-center gap-3 w-full px-3 py-2 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition" @click.stop="sidebarOpen = false">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="font-medium">Payments</span>
                        <span class="ml-auto text-xs theme-text-muted">{{ $sidebarPayouts->count() }}</span>
                        <svg class="w-4 h-4 theme-text-muted transition-transform" :class="paymentsOpen && 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                    <div x-show="paymentsOpen" x-transition class="mt-0.5 space-y-0.5">
                        @foreach($sidebarPayouts as $payout)
                        <a href="{{ route('projects.show', $payout->project) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg theme-text-secondary theme-sidebar-link-hover transition text-sm truncate group" @click="sidebarOpen = false">
                            <span class="truncate flex-1 min-w-0" title="{{ $payout->project->project_name ?? '' }}">{{ $payout->project->project_name ?? '—' }}</span>
                            <span @class([
                                'shrink-0 px-1.5 py-0.5 rounded text-xs font-medium',
                                'bg-amber-500/20 text-amber-400' => $payout->status === 'due',
                                'bg-emerald-500/20 text-emerald-400' => $payout->status === 'paid',
                                'theme-sidebar-active' => $payout->status === 'upcoming',
                                'bg-slate-500/20 text-slate-400' => in_array($payout->status, ['partial']),
                            ])>{{ \App\Models\ProjectPayout::statusLabel($payout->status) }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
                @if(Auth::user()->isAdmin())
                <a href="{{ route('developers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('developers.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    Developers
                </a>
                <a href="{{ route('sales.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('sales.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Sales
                </a>
                @endif
                @if(Auth::user()->isClient())
                <a href="{{ route('client.payments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('client.payments.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Payments
                </a>
                @endif
                @if(Auth::user()->isAdmin() || Auth::user()->isClient())
                <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('invoices.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Invoices
                </a>
                @endif
                @if(Auth::user()->isAdmin())
                <a href="{{ route('revenue.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('revenue.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Loss / Profit
                </a>
                <a href="{{ route('investments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('investments.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Finance → Investors
                </a>
                <a href="{{ route('internal-expenses.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('internal-expenses.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Finance → Internal Expenses
                </a>
                <a href="{{ route('internal-income.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('internal-income.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Finance → Internal Income
                </a>
                <a href="{{ route('leads.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('leads.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Marketing → Leads
                </a>
                <a href="{{ route('hot-offers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('hot-offers.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                    Marketing → Hot Offers
                </a>
                <a href="{{ route('testimonials.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('testimonials.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    Marketing → Testimonials
                </a>
                <a href="{{ route('email-templates.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('email-templates.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Email Templates
                </a>
                <a href="{{ route('email-footer.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('email-footer.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    Email Footer
                </a>
                <a href="{{ route('google-sync.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl theme-sidebar-text theme-sidebar-link-hover theme-sidebar-text-hover transition {{ request()->routeIs('google-sync.*') ? 'theme-sidebar-active' : '' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Google Sync
                </a>
                @endif
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 min-h-0">
            {{-- Top Bar: fixed on desktop (stays visible); fixed on mobile via CSS --}}
            <header class="app-layout-header app-layout-mobile-header h-14 shrink-0 flex items-center justify-between gap-2 sm:gap-3 px-4 sm:px-6 border-b theme-navbar-bg">
                <div class="flex items-center shrink-0">
                <button type="button" @click="sidebarOpen = true" class="p-2 -ml-1 rounded-lg theme-sidebar-link-hover theme-sidebar-text theme-sidebar-text-hover transition md:hidden" aria-label="Open menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                </div>
                <div class="flex items-center justify-end gap-1.5 sm:gap-2 md:gap-3 min-w-0 flex-1">
                @if(Auth::user()->isAdmin())
                <div id="header-usd-bdt" class="hidden sm:flex items-center shrink-0 header-usd-bdt-pill" aria-label="Live USD to BDT converter" title="Change USD amount to convert to BDT (live rate)">
                    <span class="header-usd-bdt-label">USD</span>
                    <input type="number" id="header-usd-input" min="0.01" step="0.01" value="1" class="header-usd-input" aria-label="USD amount">
                    <span class="header-usd-bdt-eq">=</span>
                    <span id="header-usd-bdt-result" class="header-usd-bdt-result">—</span>
                    <span class="header-usd-bdt-label">BDT</span>
                </div>
                <script>
                (function(){
                    var CACHE_KEY = 'usd_bdt_rate', TS_KEY = 'usd_bdt_ts', CACHE_HOURS = 6, API = 'https://open.er-api.com/v6/latest/USD';
                    var container = document.getElementById('header-usd-bdt');
                    var inputEl = document.getElementById('header-usd-input');
                    var resultEl = document.getElementById('header-usd-bdt-result');
                    if (!container || !inputEl || !resultEl) return;
                    function updateResult() {
                        var rate = parseFloat(container.getAttribute('data-rate'));
                        var usd = parseFloat(inputEl.value);
                        if (!isNaN(rate) && rate > 0 && !isNaN(usd) && usd >= 0) {
                            resultEl.textContent = (usd * rate).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        } else {
                            resultEl.textContent = '—';
                        }
                    }
                    inputEl.addEventListener('input', updateResult);
                    inputEl.addEventListener('change', function(){ if (isNaN(parseFloat(this.value)) || parseFloat(this.value) < 0.01) this.value = '1'; updateResult(); });
                    function setRate(rate) {
                        container.setAttribute('data-rate', String(rate));
                        updateResult();
                    }
                    try {
                        var cached = localStorage.getItem(CACHE_KEY), ts = parseInt(localStorage.getItem(TS_KEY) || '0', 10);
                        var now = Date.now(), maxAge = CACHE_HOURS * 60 * 60 * 1000;
                        if (cached && (now - ts) < maxAge) { setRate(parseFloat(cached)); return; }
                    } catch(e) {}
                    fetch(API).then(function(r){ return r.json(); }).then(function(data){
                        if (data && data.rates && typeof data.rates.BDT === 'number') {
                            var rate = data.rates.BDT;
                            try { localStorage.setItem(CACHE_KEY, String(rate)); localStorage.setItem(TS_KEY, String(Date.now())); } catch(e) {}
                            setRate(rate);
                        }
                    }).catch(function(){});
                })();
                </script>
                @endif
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
                @if(Auth::user()->isClient())
                <a href="{{ route('dashboard') }}" id="client-notification-bell" class="md:hidden flex items-center justify-center relative p-2 rounded-lg theme-sidebar-link-hover theme-sidebar-text theme-sidebar-text-hover transition" aria-label="Notifications">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span id="client-notification-badge" class="absolute -top-0.5 -right-0.5 min-w-[1.25rem] h-5 px-1 flex items-center justify-center rounded-full bg-orange-500 text-white text-xs font-semibold {{ ($clientUnreadCount ?? 0) > 0 ? '' : 'hidden' }}" data-count="{{ $clientUnreadCount ?? 0 }}">{{ ($clientUnreadCount ?? 0) > 99 ? '99+' : ($clientUnreadCount ?? 0) }}</span>
                </a>
                @endif
                @if((request()->routeIs('projects.*') || request()->routeIs('dashboard')) && Auth::user()->isAdmin())
                <div x-data="{
                    paymentBlur: (function(){ try { return JSON.parse(localStorage.getItem('paymentBlur') || 'false'); } catch(e) { return false; } })(),
                    syncBlur() {
                        const active = !!this.paymentBlur;
                        document.documentElement.classList.toggle('payment-blur-active', active);
                        localStorage.setItem('paymentBlur', JSON.stringify(active));
                    },
                    init() {
                        this.$watch('paymentBlur', () => this.syncBlur());
                        this.syncBlur();
                    }
                }" x-effect="syncBlur()" class="flex items-center shrink-0">
                    <button type="button" @click="paymentBlur = !paymentBlur" :class="paymentBlur ? 'theme-status-warning-bg border-amber-500/50' : 'theme-btn-secondary'" class="p-2 sm:px-3 sm:py-1.5 rounded-lg border text-sm font-medium transition flex items-center gap-1.5 shrink-0" :title="paymentBlur ? 'Payment numbers blurred – click to show' : 'Click to blur payment numbers'">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span class="hidden sm:inline" x-text="paymentBlur ? 'Show' : 'Blur'">Blur</span>
                    </button>
                </div>
                @endif
                @if(Auth::user()->isAdmin())
                <button type="button" @click="calculatorOpen = true" class="p-2 rounded-lg theme-sidebar-link-hover theme-text theme-sidebar-text-hover transition shrink-0" aria-label="Open calculator" title="Calculator">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </button>
                @endif
                {{-- Theme toggle --}}
                <button type="button" @click="window.theme && window.theme.toggleTheme({ saveUrl: '{{ route('profile.theme.update') }}', csrf: document.querySelector('meta[name=csrf-token]')?.getAttribute('content') })" class="p-2 rounded-lg theme-sidebar-link-hover theme-sidebar-text theme-sidebar-text-hover transition shrink-0" aria-label="Toggle theme" title="Toggle light/dark">
                    <svg class="w-5 h-5 theme-sun-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg class="w-5 h-5 theme-moon-icon" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                <div x-data="{ open: false }" class="relative flex items-center min-w-0 shrink">
                    <button @click="open = !open" class="flex items-center gap-1.5 sm:gap-2 pl-1 pr-2 py-1.5 sm:px-3 sm:py-2 rounded-lg theme-sidebar-link-hover transition min-w-0 max-w-full">
                        <span class="text-sm font-medium theme-text-secondary truncate max-w-[4.5rem] sm:max-w-[8rem] md:max-w-none">{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4 theme-text-muted shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-1 w-48 py-1 theme-dropdown-bg border rounded-xl z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm theme-text-secondary theme-sidebar-link-hover">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm theme-text-secondary theme-sidebar-link-hover">Log Out</button>
                        </form>
                    </div>
                </div>
                </div>
            </header>
            <main class="app-layout-main-wrap app-layout-mobile-main flex-1 min-h-0 p-6 overflow-auto max-md:p-4">
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

        {{-- Calculator popup (Admin only) --}}
        @if(Auth::user()->isAdmin())
        <div x-show="calculatorOpen" x-cloak x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @keydown.escape.window="calculatorOpen = false" class="calculator-overlay fixed inset-0 z-[60] flex items-center justify-center p-4" aria-modal="true" role="dialog" aria-label="Calculator">
            <div @click="calculatorOpen = false" class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
            <div @click.stop class="relative w-full max-w-[320px] theme-card-bg border theme-border rounded-2xl shadow-2xl overflow-hidden" x-data="{
                display: '0',
                currentNum: '0',
                previous: null,
                op: null,
                fresh: true,
                press(k) {
                    if (k === 'C') {
                        this.display = '0'; this.currentNum = '0'; this.previous = null; this.op = null; this.fresh = true;
                        return;
                    }
                    if (k === '⌫') {
                        if (this.currentNum.length > 1) {
                            this.currentNum = this.currentNum.slice(0, -1);
                        } else if (this.currentNum.length === 1) {
                            this.currentNum = '0';
                        } else return;
                        this.display = (this.previous != null ? this.previous + ' ' + this.op + ' ' : '') + this.currentNum;
                        return;
                    }
                    if (k === '±') {
                        this.currentNum = String(-parseFloat(this.currentNum || '0'));
                        this.display = (this.previous != null ? this.previous + ' ' + this.op + ' ' : '') + this.currentNum;
                        return;
                    }
                    if (k === '.') {
                        if (this.fresh) { this.currentNum = '0.'; }
                        else if (!this.currentNum.includes('.')) { this.currentNum += '.'; }
                        this.display = (this.previous != null ? this.previous + ' ' + this.op + ' ' : '') + this.currentNum;
                        this.fresh = false;
                        return;
                    }
                    if (['+','−','×','÷'].includes(k)) {
                        let num = parseFloat(this.currentNum);
                        if (this.previous != null && this.op) {
                            let r = this.calc(this.previous, this.op, num);
                            this.display = (Number.isFinite(r) ? String(r) : 'Error') + ' ' + k + ' ';
                            this.previous = Number.isFinite(r) ? r : null;
                            this.currentNum = '';
                        } else {
                            this.display = this.display + ' ' + k + ' ';
                            this.previous = num;
                            this.currentNum = '';
                        }
                        this.op = k;
                        this.fresh = true;
                        return;
                    }
                    if (k === '=') {
                        if (this.op && this.previous != null) {
                            let num = parseFloat(this.currentNum);
                            let r = this.calc(this.previous, this.op, num);
                            this.display = this.display + ' = ' + (Number.isFinite(r) ? String(r) : 'Error');
                            this.previous = null;
                            this.op = null;
                            this.currentNum = Number.isFinite(r) ? String(r) : '0';
                            this.fresh = true;
                        }
                        return;
                    }
                    if (/^\d$/.test(k)) {
                        if (this.fresh && !this.op) {
                            this.display = k;
                            this.currentNum = k;
                        } else if (this.fresh && this.op) {
                            this.display = this.display + k;
                            this.currentNum = k;
                        } else {
                            this.display = this.display + k;
                            this.currentNum = this.currentNum + k;
                        }
                        this.fresh = false;
                    }
                },
                calc(a, op, b) {
                    if (op === '+') return a + b;
                    if (op === '−') return a - b;
                    if (op === '×') return a * b;
                    if (op === '÷') return b === 0 ? NaN : a / b;
                    return b;
                }
            }">
                <div class="p-4 pb-2 flex items-center justify-between">
                    <span class="text-sm font-medium theme-text-muted">Calculator</span>
                    <button type="button" @click="calculatorOpen = false" class="p-1.5 rounded-lg theme-sidebar-link-hover theme-text-muted theme-sidebar-text-hover transition" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-4 pb-1">
                    <div class="theme-card-bg border theme-border rounded-xl px-4 py-4 min-h-[4rem] flex items-end justify-end">
                        <span class="text-xl sm:text-2xl font-semibold tabular-nums theme-text-primary max-w-full text-right break-all" x-text="display"></span>
                    </div>
                </div>
                <div class="p-4 pt-3 calculator-keypad" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;">
                    <button type="button" @click="press('C')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">C</button>
                    <button type="button" @click="press('±')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">±</button>
                    <button type="button" @click="press('⌫')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">⌫</button>
                    <button type="button" @click="press('÷')" class="calc-btn calc-btn-op" style="min-height: 2.75rem;">÷</button>
                    <button type="button" @click="press('7')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">7</button>
                    <button type="button" @click="press('8')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">8</button>
                    <button type="button" @click="press('9')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">9</button>
                    <button type="button" @click="press('×')" class="calc-btn calc-btn-op" style="min-height: 2.75rem;">×</button>
                    <button type="button" @click="press('4')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">4</button>
                    <button type="button" @click="press('5')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">5</button>
                    <button type="button" @click="press('6')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">6</button>
                    <button type="button" @click="press('−')" class="calc-btn calc-btn-op" style="min-height: 2.75rem;">−</button>
                    <button type="button" @click="press('1')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">1</button>
                    <button type="button" @click="press('2')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">2</button>
                    <button type="button" @click="press('3')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">3</button>
                    <button type="button" @click="press('+')" class="calc-btn calc-btn-op" style="min-height: 2.75rem;">+</button>
                    <button type="button" @click="press('0')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">0</button>
                    <button type="button" @click="press('.')" class="calc-btn calc-btn-num" style="min-height: 2.75rem;">.</button>
                    <button type="button" @click="press('=')" class="calc-btn calc-btn-equals" style="min-height: 2.75rem; grid-column: span 2;">=</button>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Client notification popups: server-rendered + polling for new ones (no refresh) --}}
    @auth
    @if(Auth::user()->isClient() && Auth::user()->client)
    @php
        $clientUnread = \App\Models\ClientNotification::getLatestUnreadForPopups(Auth::user()->client->id);
        $clientUnread->load(['project:id,project_name', 'payment:id,amount,payment_link,payment_status', 'payment.invoice:id', 'invoice:id']);
    @endphp
    <div id="client-notification-popups" class="fixed inset-0 z-[100]" aria-live="polite" style="pointer-events: none">
        {{-- Invisible backdrop: allows clicks to pass through to page so scrolling works; cards sit above and have pointer-events-auto --}}
        <div class="fixed inset-0 z-0" style="pointer-events: none" aria-hidden="true"></div>
        @foreach($clientUnread as $index => $n)
        <div class="client-notification-card pointer-events-auto {{ $index > 0 ? 'hidden' : '' }}"
             data-notification-id="{{ $n->id }}"
             data-type="{{ $n->type }}"
             @if($n->type !== 'payment')
             style="position:fixed;bottom:1.5rem;right:1.5rem;max-width:24rem;width:100%;z-index:102;"
             @else
             style="position:fixed;inset:0;z-index:101;display:{{ $index > 0 ? 'none' : 'flex' }};align-items:center;justify-content:center;padding:1rem;background:rgba(0,0,0,0.7);"
             @endif>
            @if($n->type !== 'payment')
            <div class="theme-card-bg-only theme-border border rounded-xl shadow-xl p-4 flex gap-3">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold theme-text-primary text-sm">{{ $n->title }}</p>
                    <p class="theme-text-muted text-xs mt-0.5 line-clamp-2">{{ $n->message }}</p>
                </div>
                <button type="button" data-dismiss-notification-id="{{ $n->id }}" onclick="window.clientNotificationDismiss({{ $n->id }})" class="theme-text-muted theme-hover-primary p-1 rounded shrink-0" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            @else
            <div class="bg-slate-800 border border-slate-600 rounded-2xl shadow-2xl max-w-md w-full p-6" style="pointer-events:auto">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-lg font-semibold text-white">{{ $n->title }}</h2>
                    <button type="button" data-dismiss-notification-id="{{ $n->id }}" onclick="window.clientNotificationDismiss({{ $n->id }})" class="theme-text-muted theme-hover-primary p-1 rounded" aria-label="Close">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p class="text-slate-300 text-sm mb-4">{{ $n->message }}</p>
                @if($n->project)<p class="text-slate-400 text-sm mb-1"><span class="text-slate-500">Project:</span> {{ $n->project->project_name }}</p>@endif
                @if($n->payment)<p class="text-orange-400 font-semibold text-lg mb-4">৳{{ number_format($n->payment->amount, 0) }}</p>@endif
                @php $showUrl = $n->invoice ? route('invoices.view', $n->invoice) : route('client.payments.index'); @endphp
                <div class="flex flex-wrap gap-3 mt-4">
                    <a href="{{ $showUrl }}" data-dismiss-notification-id="{{ $n->id }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium theme-status-info-bg border border-orange-500/40 hover:opacity-90 transition">{{ $n->invoice ? 'View Invoice' : 'Show' }}</a>
                    @if($n->payment && $n->payment->payment_status === 'DUE' && $n->payment->payment_link)<a href="{{ $n->payment->payment_link }}" target="_blank" rel="noopener noreferrer" data-dismiss-notification-id="{{ $n->id }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold theme-btn-primary hover:opacity-90 transition">Pay Now</a>@endif
                    <button type="button" data-dismiss-notification-id="{{ $n->id }}" onclick="window.clientNotificationDismiss({{ $n->id }})" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium theme-btn-secondary">Close</button>
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    <script>
    (function() {
        var csrf = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var markReadUrlPattern = @json(route('client.notifications.mark-read', ['client_notification' => '__ID__']));
        var unreadUrl = @json(route('client.notifications.unread'));
        var shownIds = new Set();
        function getContainer() { return document.getElementById('client-notification-popups'); }
        function updateContainerPointerEvents() {
            var cont = getContainer();
            if (!cont) return;
            // When no cards visible, container stays non-interactive so page can scroll
            var hasVisible = cont.querySelector('.client-notification-card:not(.hidden)');
            cont.style.pointerEvents = hasVisible ? 'auto' : 'none';
        }
        var container = getContainer();
        if (container) {
            container.querySelectorAll('.client-notification-card').forEach(function(el) { shownIds.add(Number(el.getAttribute('data-notification-id'))); });
            updateContainerPointerEvents();
        }
        function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
        function buildCard(n, isHidden) {
            var type = n.type || 'normal';
            var id = n.id;
            var displayStyle = type === 'payment' ? 'flex' : 'block';
            if (isHidden) displayStyle = 'none';
            var wrapperStyle = type !== 'payment'
                ? 'position:fixed;bottom:1.5rem;right:1.5rem;max-width:24rem;width:100%;z-index:102;'
                : 'position:fixed;inset:0;z-index:101;display:' + displayStyle + ';align-items:center;justify-content:center;padding:1rem;background:rgba(0,0,0,0.7);';
            function escUrl(u) { if (!u) return ''; return String(u).replace(/&/g, '&amp;').replace(/"/g, '&quot;'); }
            var showUrl = n.show_url || n.invoice_view_url || '';
            var showLabel = n.invoice_view_url ? 'View Invoice' : 'Show';
            var payBtn = (n.payment_link && n.payment_status === 'DUE') ? '<a href="' + escUrl(n.payment_link) + '" target="_blank" rel="noopener noreferrer" data-dismiss-notification-id="' + id + '" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold theme-btn-primary hover:opacity-90 transition">Pay Now</a>' : '';
            var inner = type !== 'payment'
                ? '<div class="bg-slate-800 border border-slate-600 rounded-xl shadow-xl p-4 flex gap-3"><div class="flex-1 min-w-0"><p class="font-semibold text-white text-sm">' + esc(n.title) + '</p><p class="text-slate-400 text-xs mt-0.5 line-clamp-2">' + esc(n.message) + '</p></div><button type="button" data-dismiss-notification-id="' + id + '" class="text-slate-400 hover:text-white p-1 rounded shrink-0" aria-label="Close"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>'
                : '<div class="bg-slate-800 border border-slate-600 rounded-2xl shadow-2xl max-w-md w-full p-6"><div class="flex justify-between items-start mb-4"><h2 class="text-lg font-semibold text-white">' + esc(n.title) + '</h2><button type="button" data-dismiss-notification-id="' + id + '" class="text-slate-400 hover:text-white p-1 rounded" aria-label="Close"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div><p class="text-slate-300 text-sm mb-4">' + esc(n.message) + '</p>' + (n.project_name ? '<p class="text-slate-400 text-sm mb-1"><span class="text-slate-500">Project:</span> ' + esc(n.project_name) + '</p>' : '') + (n.amount != null ? '<p class="text-orange-400 font-semibold text-lg mb-4">৳' + esc(String(Math.round(Number(n.amount)))) + '</p>' : '') + '<div class="flex flex-wrap gap-3 mt-4"><a href="' + escUrl(showUrl) + '" data-dismiss-notification-id="' + id + '" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium theme-sidebar-active border border-orange-500/40 hover:bg-orange-500/30 transition">' + esc(showLabel) + '</a>' + payBtn + '<button type="button" data-dismiss-notification-id="' + id + '" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium bg-slate-700 text-slate-300 hover:theme-bg-tertiary transition">Close</button></div></div>';
            var div = document.createElement('div');
            div.className = 'client-notification-card pointer-events-auto' + (isHidden ? ' hidden' : '');
            div.setAttribute('data-notification-id', id);
            div.setAttribute('data-type', type);
            div.style.cssText = wrapperStyle;
            div.innerHTML = inner;
            return div;
        }
        window.clientNotificationDismiss = function(id) {
            if (id == null || id === '') return;
            var idStr = String(id);
            var cont = getContainer();
            if (!cont) return;
            var card = cont.querySelector('.client-notification-card[data-notification-id="' + idStr + '"]');
            if (!card) card = cont.querySelector('.client-notification-card:not(.hidden)');
            if (card) {
                card.classList.add('hidden');
                card.style.setProperty('display', 'none');
                card.style.setProperty('visibility', 'hidden');
                var cards = cont.querySelectorAll('.client-notification-card');
                for (var i = 0; i < cards.length; i++) {
                    if (cards[i] === card && cards[i + 1]) {
                        cards[i + 1].classList.remove('hidden');
                        cards[i + 1].style.removeProperty('visibility');
                        cards[i + 1].style.setProperty('display', cards[i + 1].getAttribute('data-type') === 'payment' ? 'flex' : 'block');
                        break;
                    }
                }
                updateContainerPointerEvents();
            }
            if (id && csrf && markReadUrlPattern) {
                var u = String(markReadUrlPattern).replace('__ID__', idStr);
                fetch(u, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }, credentials: 'same-origin' }).catch(function(){});
            }
            var badge = document.getElementById('client-notification-badge');
            if (badge) {
                var c = Math.max(0, parseInt(badge.getAttribute('data-count') || '0', 10) - 1);
                badge.setAttribute('data-count', c);
                badge.textContent = c > 99 ? '99+' : String(c);
                badge.classList.toggle('hidden', c <= 0);
            }
        };
        window.clientNotificationAddFromPush = function(n) {
            var cont = getContainer();
            if (!cont || !n || shownIds.has(n.id)) return;
            shownIds.add(n.id);
            var hasVisible = cont.querySelector('.client-notification-card:not(.hidden)');
            var isHidden = !!hasVisible;
            var card = buildCard(n, isHidden);
            cont.appendChild(card);
            updateContainerPointerEvents();
            var badge = document.getElementById('client-notification-badge');
            if (badge) {
                var c = parseInt(badge.getAttribute('data-count') || '0', 10) + 1;
                badge.setAttribute('data-count', c);
                badge.textContent = c > 99 ? '99+' : String(c);
                badge.classList.remove('hidden');
            }
            if (n.type !== 'payment' && !isHidden) {
                setTimeout(function() { window.clientNotificationDismiss(n.id); }, 3000);
            }
        };
        function pollUnread() {
            var cont = getContainer();
            if (!unreadUrl || !cont) return;
            fetch(unreadUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var list = data.notifications || [];
                    var unreadCount = typeof data.unread_count === 'number' ? data.unread_count : 0;
                    var badge = document.getElementById('client-notification-badge');
                    if (badge) {
                        badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
                        badge.setAttribute('data-count', unreadCount);
                        badge.classList.toggle('hidden', unreadCount <= 0);
                    }
                    var hasVisible = cont.querySelector('.client-notification-card:not(.hidden)');
                    list.forEach(function(n) {
                        if (shownIds.has(n.id)) return;
                        shownIds.add(n.id);
                        var isHidden = !!hasVisible;
                        if (!hasVisible) hasVisible = true;
                        var card = buildCard(n, isHidden);
                        cont.appendChild(card);
                        updateContainerPointerEvents();
                        if (n.type !== 'payment' && !isHidden) {
                            setTimeout(function() { window.clientNotificationDismiss(n.id); }, 3000);
                        }
                    });
                })
                .catch(function() {});
        }
        function setupContainerClick() {
            var cont = getContainer();
            if (!cont || cont._notificationClickBound) return;
            cont._notificationClickBound = true;
            cont.addEventListener('click', function(e) {
                var btn = e.target.closest ? e.target.closest('[data-dismiss-notification-id]') : null;
                if (!btn) return;
                var bid = btn.getAttribute('data-dismiss-notification-id');
                if (!bid) return;
                e.preventDefault();
                e.stopPropagation();
                window.clientNotificationDismiss(bid);
                if (btn.tagName === 'A' && btn.href) {
                    if (btn.target === '_blank') window.open(btn.href, '_blank');
                    else window.location.href = btn.href;
                }
            }, false);
        }
        setupContainerClick();
        setInterval(pollUnread, 3000);
        setTimeout(pollUnread, 800);
        var firstSmall = document.querySelector('.client-notification-card:not([data-type="payment"]):not(.hidden)');
        if (firstSmall) setTimeout(function() { window.clientNotificationDismiss(firstSmall.getAttribute('data-notification-id')); }, 3000);
    })();
    </script>
    @endif
    @endauth

    @if(Auth::check() && Auth::user()->isClient() && config('fcm.public.api_key'))
    @php
        $fcmPublic = config('fcm.public');
        $firebaseConfig = [
            'apiKey' => $fcmPublic['api_key'] ?? null,
            'authDomain' => $fcmPublic['auth_domain'] ?? null,
            'projectId' => $fcmPublic['project_id'] ?? null,
            'storageBucket' => $fcmPublic['storage_bucket'] ?? null,
            'messagingSenderId' => $fcmPublic['messaging_sender_id'] ?? null,
            'appId' => $fcmPublic['app_id'] ?? null,
        ];
    @endphp
    <script>
        window.clientFcmConfig = {
            firebase: @json(array_filter($firebaseConfig)),
            registerUrl: @json(route('client.devices.register')),
            vapidKey: @json(config('fcm.vapid_key'))
        };
    </script>
    @vite(['resources/js/client-fcm.js'])
    @endif
</body>
</html>
