<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forever iVision_Amej | Complete Eye Support</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .hero { background: radial-gradient(circle at 15% 10%, rgba(20,184,166,.18), transparent 32%), radial-gradient(circle at 90% 20%, rgba(14,165,233,.16), transparent 30%), linear-gradient(135deg,#062b2a 0%,#0f3f3d 55%,#082f49 100%); }
        .cta { background: linear-gradient(135deg,#0f766e 0%,#0891b2 100%); }
        .lift { transition: transform .2s ease, box-shadow .2s ease; }
        .lift:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(15,23,42,.12); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">

@if($referralError)
    <div class="bg-red-600 px-4 py-3 text-center text-sm font-semibold text-white">Referral notice: {{ $referralError }}</div>
@elseif($referralProcessed && $referringPartner)
    <div class="bg-emerald-600 px-4 py-3 text-center text-sm font-semibold text-white">✓ You were referred by {{ $referringPartner->user->name }}. Their referral is active for this purchase.</div>
@endif

<header class="hero overflow-hidden text-white">
    <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-20 lg:px-10">
        <div class="grid items-center gap-12 lg:grid-cols-[1fr_.8fr]">
            <div>
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-bold backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    Eye support for the digital age
                </div>
                <p class="mb-3 text-sm font-black uppercase tracking-[.2em] text-teal-200">Forever iVision_Amej</p>
                <h1 class="max-w-3xl text-4xl font-black leading-tight tracking-tight sm:text-6xl">Complete eye support for the <span class="text-teal-200">digital age.</span></h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200 sm:text-xl">A targeted formula designed to support healthy vision and inner-eye health in a world surrounded by digital screens.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="#buy" class="cta inline-flex items-center justify-center rounded-2xl px-7 py-4 text-base font-black shadow-xl transition hover:scale-[1.02]">GET FOREVER iVISION_Amej <span class="ml-2">→</span></a>
                    <a href="#benefits" class="inline-flex items-center justify-center rounded-2xl border border-white/20 bg-white/5 px-7 py-4 font-bold backdrop-blur hover:bg-white/10">See the benefits</a>
                </div>
            </div>

            <div id="buy" class="rounded-3xl border border-white/10 bg-white p-7 text-slate-900 shadow-2xl sm:p-9">
                @if($product->featured_image)
                    <div class="mb-6 overflow-hidden rounded-2xl bg-slate-100">
                        <img src="{{ asset('storage/' . $product->featured_image) }}" alt="Forever iVision_Amej" class="h-64 w-full object-contain">
                    </div>
                @endif
                <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-black uppercase tracking-wider text-teal-800">Forever iVision_Amej</span>
                <h2 class="mt-4 text-2xl font-black sm:text-3xl">Complete eye support for the digital age</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Supports healthy vision, helps filter blue light from digital devices, supports visual processing speed and enhances glare recovery time.</p>
                <div class="my-7 border-y border-slate-200 py-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">One-time investment</p>
                    <div class="mt-1 flex items-end gap-2"><span class="text-5xl font-black tracking-tight">₦80,000</span><span class="pb-2 text-sm text-slate-500">NGN</span></div>
                </div>
                <form action="{{ route('checkout.create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="cta w-full rounded-2xl px-6 py-5 text-lg font-black text-white shadow-lg transition hover:scale-[1.01]">GET ACCESS NOW →</button>
                </form>
                <p class="mt-4 text-center text-xs font-semibold text-slate-500">🔒 Secure checkout • Your referral attribution is preserved</p>
                @if($referralProcessed && $referringPartner)
                    <div class="mt-5 rounded-2xl bg-emerald-50 p-4 text-center text-sm text-emerald-800"><strong>{{ $referringPartner->user->name }}</strong> referred you to this product.</div>
                @endif
            </div>
        </div>
    </div>
</header>

<section id="benefits" class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:px-10 lg:py-24">
    <div class="max-w-3xl">
        <p class="text-sm font-black uppercase tracking-[.2em] text-teal-700">Why iVision_Amej</p>
        <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-5xl">Focused support for modern visual demands.</h2>
        <p class="mt-5 text-lg leading-8 text-slate-600">Forever iVision_Amej provides all three needed carotenoids specific to inner eye health, alongside support designed for today's digital lifestyle.</p>
    </div>
    <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['Healthy vision','Supports healthy vision as part of your everyday wellness routine.','👁️'],
            ['Blue-light support','Helps filter blue light from digital devices.','📱'],
            ['Visual processing','Supports visual processing speed.','⚡'],
            ['Glare recovery','Enhances glare recovery time.','☀️'],
            ['Three key carotenoids','Provides all three needed carotenoids specific to inner eye health.','🔬'],
            ['Gluten free','A gluten-free formula for your eye-support routine.','✓'],
        ] as $benefit)
            <div class="lift rounded-3xl border border-slate-200 bg-white p-7">
                <div class="text-3xl">{{ $benefit[2] }}</div>
                <h3 class="mt-5 text-lg font-black">{{ $benefit[0] }}</h3>
                <p class="mt-2 leading-7 text-slate-600">{{ $benefit[1] }}</p>
            </div>
        @endforeach
    </div>
</section>

<section class="bg-slate-950 px-5 py-16 text-white sm:px-8 lg:py-24">
    <div class="mx-auto max-w-5xl text-center">
        <p class="text-sm font-black uppercase tracking-[.2em] text-teal-300">Made for the digital age</p>
        <h2 class="mt-3 text-3xl font-black sm:text-5xl">Support your eyes while you navigate a screen-filled world.</h2>
        <p class="mx-auto mt-5 max-w-3xl text-lg leading-8 text-slate-400">From phones and tablets to computers and other digital devices, modern life puts constant demands on our visual system. iVision_Amej is formulated to provide targeted nutritional support.</p>
        <a href="#buy" class="cta mt-8 inline-flex rounded-2xl px-7 py-4 font-black text-white">GET iVISION_Amej →</a>
    </div>
</section>

<section class="px-5 py-16 sm:px-8 lg:px-10 lg:py-24">
    <div class="mx-auto max-w-4xl rounded-[2rem] bg-gradient-to-br from-teal-50 via-white to-cyan-50 p-8 sm:p-12">
        <div class="text-center">
            <p class="text-sm font-black uppercase tracking-[.2em] text-teal-700">At a glance</p>
            <h2 class="mt-3 text-3xl font-black sm:text-4xl">Forever iVision_Amej</h2>
            <div class="mx-auto mt-8 max-w-2xl text-left">
                <ul class="space-y-4 text-lg font-semibold text-slate-700">
                    <li class="flex gap-3"><span class="text-emerald-600">✓</span> Supports healthy vision</li>
                    <li class="flex gap-3"><span class="text-emerald-600">✓</span> Helps filter blue light from digital devices</li>
                    <li class="flex gap-3"><span class="text-emerald-600">✓</span> Supports visual processing speed</li>
                    <li class="flex gap-3"><span class="text-emerald-600">✓</span> Enhances glare recovery time</li>
                    <li class="flex gap-3"><span class="text-emerald-600">✓</span> Provides all three needed carotenoids specific to inner eye health</li>
                    <li class="flex gap-3"><span class="text-emerald-600">✓</span> Gluten Free</li>
                </ul>
            </div>
            <div class="mt-10">
                <p class="text-sm font-bold uppercase tracking-wider text-slate-500">One-time investment</p>
                <p class="mt-2 text-4xl font-black">₦80,000 <span class="text-base font-bold text-slate-500">NGN</span></p>
                <a href="#buy" class="cta mt-6 inline-flex rounded-2xl px-8 py-4 font-black text-white shadow-lg">GET STARTED →</a>
            </div>
        </div>
    </div>
</section>

<footer class="border-t border-slate-200 bg-white px-5 py-8 text-center text-sm text-slate-500">
    Forever iVision_Amej • Complete eye support for the digital age
</footer>

</body>
</html>
