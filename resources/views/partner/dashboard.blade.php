<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#070b1d">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.svg') }}">
    <title>Partner Growth Hub · AIPM</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    @include('components.dashboard-payment-alert', ['count' => $totalPendingPaymentConfirmations ?? 0, 'role' => 'partner'])

    <header class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex items-center gap-2"><span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-violet-700">AIPM Partner Growth Hub</span><span class="text-xs font-bold text-slate-400">Sell • Recruit • Earn • Grow</span></div>
            <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Welcome back, {{ auth()->user()->name }}.</h1>
            <p class="mt-2 max-w-3xl text-slate-600">Everything you need to turn your audience into customers, customers into referrals, and referrals into a growing partner network.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('partner.storefront', ['partnerCode' => optional($programStats->first()['partner'] ?? null)->partner_code]) }}" class="rounded-xl bg-emerald-600 px-4 py-3 text-sm font-black text-white shadow-sm">🚀 My Store</a>
            <a href="{{ route('network.index') }}" class="rounded-xl bg-violet-600 px-4 py-3 text-sm font-black text-white shadow-sm">👥 My Network</a>
            <a href="{{ route('partner.payouts.index') }}" class="rounded-xl bg-slate-950 px-4 py-3 text-sm font-black text-white shadow-sm">💰 Payouts</a>
        </div>
    </header>

    <section class="mb-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-black uppercase tracking-wide text-slate-500">Total Sales</p><p class="mt-2 text-3xl font-black">₦{{ number_format($totalSalesAmount, 0) }}</p><p class="mt-1 text-xs text-slate-500">{{ $totalSales }} confirmed orders</p></div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm"><p class="text-xs font-black uppercase tracking-wide text-emerald-700">Available Earnings</p><p class="mt-2 text-3xl font-black text-emerald-700">₦{{ number_format($totalPending, 0) }}</p><p class="mt-1 text-xs text-emerald-700">Ready / eligible for payout</p></div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm"><p class="text-xs font-black uppercase tracking-wide text-blue-700">Lifetime Paid</p><p class="mt-2 text-3xl font-black text-blue-950">₦{{ number_format($totalPaid, 0) }}</p><p class="mt-1 text-xs text-blue-700">Already paid to you</p></div>
        <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5 shadow-sm"><p class="text-xs font-black uppercase tracking-wide text-violet-700">Partners Recruited</p><p class="mt-2 text-3xl font-black text-violet-950">{{ $totalRecruited }}</p><p class="mt-1 text-xs text-violet-700">People in your network</p></div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm"><p class="text-xs font-black uppercase tracking-wide text-amber-700">Needs Attention</p><p class="mt-2 text-3xl font-black text-amber-950">{{ $totalPendingPaymentConfirmations }}</p><p class="mt-1 text-xs text-amber-700">Orders awaiting payment confirmation</p></div>
    </section>

    <section class="mb-7 grid gap-5 lg:grid-cols-[1.55fr_.75fr]">
        <div class="overflow-hidden rounded-3xl bg-slate-950 p-7 text-white shadow-xl">
            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-300">Your #1 sales asset</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Stop explaining. Send people to your store.</h2>
                    <p class="mt-3 leading-7 text-slate-300">Your personal storefront gives prospects a simple path from discovery to product purchase while preserving your referral attribution.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-center"><div class="text-4xl">🚀</div><p class="mt-2 text-xs font-bold uppercase tracking-wide text-slate-400">Ready to share</p></div>
            </div>
            @if($programStats->isNotEmpty())
                @php $firstPartner = $programStats->first()['partner']; $storeUrl = route('partner.storefront', ['partnerCode' => $firstPartner->partner_code]); @endphp
                <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-2 text-xs font-black uppercase tracking-wide text-slate-400">Your referral storefront link</p>
                    <div class="flex flex-col gap-3 sm:flex-row"><input id="store-link" readonly value="{{ $storeUrl }}" class="min-w-0 flex-1 rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-slate-200 outline-none"><button type="button" onclick="navigator.clipboard.writeText(document.getElementById('store-link').value);this.innerText='Copied ✓';setTimeout(()=>this.innerText='Copy Link',1600)" class="rounded-xl bg-emerald-400 px-5 py-3 text-sm font-black text-slate-950">Copy Link</button></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-3"><a href="{{ $storeUrl }}" class="rounded-xl bg-white px-5 py-3 text-sm font-black text-slate-950">View My Store →</a><span class="rounded-xl border border-white/10 px-5 py-3 text-sm font-bold text-slate-300">Share this link everywhere</span></div>
            @endif
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-600">Growth mission</p>
            <h3 class="mt-2 text-2xl font-black">Your next 3 moves</h3>
            <div class="mt-5 space-y-4">
                <div class="flex gap-3"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm font-black text-violet-700">1</span><div><p class="font-black">Make your first sale</p><p class="text-sm text-slate-500">Share your storefront with warm prospects today.</p></div></div>
                <div class="flex gap-3"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-black text-emerald-700">2</span><div><p class="font-black">Recruit your first partner</p><p class="text-sm text-slate-500">Invite someone who already has an audience.</p></div></div>
                <div class="flex gap-3"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-sm font-black text-amber-700">3</span><div><p class="font-black">Help them make a sale</p><p class="text-sm text-slate-500">Your network grows when your recruits succeed.</p></div></div>
            </div>
        </div>
    </section>

    <section class="mb-7 grid gap-5 lg:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-black uppercase tracking-[0.18em] text-violet-600">Recruitment engine</p><h2 class="mt-1 text-2xl font-black">Build a network that keeps working</h2><p class="mt-2 text-sm text-slate-500">Your dashboard tracks both direct sales and the value created by the network you recruit.</p></div><a href="{{ route('network.index') }}" class="whitespace-nowrap rounded-xl bg-violet-50 px-4 py-2 text-sm font-black text-violet-700">Open Network →</a></div>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-slate-50 p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Direct commission</p><p class="mt-2 text-2xl font-black">₦{{ number_format((float)$programStats->sum('direct_commission'), 0) }}</p><p class="mt-1 text-xs text-slate-500">From your direct sales</p></div>
                <div class="rounded-2xl bg-violet-50 p-5"><p class="text-xs font-bold uppercase tracking-wide text-violet-600">Network commission</p><p class="mt-2 text-2xl font-black text-violet-950">₦{{ number_format((float)$programStats->sum('recruiter_commission'), 0) }}</p><p class="mt-1 text-xs text-violet-600">From deeper network levels</p></div>
                <div class="rounded-2xl bg-emerald-50 p-5"><p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Network size</p><p class="mt-2 text-2xl font-black text-emerald-950">{{ $totalRecruited }}</p><p class="mt-1 text-xs text-emerald-700">Partners recruited by you</p></div>
            </div>
            <div class="mt-6 rounded-2xl border border-dashed border-violet-200 bg-violet-50/50 p-5"><p class="font-black text-violet-950">Recruiting tip</p><p class="mt-1 text-sm leading-6 text-violet-900">Don't pitch "join my network" first. Lead with the opportunity: <strong>learn what the product does, show the earning opportunity, then invite them to start.</strong></p></div>
        </div>
        <div class="rounded-3xl bg-gradient-to-br from-violet-600 to-indigo-700 p-6 text-white shadow-lg">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-200">Recruitment script</p>
            <h3 class="mt-2 text-2xl font-black">Copy. Paste. Start conversations.</h3>
            <div class="mt-5 rounded-2xl bg-white/10 p-4 text-sm leading-6 text-violet-50">"I found a practical way to earn from products I already know how to recommend. You don't need to build a product yourself. Want me to show you how it works?"</div>
            <button type="button" onclick="navigator.clipboard.writeText('I found a practical way to earn from products I already know how to recommend. You don\'t need to build a product yourself. Want me to show you how it works?');this.innerText='Script copied ✓';setTimeout(()=>this.innerText='Copy recruitment script',1600)" class="mt-4 w-full rounded-xl bg-white px-4 py-3 text-sm font-black text-violet-700">Copy recruitment script</button>
        </div>
    </section>

    <section class="mb-7 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-black uppercase tracking-[0.18em] text-violet-600">Programs & products</p><h2 class="mt-1 text-2xl font-black">Know what you're selling</h2></div><p class="text-sm text-slate-500">Use the storefront to present the products and let the sales page do the heavy lifting.</p></div>
        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($programStats as $stat)
                <article class="rounded-2xl border border-slate-200 p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3"><div><p class="text-xs font-black uppercase tracking-wide text-violet-600">Program</p><h3 class="mt-1 text-lg font-black">{{ $stat['program']->name }}</h3></div><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-black text-emerald-700">Active</span></div>
                    <div class="mt-4 grid grid-cols-2 gap-3"><div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">Sales</p><p class="mt-1 font-black">{{ $stat['paid_orders'] }}</p></div><div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">Sales value</p><p class="mt-1 font-black">₦{{ number_format($stat['paid_sales_amount'],0) }}</p></div></div>
                    <div class="mt-4 flex items-center justify-between text-sm"><span class="font-semibold text-slate-500">Partners recruited</span><span class="font-black">{{ $stat['recruited_partners_count'] }}</span></div>
                    <div class="mt-2 flex items-center justify-between text-sm"><span class="font-semibold text-slate-500">Total commissions</span><span class="font-black text-emerald-700">₦{{ number_format($stat['total_commissions'],0) }}</span></div>
                </article>
            @empty
                <div class="rounded-2xl bg-slate-50 p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">No active partnership programs yet.</div>
            @endforelse
        </div>
    </section>

    <section class="mb-7 grid gap-5 lg:grid-cols-[1.25fr_.75fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between"><div><p class="text-xs font-black uppercase tracking-[0.18em] text-violet-600">Sales activity</p><h2 class="mt-1 text-2xl font-black">Recent confirmed sales</h2></div><span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">{{ $totalSales }} total</span></div>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full text-left text-sm"><thead><tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500"><th class="px-3 py-3">Order</th><th class="px-3 py-3">Customer</th><th class="px-3 py-3">Sale</th><th class="px-3 py-3">Your earning</th><th class="px-3 py-3">Status</th></tr></thead><tbody>
                @php $recentSales = $programStats->flatMap(fn($s) => $s['sale_breakdown']->map(fn($sale) => ['sale'=>$sale,'program'=>$s['program']]))->sortByDesc(fn($x) => optional($x['sale']['order'])->created_at)->take(8); @endphp
                @forelse($recentSales as $item)
                    @php $sale=$item['sale']; $order=$sale['order']; @endphp
                    <tr class="border-b border-slate-100"><td class="px-3 py-4 font-bold">{{ $order->order_number }}</td><td class="px-3 py-4">{{ $order->customer_name }}</td><td class="px-3 py-4 font-semibold">₦{{ number_format($sale['sale_value'],0) }}</td><td class="px-3 py-4 font-black text-emerald-700">₦{{ number_format($sale['partner_earnings'],0) }}</td><td class="px-3 py-4"><span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-black text-emerald-700">{{ ucfirst($order->status) }}</span></td></tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-8 text-center text-slate-500">Your first confirmed sale will appear here.</td></tr>
                @endforelse
                </tbody></table>
            </div>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">Conversion checklist</p><h3 class="mt-2 text-2xl font-black text-amber-950">Before you share</h3>
            <div class="mt-5 space-y-4 text-sm text-amber-950"><div class="flex gap-3"><span>✓</span><p>Know the <strong>problem</strong> your product solves.</p></div><div class="flex gap-3"><span>✓</span><p>Show the prospect the <strong>outcome</strong>, not just features.</p></div><div class="flex gap-3"><span>✓</span><p>Send your <strong>storefront link</strong> instead of a long explanation.</p></div><div class="flex gap-3"><span>✓</span><p>Follow up with people who showed interest.</p></div><div class="flex gap-3"><span>✓</span><p>Recruit people who can genuinely <strong>sell or refer</strong>.</p></div></div>
        </div>
    </section>

    <section class="mb-7 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-xs font-black uppercase tracking-[0.18em] text-violet-600">Earnings command center</p><h2 class="mt-1 text-2xl font-black">Understand where your money comes from</h2></div><a href="{{ route('partner.payouts.index') }}" class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white">Manage payouts →</a></div>
        <div class="mt-5 grid gap-4 sm:grid-cols-3"><div class="rounded-2xl border border-slate-200 p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Direct sales earnings</p><p class="mt-2 text-2xl font-black">₦{{ number_format((float)$programStats->sum('direct_commission'),0) }}</p><p class="mt-1 text-xs text-slate-500">Commission from your direct customer sales.</p></div><div class="rounded-2xl border border-violet-200 bg-violet-50 p-5"><p class="text-xs font-bold uppercase tracking-wide text-violet-700">Recruiter earnings</p><p class="mt-2 text-2xl font-black text-violet-950">₦{{ number_format((float)$programStats->sum('recruiter_commission'),0) }}</p><p class="mt-1 text-xs text-violet-700">Commission from eligible deeper-level network activity.</p></div><div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5"><p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Total commission generated</p><p class="mt-2 text-2xl font-black text-emerald-950">₦{{ number_format((float)$programStats->sum('total_commissions'),0) }}</p><p class="mt-1 text-xs text-emerald-700">Across your confirmed sales.</p></div></div>
    </section>

    <footer class="pb-8 text-center text-xs font-semibold text-slate-400">AIPM Partner Growth Hub · Your goal is simple: more conversations → more customers → more partners → more earnings.</footer>
</div>
</body>
</html>
