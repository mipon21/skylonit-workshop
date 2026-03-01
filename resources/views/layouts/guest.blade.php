<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Skylon-IT WorkShop') }} — Login</title>
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
    </head>
    <body class="font-sans theme-body antialiased min-h-screen">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 sm:px-6">
            @if(!empty($appLogoUrl))
                <a href="{{ route('login') }}" class="block">
                    <img src="{{ $appLogoUrl }}" alt="{{ config('app.name') }}" class="h-12 w-auto max-w-[240px] object-contain">
                </a>
            @else
                <a href="{{ route('login') }}" class="text-2xl font-bold bg-gradient-to-r from-orange-400 to-amber-500 bg-clip-text text-transparent">{{ config('app.name') }}</a>
            @endif
            <div class="w-full max-w-md mt-6 px-6 py-8 theme-card-bg-only theme-border border rounded-2xl shadow-xl mx-auto flex flex-col items-stretch">
                <div class="flex justify-end mb-2">
                    <button type="button" onclick="window.theme && window.theme.toggleTheme({})" class="p-2 rounded-lg theme-sidebar-link-hover theme-sidebar-text theme-sidebar-text-hover transition" aria-label="Toggle theme">
                        <svg class="w-5 h-5 theme-sun-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg class="w-5 h-5 theme-moon-icon" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </button>
                </div>
                {{ $slot }}
            </div>
        </div>
        @include('layouts.partials.global-footer')
    </body>
</html>
