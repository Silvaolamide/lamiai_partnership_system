<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-violet-600">Super Admin Analytics</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h2>
                <p class="text-sm text-gray-500 mt-1">Detailed platform-wide view of the same major business activity available to businesses.</p>
            </div>
            <a href="{{ route('admin') }}" class="rounded-xl border px-4 py-2 text-sm font-bold">← Dashboard</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach([
                    ['Gross sales', $stats['gross_sales']],
                    ['Net revenue', $stats['net_revenue']],
                    ['Commissions', $stats['commission_total']],
                    ['Completed sales', $stats['orders']],
                ] as $card)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ $card[0] }}</p>
                        <p class="mt-2 text-2xl font-black">{{ is_numeric($card[1]) && $card[0] !== 'Completed sales' ? '₦'.number_format($card[1], 2) : number_format($card[1]) }}</p>
                    </div>
                @endforeach
            </div>

            @if(in_array($metric, ['sales', 'orders'], true))
                <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
                    <div class="p-5 border-b">
                        <h3 class="font-black text-lg">{{ $metric === 'sales' ? 'Sales, commissions and business net' : 'Completed order detail' }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Every sale shows the customer, referring partner, commission allocations and the amount retained by the business.</p>
                    </div>
                    <div class="divide-y">
                        @forelse($orders as $order)
                            @php($commissionTotal = (float) $order->commissions->whereNotIn('status', ['reversed','cancelled'])->sum('commission_amount'))
                            <div class="p-6">
                                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                    <div>
                                        <p class="font-mono text-xs text-gray-500">{{ $order->order_number }}</p>
                                        <h4 class="mt-1 font-black">{{ $order->customer?->name ?? $order->customer_name ?? 'Guest customer' }}</h4>
                                        <p class="text-sm text-gray-500">{{ $order->customer?->email ?? $order->customer_email ?? '—' }}</p>
                                        <p class="text-xs text-gray-500 mt-2">Partner: <b>{{ $order->partner?->user?->name ?? 'Direct / none' }}</b> · Program: {{ $order->program?->name ?? '—' }}</p>
                                    </div>
                                    <div class="text-left md:text-right">
                                        <p class="text-xl font-black">{{ $order->currency }} {{ number_format((float)$order->total, 2) }}</p>
                                        <p class="text-xs text-gray-500">{{ ($order->paid_at ?? $order->created_at)?->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="grid md:grid-cols-3 gap-3 mt-5">
                                    <div class="rounded-xl bg-gray-50 p-4"><p class="text-xs text-gray-500">Customer paid</p><p class="font-black mt-1">₦{{ number_format((float)$order->total, 2) }}</p></div>
                                    <div class="rounded-xl bg-amber-50 p-4"><p class="text-xs text-amber-700">All commissions</p><p class="font-black text-amber-950 mt-1">₦{{ number_format($commissionTotal, 2) }}</p></div>
                                    <div class="rounded-xl bg-blue-50 p-4"><p class="text-xs text-blue-700">Business net</p><p class="font-black text-blue-950 mt-1">₦{{ number_format(max(0,(float)$order->total-$commissionTotal), 2) }}</p></div>
                                </div>
                                <div class="mt-5 overflow-x-auto">
                                    <table class="w-full min-w-[720px] text-sm"><thead><tr class="border-b text-left text-xs uppercase tracking-wider text-gray-400"><th class="py-2">Who</th><th>Level</th><th>Why</th><th>Base</th><th>Rate</th><th>Amount</th></tr></thead><tbody>
                                        @forelse($order->commissions->whereNotIn('status',['reversed','cancelled']) as $commission)
                                            <tr class="border-b last:border-0"><td class="py-3 font-semibold">{{ $commission->partner?->user?->name ?? 'Partner' }}</td><td>Level {{ $commission->level }}</td><td>{{ $commission->rule?->product_id ? 'Product-specific rule' : 'Program level '.$commission->level.' rule' }}</td><td>₦{{ number_format((float)$commission->base_amount,2) }}</td><td>{{ $commission->commission_type === 'percentage' ? number_format((float)$commission->rate,2).'%' : 'Fixed' }}</td><td class="font-black">₦{{ number_format((float)$commission->commission_amount,2) }}</td></tr>
                                        @empty<tr><td colspan="6" class="py-4 text-gray-500">No commission allocated.</td></tr>@endforelse
                                    </tbody></table>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center text-gray-500">No completed sales yet.</div>
                        @endforelse
                    </div>
                </div>
            @elseif($metric === 'partners')
                <div class="grid lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden"><div class="p-5 border-b"><h3 class="font-black">Partners across the platform</h3></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50"><tr><th class="text-left p-4">Partner</th><th class="text-left p-4">Program</th><th class="text-left p-4">Status</th><th class="text-left p-4">Recruited</th><th class="text-left p-4">Earnings</th></tr></thead><tbody class="divide-y">@forelse($partnersList as $partner)<tr><td class="p-4 font-semibold">{{ $partner->user?->name ?? '—' }}</td><td class="p-4">{{ $partner->program?->name ?? '—' }}</td><td class="p-4">{{ ucfirst($partner->status) }}</td><td class="p-4">{{ $partner->child_partners_count }}</td><td class="p-4 font-black">₦{{ number_format((float)($partner->earnings ?? 0),2) }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-gray-500">No partners yet.</td></tr>@endforelse</tbody></table></div></div>
                    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden"><div class="p-5 border-b"><h3 class="font-black">Partner recruitment activity</h3><p class="text-sm text-gray-500">Who recruited whom and when.</p></div><div class="divide-y">@forelse($recruits as $recruit)<div class="p-4"><p class="font-bold">{{ $recruit->parentPartner?->user?->name ?? 'Partner' }} recruited {{ $recruit->user?->name ?? 'new partner' }}</p><p class="text-xs text-gray-500 mt-1">{{ $recruit->program?->name ?? '—' }} · {{ $recruit->created_at?->diffForHumans() }}</p></div>@empty<div class="p-8 text-center text-gray-500">No recruitment activity yet.</div>@endforelse</div></div>
                </div>
            @elseif($metric === 'programs')
                <div class="bg-white rounded-2xl border shadow-sm overflow-hidden"><div class="p-5 border-b"><h3 class="font-black">Program performance</h3></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50"><tr><th class="text-left p-4">Program</th><th class="text-left p-4">Status</th><th class="text-left p-4">Partners</th><th class="text-left p-4">Products</th><th class="text-left p-4">Orders</th><th class="text-left p-4">Sales</th></tr></thead><tbody class="divide-y">@forelse($programList as $program)<tr><td class="p-4 font-bold">{{ $program->name }}</td><td class="p-4">{{ ucfirst($program->status) }}</td><td class="p-4">{{ $program->partners_count }}</td><td class="p-4">{{ $program->products_count }}</td><td class="p-4">{{ $program->orders_count }}</td><td class="p-4 font-black">₦{{ number_format((float)($program->sales ?? 0),2) }}</td></tr>@empty<tr><td colspan="6" class="p-8 text-center text-gray-500">No programs yet.</td></tr>@endforelse</tbody></table></div></div>
            @elseif($metric === 'products')
                <div class="bg-white rounded-2xl border shadow-sm overflow-hidden"><div class="p-5 border-b"><h3 class="font-black">Product performance</h3><p class="text-sm text-gray-500">Units, orders and revenue generated by every product.</p></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50"><tr><th class="text-left p-4">Product</th><th class="text-left p-4">Owner</th><th class="text-left p-4">Units</th><th class="text-left p-4">Orders</th><th class="text-left p-4">Sales</th></tr></thead><tbody class="divide-y">@forelse($productList as $row)<tr><td class="p-4 font-bold">{{ $row['product']->name }}</td><td class="p-4">{{ $row['product']->owner?->name ?? '—' }}</td><td class="p-4">{{ $row['units'] }}</td><td class="p-4">{{ $row['orders'] }}</td><td class="p-4 font-black">₦{{ number_format($row['sales'],2) }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-gray-500">No products yet.</td></tr>@endforelse</tbody></table></div></div>
            @elseif(in_array($metric, ['commissions','payable','paid-commissions'], true))
                <div class="grid lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl border shadow-sm p-5"><h3 class="font-black">By commission level</h3><div class="mt-4 space-y-3">@forelse($byLevel as $level => $amount)<div class="flex justify-between border-b pb-3"><span>Level {{ $level }}</span><b>₦{{ number_format($amount,2) }}</b></div>@empty<p class="text-sm text-gray-500">No commissions.</p>@endforelse</div></div>
                    <div class="bg-white rounded-2xl border shadow-sm p-5"><h3 class="font-black">Top recipients</h3><div class="mt-4 space-y-3">@forelse($byPartner->take(10) as $row)<div class="flex justify-between border-b pb-3 gap-3"><span>{{ $row['partner']?->user?->name ?? 'Partner' }} <small class="block text-gray-400">{{ $row['count'] }} allocations</small></span><b>₦{{ number_format($row['amount'],2) }}</b></div>@empty<p class="text-sm text-gray-500">No commissions.</p>@endforelse</div></div>
                    <div class="bg-white rounded-2xl border shadow-sm p-5"><h3 class="font-black">Commission totals</h3><p class="mt-4 text-sm text-gray-500">This view excludes reversed and cancelled commissions.</p><p class="mt-2 text-3xl font-black">₦{{ number_format($commissionList->sum('commission_amount'),2) }}</p><p class="text-xs text-gray-500 mt-1">{{ $commissionList->count() }} commission records</p></div>
                </div>
                <div class="bg-white rounded-2xl border shadow-sm overflow-hidden"><div class="p-5 border-b"><h3 class="font-black">Commission allocation detail</h3></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50"><tr><th class="text-left p-4">Recipient</th><th class="text-left p-4">Order</th><th class="text-left p-4">Level</th><th class="text-left p-4">Why</th><th class="text-left p-4">Status</th><th class="text-left p-4">Amount</th></tr></thead><tbody class="divide-y">@forelse($commissionList as $commission)<tr><td class="p-4 font-semibold">{{ $commission->partner?->user?->name ?? 'Partner' }}</td><td class="p-4">{{ $commission->order?->order_number ?? '—' }}</td><td class="p-4">Level {{ $commission->level }}</td><td class="p-4">{{ $commission->rule?->product_id ? 'Product-specific rule' : 'Program level '.$commission->level.' rule' }}</td><td class="p-4">{{ ucfirst($commission->status) }}</td><td class="p-4 font-black">₦{{ number_format((float)$commission->commission_amount,2) }}</td></tr>@empty<tr><td colspan="6" class="p-8 text-center text-gray-500">No commission records.</td></tr>@endforelse</tbody></table></div></div>
            @endif
        </div>
    </div>
</x-app-layout>
