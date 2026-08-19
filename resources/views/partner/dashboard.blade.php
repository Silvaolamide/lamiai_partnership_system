<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Dashboard · AIPM</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-violet-600">AIPM Partner Hub</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">Welcome back, {{ auth()->user()->name }}.</h1>
            <p class="mt-2 text-slate-600">Manage your programs, referrals, products, earnings and partner network from one place.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('partner.storefront', ['partnerCode' => optional($programStats->first()['partner'] ?? null)->partner_code]) }}" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white">Open My Store</a>
            <a href="{{ route('network.index') }}" class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white">My Network</a>
            <a href="{{ route('partner.payouts.index') }}" class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white">Payouts</a>
        </div>
    </header>

    <section class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">Gross Sales</p><p class="mt-2 text-3xl font-black">₦{{ number_format($totalSalesAmount,2) }}</p><p class="mt-1 text-xs text-slate-500">{{ $totalSales }} completed orders</p></div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm"><p class="text-sm font-semibold text-emerald-700">Available Commission</p><p class="mt-2 text-3xl font-black text-emerald-700">₦{{ number_format($totalPending,2) }}</p><p class="mt-1 text-xs text-emerald-700">Eligible for payout</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">Paid Commission</p><p class="mt-2 text-3xl font-black">₦{{ number_format($totalPaid,2) }}</p><p class="mt-1 text-xs text-slate-500">Lifetime paid earnings</p></div>
        <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5 shadow-sm"><p class="text-sm font-semibold text-violet-700">Partners Recruited</p><p class="mt-2 text-3xl font-black text-violet-950">{{ $totalRecruited }}</p><p class="mt-1 text-xs text-violet-700">Across your programs</p></div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm"><p class="text-sm font-semibold text-blue-700">Business Net Revenue</p><p class="mt-2 text-3xl font-black text-blue-950">₦{{ number_format($totalNetBusinessRevenue,2) }}</p><p class="mt-1 text-xs text-blue-700">Sales less commissions</p></div>
    </section>

    <section class="mb-8 grid gap-5 lg:grid-cols-[1.35fr_.65fr]">
        <div class="rounded-3xl bg-slate-950 p-7 text-white shadow-xl">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-300">Your public storefront</p>
            <h2 class="mt-2 text-3xl font-black tracking-tight">Your store does the selling. You bring the audience.</h2>
            <p class="mt-3 max-w-2xl leading-7 text-slate-300">Share your personal storefront with prospects. They can discover your products, open each product sales page and join your network without needing access to this dashboard.</p>
            @if($programStats->isNotEmpty())
                @php $firstPartner = $programStats->first()['partner']; @endphp
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('partner.storefront', ['partnerCode' => $firstPartner->partner_code]) }}" class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-black text-slate-950 hover:bg-emerald-400">View my storefront →</a>
                    <button type="button" onclick="navigator.clipboard?.writeText('{{ route('partner.storefront', ['partnerCode' => $firstPartner->partner_code]) }}'); this.textContent='Copied!'" class="rounded-xl border border-white/15 bg-white/5 px-5 py-3 text-sm font-bold hover:bg-white/10">Copy storefront link</button>
                </div>
            @endif
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-600">Quick actions</p>
            <div class="mt-5 space-y-3">
                <a href="#programs" class="block rounded-2xl bg-slate-50 p-4 font-bold hover:bg-violet-50">View my programs <span class="float-right">→</span></a>
                <a href="{{ route('partner.marketplace.index') }}" class="block rounded-2xl bg-slate-50 p-4 font-bold hover:bg-violet-50">Find more programs <span class="float-right">→</span></a>
                <a href="{{ route('profile.edit') }}" class="block rounded-2xl bg-slate-50 p-4 font-bold hover:bg-violet-50">Update profile <span class="float-right">→</span></a>
            </div>
        </div>
    </section>

    <section id="programs" class="space-y-6">
        @forelse($programStats as $programStat)
            @php
                $program = $programStat['program'];
                $partner = $programStat['partner'];
                $rules = $program->commissionRules->where('status', true)->where('event', 'sale');
                $level1Rule = $rules->where('level', 1)->sortByDesc('priority')->first();
                $level2Rule = $rules->where('level', 2)->sortByDesc('priority')->first();
                $storefrontUrl = route('partner.storefront', ['partnerCode' => $partner->partner_code]);
            @endphp
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-6 sm:p-7">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div><p class="text-xs font-black uppercase tracking-[0.18em] text-violet-600">Partnership Program</p><h2 class="mt-1 text-2xl font-black">{{ $program->name }}</h2><p class="mt-2 text-sm text-slate-500">Partner code: <span class="font-mono font-bold">{{ $partner->partner_code }}</span></p></div>
                        <a href="{{ $storefrontUrl }}" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-black text-white">Open public storefront →</a>
                    </div>
                </div>
                <div class="grid gap-6 p-6 sm:p-7 lg:grid-cols-[.8fr_1.2fr]">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Sales</p><p class="mt-1 text-2xl font-black">₦{{ number_format($programStat['sales_amount'],2) }}</p></div>
                        <div class="rounded-2xl bg-emerald-50 p-4"><p class="text-xs text-emerald-700">Commission</p><p class="mt-1 text-2xl font-black text-emerald-700">₦{{ number_format($programStat['commission_total'],2) }}</p></div>
                        <div class="rounded-2xl bg-violet-50 p-4"><p class="text-xs text-violet-700">Recruited</p><p class="mt-1 text-2xl font-black text-violet-950">{{ $programStat['recruited_count'] }}</p></div>
                        <div class="rounded-2xl bg-blue-50 p-4"><p class="text-xs text-blue-700">Your rate</p><p class="mt-1 text-2xl font-black text-blue-950">{{ $level1Rule ? ($level1Rule->commission_type === 'percentage' ? number_format((float)$level1Rule->value,2).'%' : '₦'.number_format((float)$level1Rule->value,2)) : '—' }}</p></div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between"><h3 class="text-lg font-black">Products you can promote</h3><a href="{{ $storefrontUrl }}#products" class="text-sm font-bold text-violet-600">See all on storefront →</a></div>
                        @if($program->products->isNotEmpty())
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach($program->products->take(6) as $product)
                                    <a href="{{ route('product.show', ['slug' => $product->slug, 'ref' => $partner->partner_code]) }}" class="group rounded-2xl border border-slate-200 p-4 hover:border-violet-300 hover:shadow-sm"><div class="flex items-center justify-between gap-3"><div><p class="font-bold group-hover:text-violet-700">{{ $product->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $product->currency ?? 'NGN' }} {{ number_format((float)$product->price,2) }}</p></div><span class="text-violet-600">→</span></div></a>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-4 rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">No active products are currently attached to this program.</p>
                        @endif
                    </div>
                </div>
            </section>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center"><h2 class="text-2xl font-black">You haven't joined a program yet.</h2><p class="mt-2 text-slate-600">Browse available programs and choose the ones you want to promote.</p><a href="{{ route('partner.marketplace.index') }}" class="mt-6 inline-flex rounded-xl bg-violet-600 px-5 py-3 font-bold text-white">Browse programs →</a></div>
        @endforelse
    </section>
</div>
</body>
</html>
