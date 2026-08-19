<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>AI Powered Marketing (AIPM)</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="alternate icon" href="{{ asset('favicon.svg') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900">
        @php($isAdmin = Auth::check() && Auth::user()->hasRole('super_admin'))
        <div x-data="{ adminSidebarCollapsed: false, mobileNavOpen: false }" class="min-h-screen {{ $isAdmin ? 'bg-[#f6f7fb]' : 'bg-gray-100' }}">
            @include('layouts.navigation')

            @if($isAdmin)
                <main class="min-h-screen pt-[78px] transition-all duration-300 lg:ml-[264px]" :class="adminSidebarCollapsed ? 'lg:ml-[82px]' : 'lg:ml-[264px]'">
                    {{ $slot }}
                </main>
            @else
                @isset($header)
                    <header class="bg-white shadow">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{{ $header }}</div>
                    </header>
                @endisset
                <main>{{ $slot }}</main>
            @endif
        </div>
    </body>
</html>
