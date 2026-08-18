@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px]">
        <div class="mb-6 flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
            <div>
                <a href="{{ route('admin.analytics.businesses') }}" class="mb-3 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:border-violet-200 hover:text-violet-700">← All Businesses</a>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-violet-600">Business intelligence</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">{{ $business->business_name ?: $business->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $business->name }} · {{ $business->email }} · {{ $business->business_phone ?: 'No phone' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $business->business_super_admin_approved_at ? 'bg-emerald-50 text-emerald-700' : ($business->business_rejected_at ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ $business->business_super_admin_approved_at ? 'Approved' : ($business->business_rejected_at ? 'Rejected' : 'Pending approval') }}</span>
                <p class="mt-2 text-[11px] font-semibold text-slate-400">Joined {{ $business->created_at?->format('d M Y H:i') }}</p>
            </div>
        </div>

        <form method="GET" class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid gap-3 md:grid-cols-4 md:items-end">
                <div><label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-slate-400">From</label><input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm font-semibold focus:border-violet-500 focus:ring-violet-500"></div>
                <div><label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-slate-400">To</label><input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm font-semibold focus:border-violet-500 focus:ring-violet-500"></div>
                <div class="flex gap-2 md:col-span-2"><button class="rounded-xl bg-slate-950 px-5 py-2.5 text-xs font-black text-white transition hover:bg-violet-700">Apply filters</button><a href="{{ route('admin.analytics.business', $business) }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-xs font-black text-slate-500 transition hover:bg-slate-50">Reset</a></div>
            </div>
        </form>

        <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-8">
            @foreach([['Gross Sales','₦'.number_format($stats['gross_sales'],2),'violet'],['Net Revenue','₦'.number_format($stats['net_revenue'],2),'emerald'],['Commissions','₦'.number_format($stats['commissions'],2),'fuchsia'],['Paid Orders',$stats['paid_orders'],'blue'],['Customers',$stats['customers'],'cyan'],['Partners',$stats['partners'],'amber'],['Programs',$stats['programs'],'indigo'],['Avg. Order','₦'.number_format($stats['average_order'],2),'slate']] as $stat)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $stat[0] }}</span>
                <div class="mt-2 truncate text-lg font-black tracking-tight text-slate-950">{{ $stat[1] }}</div>
            </div>
            @endforeach
        </div>

        <div class="mb-6 grid gap-5 lg:grid-cols-3">
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-black text-slate-950">Business profile</h2><p class="text-xs text-slate-400">Account and operating details</p></div>
                <div class="grid gap-5 p-5 sm:grid-cols-2">
                    @foreach([['Business name',$business->business_name ?: $business->name],['Account email',$business->email],['Industry',$business->business_industry ?: 'Not provided'],['Website',$business->business_website ?: 'Not provided'],['Phone',$business->business_phone ?: 'Not provided'],['Email verified',$business->email_verified_at ? 'Yes · '.$business->email_verified_at->format('d M Y') : 'No']] as $item)
                    <div><span class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $item[0] }}</span><p class="mt-1 text-sm font-bold text-slate-800">{{ $item[1] }}</p></div>
                    @endforeach
                </div>
            </section>
            <section class="rounded-2xl bg-slate-950 p-5 text-white shadow-xl shadow-slate-950/10">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-violet-300">Financial position</p>
                <div class="mt-4 space-y-3">
                    @foreach([['Gross sales',$stats['gross_sales']],['Commissions',$stats['commissions']],['Net revenue',$stats['net_revenue']],['Refunded',$stats['refunded']],['Payouts requested',$stats['payouts_requested']],['Payouts paid',$stats['payouts_paid']]] as $item)
                    <div class="flex items-center justify-between border-b border-white/10 pb-3"><span class="text-xs text-slate-400">{{ $item[0] }}</span><strong class="text-sm">₦{{ number_format($item[1],2) }}</strong></div>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><div><h2 class="font-black text-slate-950">Programs & performance</h2><p class="text-xs text-slate-400">{{ $programs->count() }} programs</p></div></div>
            <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3 text-left">Program</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3">Partners</th><th class="px-5 py-3">Products</th><th class="px-5 py-3">Orders</th><th class="px-5 py-3">Commission rules</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($programs as $program)<tr class="transition hover:bg-violet-50/30"><td class="px-5 py-4"><strong class="font-black text-slate-900">{{ $program->name }}</strong><small class="block text-xs text-slate-400">{{ $program->slug }}</small></td><td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black">{{ ucfirst($program->status) }}</span></td><td class="px-5 py-4 text-center font-bold">{{ $program->partners_count }}</td><td class="px-5 py-4 text-center font-bold">{{ $program->products_count }}</td><td class="px-5 py-4 text-center font-bold">{{ $program->orders_count }}</td><td class="px-5 py-4 text-center font-bold">{{ $program->commissionRules->count() }}</td></tr>@empty<tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">No programs.</td></tr>@endforelse</tbody></table></div>
        </section>

        <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-black text-slate-950">Partners under this business</h2><p class="text-xs text-slate-400">Showing up to 100 partners</p></div>
            <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3 text-left">Partner</th><th class="px-5 py-3 text-left">Program</th><th class="px-5 py-3 text-left">Recruited by</th><th class="px-5 py-3">Recruits</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($partners as $partner)<tr class="transition hover:bg-violet-50/30"><td class="px-5 py-4"><strong class="font-black text-slate-900">{{ $partner->user->name }}</strong><small class="block text-xs text-slate-400">{{ $partner->user->email }}</small></td><td class="px-5 py-4 font-semibold">{{ $partner->program->name }}</td><td class="px-5 py-4 text-slate-500">{{ $partner->parentPartner?->user?->name ?? 'Direct / none' }}</td><td class="px-5 py-4 text-center font-bold">{{ $partner->children_count }}</td><td class="px-5 py-4"><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black text-emerald-700">{{ ucfirst($partner->status ?? 'active') }}</span></td><td class="px-5 py-4 text-right"><a href="{{ route('admin.analytics.partner', [$business, $partner]) }}" class="rounded-xl bg-violet-600 px-3 py-2 text-xs font-black text-white transition hover:bg-violet-700">Full details →</a></td></tr>@empty<tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">No partners.</td></tr>@endforelse</tbody></table></div>
        </section>

        <div class="mb-6 grid gap-5 lg:grid-cols-7">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-4"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-black">Customers</h2><p class="text-xs text-slate-400">Showing up to 30</p></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3 text-left">Customer</th><th class="px-5 py-3 text-left">Email</th><th class="px-5 py-3 text-left">Joined</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($customers->take(30) as $customer)<tr><td class="px-5 py-3 font-bold">{{ $customer->name }}</td><td class="px-5 py-3 text-slate-500">{{ $customer->email }}</td><td class="px-5 py-3 text-slate-500">{{ $customer->created_at?->format('d M Y') }}</td></tr>@empty<tr><td colspan="3" class="px-5 py-8 text-center text-slate-400">No registered customers yet.</td></tr>@endforelse</tbody></table></div></section>
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-3"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-black">Recent activity</h2></div><div class="divide-y divide-slate-100">@forelse($recentActivity as $activity)<div class="px-5 py-4"><strong class="text-sm">{{ $activity['label'] }}</strong><div class="mt-1 text-xs text-slate-500">{{ $activity['description'] }}</div><small class="mt-1 block text-[10px] text-slate-400">{{ $activity['date']?->format('d M Y H:i') }}</small></div>@empty<div class="p-5 text-sm text-slate-400">No activity.</div>@endforelse</div></section>
        </div>

        <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-black">Recent sales / orders</h2><p class="text-xs text-slate-400">Showing up to 100</p></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3 text-left">Order</th><th class="px-5 py-3 text-left">Customer</th><th class="px-5 py-3 text-left">Partner</th><th class="px-5 py-3 text-left">Program</th><th class="px-5 py-3 text-left">Amount</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-left">Date</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($orders as $order)<tr class="hover:bg-slate-50"><td class="px-5 py-3 font-bold">{{ $order->order_number }}</td><td class="px-5 py-3">{{ $order->customer?->name ?? $order->customer_name ?? $order->customer_email }}</td><td class="px-5 py-3">{{ $order->partner?->user?->name ?? 'Direct' }}</td><td class="px-5 py-3">{{ $order->program?->name }}</td><td class="px-5 py-3 font-black">₦{{ number_format($order->total,2) }}</td><td class="px-5 py-3">{{ ucfirst($order->status) }}</td><td class="px-5 py-3 text-slate-500">{{ $order->created_at?->format('d M Y H:i') }}</td></tr>@empty<tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">No orders.</td></tr>@endforelse</tbody></table></div></section>

        <div class="grid gap-5 lg:grid-cols-7"><section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-4"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-black">Commission trail</h2><p class="text-xs text-slate-400">Showing up to 100</p></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3 text-left">Recipient</th><th class="px-5 py-3 text-left">Program</th><th class="px-5 py-3">Level</th><th class="px-5 py-3 text-left">Amount</th><th class="px-5 py-3 text-left">Status</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($commissions as $commission)<tr><td class="px-5 py-3 font-bold">{{ $commission->partner?->user?->name ?? '—' }}</td><td class="px-5 py-3">{{ $commission->program?->name }}</td><td class="px-5 py-3 text-center">{{ $commission->level ?? $commission->rule?->level ?? '—' }}</td><td class="px-5 py-3 font-black">₦{{ number_format((float)$commission->commission_amount,2) }}</td><td class="px-5 py-3">{{ ucfirst($commission->status) }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No commissions.</td></tr>@endforelse</tbody></table></div></section><section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-3"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-black">Business payouts</h2></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3 text-left">Reference</th><th class="px-5 py-3 text-left">Amount</th><th class="px-5 py-3 text-left">Status</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($payouts as $payout)<tr><td class="px-5 py-3 font-bold">{{ $payout->reference ?: '—' }}</td><td class="px-5 py-3 font-black">₦{{ number_format($payout->amount,2) }}</td><td class="px-5 py-3">{{ ucfirst($payout->status) }}</td></tr>@empty<tr><td colspan="3" class="px-5 py-8 text-center text-slate-400">No payouts.</td></tr>@endforelse</tbody></table></div></section></div>
    </div>
</div>
@endsection
