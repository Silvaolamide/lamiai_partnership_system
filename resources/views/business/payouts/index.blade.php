<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Payouts — AIPM</title>
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
            <a href="{{ route('business.dashboard') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Dashboard</a>
            <a href="{{ route('business.programs.index') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Programs</a>
            <a href="{{ route('business.products.index') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Products</a>
            <a href="{{ route('business.affiliates.index') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Affiliates</a>
            <a href="{{ route('business.sales.index') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Sales</a>
            <a href="{{ route('business.commissions.index') }}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Commissions</a>
            <a href="{{ route('business.payouts.index') }}" class="block rounded-xl bg-violet-50 px-4 py-3 text-sm font-black text-violet-700">Payouts</a>
        </nav>
    </aside>

    <main class="min-w-0 flex-1">
        <header class="border-b border-slate-200 bg-white px-5 py-5 lg:px-10">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Business finance</p><h1 class="mt-1 text-3xl font-black">Payouts</h1></div>
                <a href="{{ route('business.dashboard') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-black">Back to dashboard</a>
            </div>
        </header>

        <div class="mx-auto max-w-7xl space-y-7 p-5 lg:p-10">
            @if(session('success'))<div class="rounded-xl bg-emerald-50 p-4 text-sm font-bold text-emerald-700">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="rounded-xl bg-rose-50 p-4 text-sm font-bold text-rose-700">{{ $errors->first() }}</div>@endif

            <section class="grid gap-4 md:grid-cols-2">
                @foreach($eligibleByCurrency as $currency => $summary)
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <p class="text-sm font-bold text-slate-500">Available to withdraw · {{ $currency }}</p>
                        <p class="mt-2 text-3xl font-black">{{ $currency }} {{ number_format($summary['total'], 2) }}</p>
                        <p class="mt-2 text-xs text-slate-400">{{ $summary['orders']->count() }} eligible sale{{ $summary['orders']->count() === 1 ? '' : 's' }}</p>
                    </div>
                @endforeach
                @if($eligibleByCurrency->isEmpty())
                    <div class="rounded-2xl bg-white p-6 shadow-sm md:col-span-2">
                        <p class="font-black">No sales are currently eligible.</p>
                        <p class="mt-1 text-sm text-slate-500">Paid sales become withdrawable after the platform payout protection period.</p>
                    </div>
                @endif
            </section>

            <form method="POST" action="{{ route('business.payouts.store') }}" class="rounded-2xl bg-white shadow-sm">
                @csrf
                <div class="border-b border-slate-100 p-6">
                    <p class="text-xs font-black uppercase tracking-widest text-violet-600">Request payment</p>
                    <h2 class="mt-1 text-xl font-black">Select sales to withdraw</h2>
                    <p class="mt-1 text-sm text-slate-500">The business receives the sale value after partner/recruit commissions have been reserved.</p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($eligibleOrders as $order)
                        <label class="flex cursor-pointer items-center gap-4 p-5 hover:bg-slate-50">
                            <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="rounded text-violet-600">
                            <div class="min-w-0 flex-1">
                                <p class="font-black">{{ $order->order_number }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $order->program?->name }} · {{ $order->paid_at?->format('M j, Y') }} · {{ $order->partner?->user?->name ?? 'Direct sale' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-black">{{ $order->currency }} {{ number_format($order->business_net_amount, 2) }}</p>
                                @if($order->commission_total > 0)<p class="text-xs text-slate-400">{{ $order->currency }} {{ number_format($order->commission_total, 2) }} commissions</p>@endif
                            </div>
                        </label>
                    @empty
                        <div class="p-10 text-center text-sm text-slate-400">No eligible sales yet.</div>
                    @endforelse
                </div>

                @if($eligibleOrders->isNotEmpty())
                    <div class="grid gap-4 border-t border-slate-100 p-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-bold text-slate-700">Payout method</label>
                            <input name="method" value="{{ old('method', 'bank_transfer') }}" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700">Notes</label>
                            <input name="notes" value="{{ old('notes') }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-violet-500 focus:ring-violet-500" placeholder="Optional payment instructions">
                        </div>
                    </div>
                    <div class="flex justify-end border-t border-slate-100 p-6">
                        <button class="rounded-xl bg-violet-600 px-6 py-3 text-sm font-black text-white hover:bg-violet-700">Request payout</button>
                    </div>
                @endif
            </form>

            <section class="rounded-2xl bg-white shadow-sm">
                <div class="border-b border-slate-100 p-6"><p class="text-xs font-black uppercase tracking-widest text-violet-600">History</p><h2 class="mt-1 text-xl font-black">Business payout requests</h2></div>
                <div class="divide-y divide-slate-100">
                    @forelse($businessPayouts as $payout)
                        <div class="flex flex-wrap items-center justify-between gap-4 p-5">
                            <div><p class="font-black">{{ $payout->reference }}</p><p class="mt-1 text-xs text-slate-400">{{ $payout->orders_count }} sales · {{ $payout->requested_at?->format('M j, Y g:i A') }}</p></div>
                            <div class="text-right"><p class="font-black">{{ $payout->currency }} {{ number_format((float)$payout->amount, 2) }}</p><span class="text-xs font-bold uppercase text-slate-500">{{ $payout->status }}</span></div>
                        </div>
                    @empty
                        <div class="p-10 text-center text-sm text-slate-400">No payout requests yet.</div>
                    @endforelse
                </div>
                <div class="p-5">{{ $businessPayouts->links() }}</div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
