<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'AIPM — AI Powered Marketing') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="alternate icon" href="{{ asset('favicon.svg') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root { --brand-purple:#6d28d9; --brand-purple-dark:#4c1d95; --brand-orange:#f97316; --brand-ink:#171323; }
            body { font-family:Figtree,ui-sans-serif,system-ui,sans-serif; }
            .brand-grid { background-image: linear-gradient(rgba(109,40,217,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(109,40,217,.045) 1px,transparent 1px); background-size:32px 32px; }
            .brand-glow { background: radial-gradient(circle at 15% 20%,rgba(109,40,217,.18),transparent 32%),radial-gradient(circle at 85% 75%,rgba(249,115,22,.12),transparent 30%); }
            .brand-input { transition:all .2s ease; }
            .brand-input:focus { border-color:var(--brand-purple)!important; box-shadow:0 0 0 4px rgba(109,40,217,.10)!important; }
            .brand-btn { background:linear-gradient(135deg,var(--brand-purple),#7c3aed); transition:transform .2s ease,box-shadow .2s ease; }
            .brand-btn:hover { transform:translateY(-1px); box-shadow:0 12px 24px rgba(109,40,217,.22); }
        </style>
    </head>
    <body class="min-h-screen bg-[#faf9fc] text-[#171323] antialiased">
        <main class="min-h-screen grid lg:grid-cols-[1.05fr_.95fr] brand-grid brand-glow">
            <section class="relative hidden lg:flex overflow-hidden bg-[#171323] px-14 py-12 text-white">
                <div class="absolute inset-0 opacity-30" style="background:radial-gradient(circle at 20% 20%,#7c3aed,transparent 35%),radial-gradient(circle at 85% 80%,#f97316,transparent 28%);"></div>
                <div class="relative z-10 flex w-full flex-col">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3 w-fit">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-white text-sm font-black tracking-tight text-violet-700 shadow-xl">AIPM</span>
                        <span class="text-xl font-extrabold tracking-tight">AI Powered <span class="text-violet-300">Marketing</span></span>
                    </a>
                    <div class="my-auto max-w-xl pb-10">
                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold backdrop-blur"><span class="h-2 w-2 rounded-full bg-orange-400"></span> Business Affiliate Platform</div>
                        <h1 class="text-5xl font-extrabold leading-[1.05] tracking-tight xl:text-6xl">Turn your customers into your sales force.</h1>
                        <p class="mt-6 max-w-lg text-lg leading-8 text-white/65">AI Powered Marketing lets businesses create affiliate programs, recruit partners and reward people for bringing real customers.</p>
                        <div class="mt-10 grid grid-cols-3 gap-3 max-w-lg"><div class="rounded-2xl border border-white/10 bg-white/[.06] p-4"><div class="text-2xl font-extrabold">01</div><div class="mt-1 text-xs text-white/50">Create</div></div><div class="rounded-2xl border border-white/10 bg-white/[.06] p-4"><div class="text-2xl font-extrabold">02</div><div class="mt-1 text-xs text-white/50">Recruit</div></div><div class="rounded-2xl border border-white/10 bg-white/[.06] p-4"><div class="text-2xl font-extrabold">03</div><div class="mt-1 text-xs text-white/50">Grow</div></div></div>
                    </div>
                    <p class="text-sm text-white/35">© {{ date('Y') }} AIPM — AI Powered Marketing. Built for ambitious businesses.</p>
                </div>
            </section>
            <section class="flex items-center justify-center px-5 py-10 sm:px-8">
                <div class="w-full max-w-[460px]">
                    <div class="mb-8 flex items-center justify-between lg:hidden"><a href="{{ url('/') }}" class="flex items-center gap-2"><span class="grid h-10 w-10 place-items-center rounded-xl bg-violet-700 text-xs font-black text-white">AIPM</span><span class="font-extrabold tracking-tight">AI <span class="text-violet-700">Powered Marketing</span></span></a><span class="text-xs font-semibold text-gray-400">AIPM</span></div>
                    <div class="rounded-[28px] border border-gray-100 bg-white p-7 shadow-[0_24px_70px_rgba(23,19,35,.08)] sm:p-10">{{ $slot }}</div>
                    <p class="mt-6 text-center text-xs text-gray-400">Secure access to your AIPM partnership account</p>
                </div>
            </section>
        </main>
    </body>
</html>
