<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ auth()->user()->business_name ?? 'Business' }} — AIPM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<div class="min-h-screen lg:flex">
    <aside class="hidden w-72 shrink-0 border-r border-slate-200 bg-white lg:flex lg:flex-col">
        <div class="border-b border-slate-100 p-7">
            <a href="{{ route('business.dashboard') }}" class="text-xl font-black">AIPM</a>
            <p class="mt-2 text-xs font-bold uppercase tracking-widest text-slate-400">AI Powered Marketing</p>
        </div>
        <nav class="flex-1 space-y-1 p-4">
            <a href="{{ route('business.dashboard') }}" class="block rounded-xl bg-violet-50 px-4 py-3 text-sm font-black text-violet-700">Dashboard</a>
            <a href="{{ route('business.programs.index') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Programs</a>
            <a href="{{ route('business.products.index') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Products</a>
            <a href="{{ route('business.affiliates.index') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Affiliates</a>
            <a href="{{ route('network.index') }}" class="block rounded-xl bg-violet-50 px-4 py-3 text-sm font-black text-violet-700">Recruitment Network</a>
            <a href="{{ route('business.sales.index') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Sales</a>
            <a href="{{ route('business.commissions.index') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Commissions</a>
            <a href="{{ route('business.payouts.index') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Payouts</a>
        </nav>
        <div class="border-t border-slate-100 p-4">
            <a href="{{ route('profile.edit') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Settings</a>
        </div>
    </aside>

    <main class="min-w-0 flex-1">
        <header class="border-b border-slate-200 bg-white px-5 py-5 lg:px-10">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Business dashboard</p><h1 class="mt-1 text-3xl font-black">{{ auth()->user()->business_name ?? 'Your Business' }}</h1></div>
                <div class="flex gap-2">
                    <a href="{{ route('network.index') }}" class="rounded-xl border border-violet-200 bg-violet-50 px-4 py-2.5 text-sm font-black text-violet-700">Network</a>
                    <a href="{{ route('business.products.create') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-black">+ Add product</a>
                    <a href="{{ route('business.payouts.index') }}" class="rounded-xl border border-violet-200 bg-violet-50 px-4 py-2.5 text-sm font-black text-violet-700">Request payout</a>
                    <a href="{{ route('business.start') }}" class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-black text-white">Create program</a>
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-7xl space-y-7 p-5 lg:p-10">
            @if(session('success'))<div class="rounded-xl bg-emerald-50 p-4 text-sm font-bold text-emerald-700">{{ session('success') }}</div>@endif

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <a href="{{ route('business.sales.index') }}" class="rounded-2xl bg-white p-6 shadow-sm hover:ring-2 hover:ring-violet-200"><p class="text-sm font-bold text-slate-500">Gross revenue</p><p class="mt-3 text-2xl font-black">₦{{ number_format($stats['revenue'],2) }}</p><p class="mt-1 text-xs text-slate-400">View sales →</p></a>
                <a href="{{ route('business.commissions.index') }}" class="rounded-2xl bg-white p-6 shadow-sm hover:ring-2 hover:ring-violet-200"><p class="text-sm font-bold text-slate-500">Partner commissions</p><p class="mt-3 text-2xl font-black">₦{{ number_format($stats['commission'],2) }}</p><p class="mt-1 text-xs text-slate-400">View commissions →</p></a>
                <a href="{{ route('business.payouts.index') }}" class="rounded-2xl bg-white p-6 shadow-sm hover:ring-2 hover:ring-violet-200"><p class="text-sm font-bold text-slate-500">Available to withdraw</p><p class="mt-3 text-2xl font-black">₦{{ number_format($stats['business_available'],2) }}</p><p class="mt-1 text-xs text-slate-400">Request business payout →</p></a>
                <a href="{{ route('business.payouts.index') }}" class="rounded-2xl bg-white p-6 shadow-sm hover:ring-2 hover:ring-violet-200"><p class="text-sm font-bold text-slate-500">In payout processing</p><p class="mt-3 text-2xl font-black">₦{{ number_format($stats['business_requested'],2) }}</p><p class="mt-1 text-xs text-slate-400">Track payouts →</p></a>
                <a href="{{ route('business.payouts.index') }}" class="rounded-2xl bg-white p-6 shadow-sm hover:ring-2 hover:ring-violet-200"><p class="text-sm font-bold text-slate-500">Paid to business</p><p class="mt-3 text-2xl font-black">₦{{ number_format($stats['business_paid'],2) }}</p><p class="mt-1 text-xs text-slate-400">Payout history →</p></a>
            </section>

            <div class="rounded-2xl border border-violet-100 bg-violet-50 p-5 text-sm text-violet-900">
                <b>Payout protection:</b> paid sales become eligible after {{ $stats['payout_delay_days'] }} day{{ $stats['payout_delay_days'] == 1 ? '' : 's' }}. The business receives the sale value after partner and recruiter commissions are reserved.
            </div>

            <section class="rounded-2xl bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 p-6"><div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Programs</p><h2 class="text-xl font-black">Your affiliate programs</h2></div><a href="{{ route('business.programs.index') }}" class="text-sm font-black text-violet-600">View all →</a></div>
                <div class="divide-y divide-slate-100">
                    @forelse($programs as $program)
                        <a href="{{ route('business.programs.edit', $program) }}" class="flex items-center justify-between gap-4 p-6 hover:bg-slate-50"><div><p class="font-black">{{ $program->name }}</p><p class="mt-1 text-xs text-slate-400">{{ $program->partners_count }} affiliates · {{ $program->orders_count }} orders · {{ $program->products_count }} products</p></div><span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">{{ strtoupper($program->status) }}</span></a>
                    @empty
                        <div class="p-10 text-center"><p class="font-black">No programs yet</p><a href="{{ route('business.start') }}" class="mt-3 inline-block font-black text-violet-600">Create your first program →</a></div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 p-6"><div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Catalog</p><h2 class="text-xl font-black">Products</h2></div><a href="{{ route('business.products.index') }}" class="text-sm font-black text-violet-600">View all →</a></div>
                <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($products->take(6) as $product)
                        <a href="{{ route('business.products.edit', $product) }}" class="rounded-xl border border-slate-200 p-5 hover:border-violet-300"><p class="font-black">{{ $product->name }}</p><p class="mt-1 text-sm text-slate-500">{{ $product->currency }} {{ number_format((float)$product->price,2) }}</p><p class="mt-3 text-xs font-bold text-violet-600">Edit product →</p></a>
                    @empty
                        <a href="{{ route('business.products.create') }}" class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm font-black text-violet-600">+ Create product</a>
                    @endforelse
                </div>
            </section>

            <section class="grid gap-7 lg:grid-cols-4">
                <a href="{{ route('business.affiliates.index') }}" class="rounded-2xl bg-white p-6 shadow-sm hover:ring-2 hover:ring-violet-200"><p class="text-xs font-black uppercase tracking-widest text-violet-600">Sales force</p><h2 class="mt-1 text-xl font-black">Affiliates</h2><p class="mt-3 text-sm text-slate-500">Manage the people promoting your products and see their performance.</p><span class="mt-5 inline-block text-sm font-black text-violet-600">Manage affiliates →</span></a>
                <a href="{{ route('network.index') }}" class="rounded-2xl bg-white p-6 shadow-sm hover:ring-2 hover:ring-violet-200"><p class="text-xs font-black uppercase tracking-widest text-violet-600">Recruitment</p><h2 class="mt-1 text-xl font-black">Network</h2><p class="mt-3 text-sm text-slate-500">See your affiliates and the partners they recruit in a visual tree.</p><span class="mt-5 inline-block text-sm font-black text-violet-600">View network →</span></a>
                <a href="{{ route('business.commissions.index') }}" class="rounded-2xl bg-white p-6 shadow-sm hover:ring-2 hover:ring-violet-200"><p class="text-xs font-black uppercase tracking-widest text-violet-600">Money out</p><h2 class="mt-1 text-xl font-black">Commissions</h2><p class="mt-3 text-sm text-slate-500">Review partner and recruiter earnings and payout status.</p><span class="mt-5 inline-block text-sm font-black text-violet-600">Manage commissions →</span></a>
                <a href="{{ route('business.payouts.index') }}" class="rounded-2xl bg-white p-6 shadow-sm hover:ring-2 hover:ring-violet-200"><p class="text-xs font-black uppercase tracking-widest text-violet-600">Money in</p><h2 class="mt-1 text-xl font-black">Business payouts</h2><p class="mt-3 text-sm text-slate-500">Withdraw your net sale proceeds and track every business payment.</p><span class="mt-5 inline-block text-sm font-black text-violet-600">Manage payouts →</span></a>
            </section>

            <section class="rounded-2xl bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 p-6"><div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Activity</p><h2 class="text-xl font-black">Recent sales</h2></div><a href="{{ route('business.sales.index') }}" class="text-sm font-black text-violet-600">View all →</a></div>
                <div class="divide-y divide-slate-100">
                    @forelse($recentOrders as $order)
                        <a href="{{ route('business.sales.index') }}" class="flex items-center justify-between gap-4 p-6 hover:bg-slate-50"><div><p class="font-black">Order {{ $order->order_number }}</p><p class="text-xs text-slate-400">{{ $order->partner?->user?->name ?? 'Direct affiliate' }} · {{ $order->created_at?->diffForHumans() }}</p></div><div class="text-right"><p class="font-black">{{ $order->currency }} {{ number_format((float)$order->total,2) }}</p><p class="text-xs text-slate-400">{{ ucfirst($order->status) }}</p></div></a>
                    @empty
                        <div class="p-10 text-center text-sm text-slate-400">Your affiliate sales will appear here.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
