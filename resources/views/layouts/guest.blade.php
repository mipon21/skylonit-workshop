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
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-200 antialiased bg-slate-950">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            @if(!empty($appLogoUrl))
                <a href="{{ route('login') }}" class="block">
                    <img src="{{ $appLogoUrl }}" alt="{{ config('app.name') }}" class="h-10 w-auto max-w-[200px] object-contain">
                </a>
            @else
                <a href="{{ route('login') }}" class="text-xl font-bold bg-gradient-to-r from-cyan-400 to-sky-500 bg-clip-text text-transparent">{{ config('app.name') }}</a>
            @endif
            <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
