<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AIPM — AI Powered Marketing') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-900">
    <div x-data="{ adminSidebarCollapsed: false, mobileNavOpen: false }" class="min-h-screen bg-[#f6f7fb]">
        @include('layouts.navigation')
        <main class="min-h-screen pt-[78px] transition-all duration-300 lg:ml-[264px]" :class="adminSidebarCollapsed ? 'lg:ml-[82px]' : 'lg:ml-[264px]'">
            @yield('content')
        </main>
    </div>
</body>
</html>
