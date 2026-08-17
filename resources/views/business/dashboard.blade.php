<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $programs->first()?->owner?->business_name ?? auth()->user()->business_name ?? 'Business' }} — LAMI AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background:#f6f7fb; }
        .brand-gradient { background:linear-gradient(135deg,#6d28d9 0%,#a21caf 100%); }
        .brand-text { background:linear-gradient(135deg,#6d28d9,#a21caf); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .soft-shadow { box-shadow:0 18px 50px rgba(15,23,42,.07); }
    </style>
</head>
<body class="min-h-screen text-slate-900 antialiased">
<div class="min-h-screen lg:flex">
    <aside class="hidden w-72 shrink-0 border-r border-slate-200 bg-white lg:flex lg:flex-col">
        <div class="border-b border-slate-100 p-7">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-slate-950 text-lg font-black text-white">L</span>
                <span class="text-xl font-black">LAMI <span class="text-violet-600">AI</span></span>
            </a>
            <p class="mt-2 text-xs font-bold uppercase tracking-widest text-slate-400">Business Marketing</p>
        </div>
        <nav class="flex-1 space-y-1 p-4">
            <a href="{{ route('business.dashboard') }}" class="flex items-center gap-3 rounded-xl bg-violet-50 px-4 py-3 text-sm font-black text-violet-700">⌂ <span>Dashboard</span></a>
            <a href="#programs" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">◈ <span>Programs</span></a>
            <a href="#products" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">□ <span>Products</span></a>
            <a href="#affiliates" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">♧ <span>Affiliates</span></a>
            <a href="#sales" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">↗ <span>Sales</span></a>
            <a href="#commissions" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">₦ <span>Commissions</span></a>
        </nav>
        <div class="border-t border-slate-100 p-4">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">⚙ <span>Settings</span></a>
            <div class="mt-3 rounded-2xl bg-slate-950 p-4 text-white">
                <p class="text-xs font-bold text-slate-400">SIGNED IN AS</p>
                <p class="mt-1 truncate font-bold">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </aside>

    <main class="min-w-0 flex-1">
        <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 px-5 py-4 backdrop-blur lg:px-10">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-violet-600">Business dashboard</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight lg:text-3xl">Good to see you, {{ Str::before(auth()->user()->name, ' ') }}.</h1>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('business.onboarding', ['step' => 'product']) }}" class="hidden rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 hover:bg-slate-50 sm:inline-flex">+ Add product</a>
                    <a href="{{ route('business.onboarding', ['step' => 'commission']) }}" class="brand-gradient rounded-xl px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-violet-200">Create program</a>
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-[1500px] space-y-7 p-5 lg:p-10">
            @if(session('success'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">✓ {{ session('success') }}</div>
            @endif

            @if($programs->isEmpty())
                <section class="brand-gradient overflow-hidden rounded-[2rem] p-7 text-white soft-shadow lg:p-10">
                    <div class="max-w-2xl">
                        <p class="text-sm font-black uppercase tracking-widest text-violet-200">Your affiliate engine starts here</p>
                        <h2 class="mt-3 text-4xl font-black tracking-tight lg:text-5xl">Turn your customers and creators into a sales force.</h2>
                        <p class="mt-4 max-w-xl leading-7 text-violet-100">Create your first affiliate program, decide what partners earn, and start tracking sales from one place.</p>
                        <a href="{{ route('business.start') }}" class="mt-7 inline-flex rounded-xl bg-white px-5 py-3 font-black text-violet-700">Create my first program →</a>
                    </div>
                </section>
            @else
                <section class="brand-gradient overflow-hidden rounded-[2rem] p-7 text-white soft-shadow lg:p-9">
                    <div class="flex flex-col justify-between gap-7 lg:flex-row lg:items-end">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-black uppercase tracking-widest">Live program</span>
                                <span class="rounded-full bg-emerald-400 px-3 py-1 text-xs font-black text-emerald-950">ACTIVE</span>
                            </div>
                            <h2 class="mt-4 text-3xl font-black tracking-tight lg:text-4xl">{{ $programs->first()->name }}</h2>
                            <p class="mt-2 max-w-2xl text-violet-100">{{ $programs->first()->description }}</p>
                            <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm font-bold text-violet-100">
                                <span>{{ $programs->first()->partners_count }} affiliates</span>
                                <span>{{ $programs->first()->orders_count }} orders</span>
                                <span>{{ $programs->first()->products_count }} products</span>
                                @if($programs->first()->commissionRules->first())<span>{{ $programs->first()->commissionRules->first()->value }}% direct commission</span>@endif
                            </div>
                        </div>
                        <a href="#programs" class="inline-flex shrink-0 rounded-xl bg-white px-5 py-3 text-sm font-black text-violet-700">Manage program →</a>
                    </div>
                </section>
            @endif

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach([
                    ['Revenue','₦'.number_format($stats['revenue'], 2), 'Affiliate-attributed sales', '↗'],
                    ['Sales',number_format($stats['sales']), 'Completed affiliate orders', '◉'],
                    ['Commission', '₦'.number_format($stats['commission'], 2), 'Total partner earnings', '₦'],
                    ['Active affiliates',number_format($stats['affiliates']), 'Across your programs', '♧'],
                ] as $card)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 soft-shadow">
                        <div class="flex items-center justify-between"><p class="text-sm font-bold text-slate-500">{{ $card[0] }}</p><span class="grid h-9 w-9 place-items-center rounded-xl bg-violet-50 font-black text-violet-600">{{ $card[3] }}</span></div>
                        <p class="mt-4 text-2xl font-black tracking-tight">{{ $card[1] }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-400">{{ $card[2] }}</p>
                    </div>
                @endforeach
            </section>

            <div class="grid gap-7 xl:grid-cols-[1.5fr_1fr]">
                <section id="sales" class="rounded-2xl border border-slate-200 bg-white p-6 soft-shadow">
                    <div class="flex items-center justify-between">
                        <div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Performance</p><h2 class="mt-1 text-xl font-black">Affiliate sales</h2></div>
                        <select class="rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold"><option>Last 30 days</option><option>Last 7 days</option><option>Last 90 days</option></select>
                    </div>
                    <div class="mt-7 flex h-56 items-end gap-2 rounded-2xl bg-slate-50 p-5">
                        @php $heights = [24,38,31,52,45,68,56,74,61,83,70,92,78,88,66,76,96,81,90,73,84,98,89,100,86,94,78,91,83,97]; @endphp
                        @foreach($heights as $height)
                            <div class="group flex h-full flex-1 items-end"><div class="w-full rounded-t-md bg-gradient-to-t from-violet-600 to-fuchsia-400 transition group-hover:from-violet-700" style="height:{{ $height }}%"></div></div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex justify-between text-[11px] font-bold text-slate-400"><span>30 days ago</span><span>Today</span></div>
                </section>

                <section id="commissions" class="rounded-2xl border border-slate-200 bg-white p-6 soft-shadow">
                    <div class="flex items-center justify-between"><div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Money</p><h2 class="mt-1 text-xl font-black">Commission health</h2></div><span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700">Live</span></div>
                    <div class="mt-7 space-y-5">
                        <div><div class="flex justify-between text-sm font-bold"><span class="text-slate-500">Pending / payable</span><span>₦{{ number_format($stats['pending_commission'], 2) }}</span></div><div class="mt-2 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-amber-400" style="width:{{ $stats['commission'] > 0 ? min(100, ($stats['pending_commission'] / $stats['commission']) * 100) : 0 }}%"></div></div></div>
                        <div><div class="flex justify-between text-sm font-bold"><span class="text-slate-500">Paid to affiliates</span><span>₦{{ number_format($stats['paid_commission'], 2) }}</span></div><div class="mt-2 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-emerald-500" style="width:{{ $stats['commission'] > 0 ? min(100, ($stats['paid_commission'] / $stats['commission']) * 100) : 0 }}%"></div></div></div>
                    </div>
                    <div class="mt-8 grid grid-cols-2 gap-3"><div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-bold text-slate-400">Products</p><p class="mt-1 text-xl font-black">{{ $stats['products'] }}</p></div><div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-bold text-slate-400">Programs</p><p class="mt-1 text-xl font-black">{{ $stats['programs'] }}</p></div></div>
                </section>
            </div>

            <div class="grid gap-7 xl:grid-cols-[1.2fr_1fr]">
                <section id="affiliates" class="rounded-2xl border border-slate-200 bg-white soft-shadow">
                    <div class="flex items-center justify-between border-b border-slate-100 p-6"><div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Your sales force</p><h2 class="mt-1 text-xl font-black">Top affiliates</h2></div><span class="text-xs font-bold text-slate-400">Ranked by revenue</span></div>
                    @forelse($topAffiliates as $index => $affiliate)
                        <div class="flex items-center gap-4 border-b border-slate-100 px-6 py-4 last:border-0">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-violet-50 font-black text-violet-700">{{ strtoupper(substr($affiliate['partner']?->user?->name ?? 'A', 0, 1)) }}</div>
                            <div class="min-w-0 flex-1"><p class="truncate font-black">{{ $affiliate['partner']?->user?->name ?? 'Affiliate #'.$affiliate['partner']?->id }}</p><p class="text-xs font-semibold text-slate-400">{{ $affiliate['sales'] }} sales · {{ $affiliate['partner']?->partner_code }}</p></div>
                            <div class="text-right"><p class="font-black">₦{{ number_format($affiliate['revenue'], 2) }}</p><p class="text-xs font-bold text-emerald-600">₦{{ number_format($affiliate['commission'], 2) }} earned</p></div>
                        </div>
                    @empty
                        <div class="p-10 text-center"><p class="font-black">No affiliate sales yet</p><p class="mt-1 text-sm text-slate-400">Your top partners will appear here once sales start coming in.</p></div>
                    @endforelse
                </section>

                <section id="products" class="rounded-2xl border border-slate-200 bg-white soft-shadow">
                    <div class="flex items-center justify-between border-b border-slate-100 p-6"><div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Catalog</p><h2 class="mt-1 text-xl font-black">Products</h2></div><a href="{{ route('business.onboarding', ['step' => 'product']) }}" class="text-sm font-black text-violet-600">+ Add</a></div>
                    @forelse($products->take(5) as $product)
                        <div class="flex items-center gap-4 border-b border-slate-100 px-6 py-4 last:border-0"><div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-slate-950 font-black text-white">{{ strtoupper(substr($product->name,0,1)) }}</div><div class="min-w-0 flex-1"><p class="truncate font-black">{{ $product->name }}</p><p class="text-xs font-semibold text-slate-400">{{ $product->partnershipPrograms->count() }} program(s) · {{ strtoupper($product->status) }}</p></div><p class="font-black">{{ $product->currency }} {{ number_format((float)$product->price, 2) }}</p></div>
                    @empty
                        <div class="p-10 text-center"><p class="font-black">No products yet</p><a href="{{ route('business.start') }}" class="mt-2 inline-block text-sm font-black text-violet-600">Create one →</a></div>
                    @endforelse
                </section>
            </div>

            <section id="programs" class="rounded-2xl border border-slate-200 bg-white soft-shadow">
                <div class="flex flex-col justify-between gap-3 border-b border-slate-100 p-6 sm:flex-row sm:items-center"><div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Affiliate programs</p><h2 class="mt-1 text-xl font-black">Programs</h2></div><a href="{{ route('business.start') }}" class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white">+ New program</a></div>
                <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400"><tr><th class="px-6 py-4">Program</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Affiliates</th><th class="px-6 py-4">Orders</th><th class="px-6 py-4">Commission</th></tr></thead><tbody>
                    @foreach($programs as $program)<tr class="border-t border-slate-100"><td class="px-6 py-4"><p class="font-black">{{ $program->name }}</p><p class="text-xs text-slate-400">{{ $program->slug }}</p></td><td class="px-6 py-4"><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-black text-emerald-700">{{ strtoupper($program->status) }}</span></td><td class="px-6 py-4 font-bold">{{ $program->partners_count }}</td><td class="px-6 py-4 font-bold">{{ $program->orders_count }}</td><td class="px-6 py-4 font-bold">{{ $program->commissionRules->first()?->value ?? 0 }}% direct</td></tr>@endforeach
                </tbody></table></div>
            </section>

            <section id="sales" class="rounded-2xl border border-slate-200 bg-white soft-shadow">
                <div class="border-b border-slate-100 p-6"><p class="text-xs font-black uppercase tracking-widest text-violet-600">Activity</p><h2 class="mt-1 text-xl font-black">Recent sales</h2></div>
                <div class="divide-y divide-slate-100">
                    @forelse($recentOrders as $order)
                        <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center"><div class="grid h-10 w-10 place-items-center rounded-xl bg-violet-50 text-violet-700">↗</div><div class="min-w-0 flex-1"><p class="font-black">Order {{ $order->order_number }}</p><p class="text-xs font-semibold text-slate-400">{{ $order->partner?->user?->name ?? 'Direct affiliate' }} · {{ $order->created_at?->diffForHumans() }}</p></div><div class="text-left sm:text-right"><p class="font-black">{{ $order->currency }} {{ number_format((float)$order->total, 2) }}</p><span class="text-xs font-bold text-slate-400">{{ ucfirst($order->status) }}</span></div></div>
                    @empty
                        <div class="p-10 text-center"><p class="font-black">Your first affiliate sale will appear here.</p><p class="mt-1 text-sm text-slate-400">Share your program with partners to get started.</p></div>
                    @endforelse
                </div>
            </section>

            <footer class="pb-5 pt-2 text-center text-xs font-semibold text-slate-400">LAMI AI Business Marketing · Grow with performance-based partnerships.</footer>
        </div>
    </main>
</div>
</body>
</html>
