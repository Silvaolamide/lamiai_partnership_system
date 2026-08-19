<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#070b1d">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.svg') }}">
    <title>Partner Dashboard · AIPM</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    @include('components.dashboard-payment-alert', ['count' => $totalPendingPaymentConfirmations ?? 0, 'role' => 'partner'])
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
                    <a href="{{ route('partner.storefront', ['partnerCode' => $firstPartner->partner_code]) }}" class="rounded-xl bg-emerald-400 px-5 py-3 text-center text-sm font-black text-slate-950">View My Store</a>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ route('partner.storefront', ['partnerCode' => $firstPartner->partner_code]) }}'); this.innerText='Copied!';" class="rounded-xl border border-white/20 px-5 py-3 text-center text-sm font-bold text-white">Copy Store Link</button>
                </div>
            @endif
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-600">Quick actions</p>
            <div class="mt-5 grid gap-3">
                <a href="{{ route('partner.storefront', ['partnerCode' => optional($programStats->first()['partner'] ?? null)->partner_code]) }}" class="rounded-xl border border-slate-200 px-4 py-3 font-bold hover:bg-slate-50">Open Storefront</a>
                <a href="{{ route('network.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 font-bold hover:bg-slate-50">Manage Network</a>
                <a href="{{ route('partner.payouts.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 font-bold hover:bg-slate-50">View Payouts</a>
            </div>
        </div>
    </section>

    <section class="mb-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="text-xs font-black uppercase tracking-[0.18em] text-violet-600">Programs</p><h2 class="mt-1 text-2xl font-black">Your performance</h2></div>
            <p class="text-sm text-slate-500">Completed sales and commissions</p>
        </div>
        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead><tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500"><th class="px-3 py-3">Program</th><th class="px-3 py-3">Sales</th><th class="px-3 py-3">Revenue</th><th class="px-3 py-3">Commission</th></tr></thead>
                <tbody>
                @forelse($programStats as $stat)
                    <tr class="border-b border-slate-100"><td class="px-3 py-4 font-bold">{{ $stat['program']->name }}</td><td class="px-3 py-4">{{ $stat['paid_orders'] }}</td><td class="px-3 py-4">₦{{ number_format($stat['paid_sales_amount'],2) }}</td><td class="px-3 py-4">₦{{ number_format($stat['total_commissions'],2) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-8 text-center text-slate-500">No program activity yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
</body>
</html>
