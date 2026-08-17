<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAMI AI — Create your affiliate program</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background:#f8f8fb; }
        .gradient { background:linear-gradient(135deg,#6d28d9,#a21caf); }
        .glow { box-shadow:0 24px 80px rgba(109,40,217,.14); }
    </style>
</head>
<body class="min-h-screen font-sans text-gray-900 antialiased">
<div class="min-h-screen lg:grid lg:grid-cols-[360px_1fr]">
    <aside class="hidden bg-gray-950 p-8 text-white lg:flex lg:flex-col">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-lg font-black text-gray-950">L</span>
            <span class="text-xl font-black">LAMI <span class="text-violet-400">AI</span></span>
        </a>
        <div class="my-auto">
            <p class="text-sm font-bold uppercase tracking-[.2em] text-violet-300">Business setup</p>
            <h1 class="mt-5 text-4xl font-black leading-tight">Build your sales force in minutes.</h1>
            <p class="mt-5 leading-7 text-gray-400">Set up your business, add your first product, decide what affiliates earn, then publish your program.</p>
            <div class="mt-10 space-y-5">
                @foreach($steps as $key => $label)
                    @php $done = array_search($key, array_keys($steps)) < array_search($step, array_keys($steps)); @endphp
                    <div class="flex items-center gap-4">
                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ $key === $step ? 'gradient text-white' : ($done ? 'bg-emerald-400 text-gray-950' : 'bg-white/10 text-gray-400') }} font-black text-sm">
                            {{ $done ? '✓' : array_search($key, array_keys($steps)) + 1 }}
                        </div>
                        <span class="{{ $key === $step ? 'font-bold text-white' : 'text-gray-500' }}">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <p class="text-xs text-gray-600">LAMI AI Business Marketing</p>
    </aside>

    <main class="flex min-h-screen flex-col">
        <header class="flex items-center justify-between border-b border-gray-200 bg-white px-5 py-5 lg:px-10">
            <div class="lg:hidden text-lg font-black">LAMI <span class="text-violet-600">AI</span></div>
            <div class="hidden lg:block"></div>
            <div class="text-sm font-semibold text-gray-500">Step {{ array_search($step, array_keys($steps)) + 1 }} of {{ count($steps) }}</div>
        </header>

        <div class="mx-auto w-full max-w-3xl flex-1 px-5 py-10 lg:px-10 lg:py-16">
            <div class="mb-8 h-1.5 overflow-hidden rounded-full bg-gray-200">
                <div class="gradient h-full rounded-full transition-all" style="width:{{ ((array_search($step, array_keys($steps)) + 1) / count($steps)) * 100 }}%"></div>
            </div>

            @if($step === 'profile')
                <div class="mb-9"><span class="text-sm font-black uppercase tracking-[.18em] text-violet-600">Business profile</span><h2 class="mt-3 text-4xl font-black tracking-tight">Tell us about your business.</h2><p class="mt-3 text-gray-500">This information appears across your affiliate program and helps partners understand who they are promoting.</p></div>
                <form method="POST" action="{{ route('business.onboarding.store', ['step' => 'profile']) }}" class="space-y-6">
                    @csrf
                    <div><label class="text-sm font-bold">Business name</label><input name="business_name" value="{{ old('business_name', $data['profile']['business_name'] ?? '') }}" required class="mt-2 w-full rounded-2xl border-gray-200 px-5 py-4 shadow-sm" placeholder="e.g. LAMI AI Academy"></div>
                    <div class="grid gap-6 sm:grid-cols-2"><div><label class="text-sm font-bold">Website</label><input name="business_website" value="{{ old('business_website', $data['profile']['business_website'] ?? '') }}" class="mt-2 w-full rounded-2xl border-gray-200 px-5 py-4 shadow-sm" placeholder="https://example.com"></div><div><label class="text-sm font-bold">Phone</label><input name="business_phone" value="{{ old('business_phone', $data['profile']['business_phone'] ?? '') }}" class="mt-2 w-full rounded-2xl border-gray-200 px-5 py-4 shadow-sm" placeholder="+234..."></div></div>
                    <div><label class="text-sm font-bold">Industry</label><select name="business_industry" required class="mt-2 w-full rounded-2xl border-gray-200 px-5 py-4 shadow-sm"><option value="">Choose an industry</option>@foreach(['Technology','Education','Fashion','Health & Wellness','Finance','E-commerce','Professional Services','Media & Entertainment','Other'] as $industry)<option value="{{ $industry }}" @selected(old('business_industry', $data['profile']['business_industry'] ?? '') === $industry)>{{ $industry }}</option>@endforeach</select></div>
                    <button class="gradient w-full rounded-2xl px-6 py-4 font-black text-white shadow-lg">Continue to product →</button>
                </form>
            @elseif($step === 'product')
                <div class="mb-9"><span class="text-sm font-black uppercase tracking-[.18em] text-violet-600">First product</span><h2 class="mt-3 text-4xl font-black tracking-tight">What will affiliates sell?</h2><p class="mt-3 text-gray-500">Start with one product. You can add more from your dashboard later.</p></div>
                <form method="POST" action="{{ route('business.onboarding.store', ['step' => 'product']) }}" class="space-y-6">
                    @csrf
                    <div><label class="text-sm font-bold">Product name</label><input name="name" value="{{ old('name', $data['product']['name'] ?? '') }}" required class="mt-2 w-full rounded-2xl border-gray-200 px-5 py-4 shadow-sm" placeholder="e.g. AI Content Masterclass"></div>
                    <div><label class="text-sm font-bold">Description</label><textarea name="description" rows="4" class="mt-2 w-full rounded-2xl border-gray-200 px-5 py-4 shadow-sm" placeholder="What does the customer get?">{{ old('description', $data['product']['description'] ?? '') }}</textarea></div>
                    <div class="grid gap-6 sm:grid-cols-[1fr_140px]"><div><label class="text-sm font-bold">Price</label><input type="number" step="0.01" min="0" name="price" value="{{ old('price', $data['product']['price'] ?? '') }}" required class="mt-2 w-full rounded-2xl border-gray-200 px-5 py-4 shadow-sm" placeholder="20000"></div><div><label class="text-sm font-bold">Currency</label><select name="currency" class="mt-2 w-full rounded-2xl border-gray-200 px-5 py-4 shadow-sm"><option value="NGN">NGN</option><option value="USD">USD</option><option value="GBP">GBP</option></select></div></div>
                    <button class="gradient w-full rounded-2xl px-6 py-4 font-black text-white shadow-lg">Continue to commission →</button>
                </form>
            @elseif($step === 'commission')
                <div class="mb-9"><span class="text-sm font-black uppercase tracking-[.18em] text-violet-600">Commission engine</span><h2 class="mt-3 text-4xl font-black tracking-tight">Decide how your partners earn.</h2><p class="mt-3 text-gray-500">You can reward the affiliate who makes the sale and optionally reward their upstream partners.</p></div>
                <form method="POST" action="{{ route('business.onboarding.store', ['step' => 'commission']) }}" class="space-y-6">
                    @csrf
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 glow"><div class="flex items-center justify-between"><div><h3 class="font-black">Level 1</h3><p class="text-sm text-gray-500">Direct affiliate who generated the sale</p></div><div class="flex items-center gap-2"><input type="number" step="0.01" min="0" max="100" name="level_1" value="{{ old('level_1', $data['commission']['level_1'] ?? 20) }}" required class="w-24 rounded-xl border-gray-200 px-3 py-3 text-right font-black"><span class="font-black">%</span></div></div></div>
                    <div class="rounded-3xl border border-gray-200 bg-white p-6"><div class="flex items-center justify-between"><div><h3 class="font-black">Level 2 <span class="ml-2 rounded-full bg-gray-100 px-2 py-1 text-xs">Optional</span></h3><p class="text-sm text-gray-500">Recruiter / upstream partner</p></div><div class="flex items-center gap-2"><input type="number" step="0.01" min="0" max="100" name="level_2" value="{{ old('level_2', $data['commission']['level_2'] ?? 5) }}" class="w-24 rounded-xl border-gray-200 px-3 py-3 text-right font-black"><span class="font-black">%</span></div></div></div>
                    <div class="rounded-3xl border border-gray-200 bg-white p-6"><div class="flex items-center justify-between"><div><h3 class="font-black">Level 3 <span class="ml-2 rounded-full bg-gray-100 px-2 py-1 text-xs">Optional</span></h3><p class="text-sm text-gray-500">Third-level partner</p></div><div class="flex items-center gap-2"><input type="number" step="0.01" min="0" max="100" name="level_3" value="{{ old('level_3', $data['commission']['level_3'] ?? 0) }}" class="w-24 rounded-xl border-gray-200 px-3 py-3 text-right font-black"><span class="font-black">%</span></div></div></div>
                    <div class="grid gap-6 sm:grid-cols-2"><div><label class="text-sm font-bold">Referral attribution window</label><div class="mt-2 flex items-center gap-2"><input type="number" min="1" max="365" name="attribution_window_days" value="{{ old('attribution_window_days', $data['commission']['attribution_window_days'] ?? 30) }}" required class="w-full rounded-2xl border-gray-200 px-5 py-4"><span class="text-sm font-bold text-gray-500">days</span></div></div><div><label class="text-sm font-bold">Minimum payout</label><input type="number" min="0" step="0.01" name="minimum_payout" value="{{ old('minimum_payout', $data['commission']['minimum_payout'] ?? 10000) }}" required class="mt-2 w-full rounded-2xl border-gray-200 px-5 py-4"></div></div>
                    <button class="gradient w-full rounded-2xl px-6 py-4 font-black text-white shadow-lg">Review my program →</button>
                </form>
            @else
                <div class="mb-9"><span class="text-sm font-black uppercase tracking-[.18em] text-violet-600">Ready to launch</span><h2 class="mt-3 text-4xl font-black tracking-tight">Publish your affiliate program.</h2><p class="mt-3 text-gray-500">Everything looks good. Publish it and start recruiting partners.</p></div>
                <div class="space-y-4"><div class="rounded-3xl border border-gray-200 bg-white p-6"><p class="text-xs font-bold uppercase tracking-widest text-gray-400">Business</p><p class="mt-2 text-xl font-black">{{ $data['profile']['business_name'] }}</p><p class="mt-1 text-sm text-gray-500">{{ $data['profile']['business_industry'] }}</p></div><div class="rounded-3xl border border-gray-200 bg-white p-6"><p class="text-xs font-bold uppercase tracking-widest text-gray-400">Product</p><p class="mt-2 text-xl font-black">{{ $data['product']['name'] }}</p><p class="mt-1 text-sm text-gray-500">{{ $data['product']['currency'] }} {{ number_format((float)$data['product']['price'], 2) }}</p></div><div class="rounded-3xl border border-gray-200 bg-white p-6"><p class="text-xs font-bold uppercase tracking-widest text-gray-400">Commission</p><p class="mt-2 text-xl font-black">{{ $data['commission']['level_1'] }}% direct</p><p class="mt-1 text-sm text-gray-500">{{ $data['commission']['level_2'] ?? 0 }}% level 2 · {{ $data['commission']['level_3'] ?? 0 }}% level 3</p></div></div>
                <form method="POST" action="{{ route('business.onboarding.store', ['step' => 'publish']) }}" class="mt-8">@csrf<label class="flex items-start gap-3 rounded-2xl bg-violet-50 p-4 text-sm text-gray-700"><input type="checkbox" name="publish" value="1" required class="mt-1 h-4 w-4 rounded"><span>I confirm these details are correct and I want to publish this affiliate program.</span></label><button class="gradient mt-5 w-full rounded-2xl px-6 py-4 font-black text-white shadow-lg">Publish Affiliate Program 🚀</button></form>
            @endif

            @if($errors->any())<div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        </div>
    </main>
</div>
</body>
</html>
