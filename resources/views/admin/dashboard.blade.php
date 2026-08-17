<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Super Admin Dashboard</h2>
                <p class="text-sm text-gray-500 mt-1">Complete overview of your partnership business.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.businesses.index') }}" class="rounded-xl border px-4 py-2 text-sm font-bold">Businesses</a>
                <a href="{{ route('admin.settings') }}" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-bold text-white">Approval Settings</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ([
                    ['Partners', $stats['partners'], $stats['active_partners'].' active'],
                    ['Programs', $stats['programs'], $stats['active_programs'].' active'],
                    ['Products', $stats['products'], 'Products in catalog'],
                    ['Orders', $stats['orders'], $stats['paid_orders'].' paid'],
                    ['Total Sales', number_format($stats['sales'], 2), 'Paid orders'],
                    ['Commissions', number_format($stats['commissions'], 2), 'Generated'],
                    ['Payable', number_format($stats['payable_commissions'], 2), 'Awaiting payout'],
                    ['Paid Commissions', number_format($stats['paid_commissions'], 2), 'Already paid'],
                ] as $card)
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-5 border border-gray-100"><p class="text-sm font-medium text-gray-500">{{ $card[0] }}</p><p class="text-2xl font-bold text-gray-900 mt-2">{{ $card[1] }}</p><p class="text-xs text-gray-500 mt-1">{{ $card[2] }}</p></div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-5 border-b flex items-center justify-between"><div><h3 class="font-semibold text-gray-900">Recent Orders</h3><p class="text-sm text-gray-500">Latest customer transactions</p></div><a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-indigo-600">View all</a></div>
                    <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-gray-500"><tr><th class="text-left p-4">Order</th><th class="text-left p-4">Customer</th><th class="text-left p-4">Partner</th><th class="text-left p-4">Amount</th><th class="text-left p-4">Status</th></tr></thead><tbody class="divide-y">
                        @forelse ($recentOrders as $order)<tr><td class="p-4"><a class="font-medium text-indigo-600" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td><td class="p-4">{{ $order->customer?->name ?? '—' }}</td><td class="p-4">{{ $order->partner?->user?->name ?? '—' }}</td><td class="p-4">{{ $order->currency }} {{ number_format($order->total, 2) }}</td><td class="p-4"><span class="px-2 py-1 rounded-full text-xs bg-gray-100">{{ ucfirst($order->status) }}</span></td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-gray-500">No orders yet.</td></tr>@endforelse
                    </tbody></table></div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100"><div class="p-5 border-b"><h3 class="font-semibold">Top Partners</h3><p class="text-sm text-gray-500">By commission earnings</p></div><div class="divide-y">
                    @forelse ($topPartners as $partner)<div class="p-4 flex justify-between gap-3"><div><p class="font-medium">{{ $partner->user?->name ?? 'Unknown' }}</p><p class="text-xs text-gray-500">{{ $partner->program?->name ?? '—' }}</p></div><div class="font-semibold">{{ number_format($partner->earnings ?? 0, 2) }}</div></div>@empty<div class="p-6 text-center text-gray-500">No partners yet.</div>@endforelse
                </div><div class="p-4 border-t"><a href="{{ route('admin.partners.index') }}" class="text-sm text-indigo-600">Manage partners →</a></div></div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100"><div class="p-5 border-b flex items-center justify-between"><div><h3 class="font-semibold">Programs</h3><p class="text-sm text-gray-500">Performance across partnership programs</p></div><a href="{{ route('admin.programs.index') }}" class="text-sm text-indigo-600">Manage programs</a></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50"><tr><th class="text-left p-4">Program</th><th class="text-left p-4">Status</th><th class="text-left p-4">Partners</th><th class="text-left p-4">Products</th><th class="text-left p-4">Orders</th><th class="text-left p-4">Sales</th></tr></thead><tbody class="divide-y">
                @forelse ($programs as $program)<tr><td class="p-4 font-medium">{{ $program->name }}</td><td class="p-4">{{ ucfirst($program->status) }}</td><td class="p-4">{{ $program->partners_count }}</td><td class="p-4">{{ $program->products_count }}</td><td class="p-4">{{ $program->orders_count }}</td><td class="p-4">{{ number_format($program->sales ?? 0, 2) }}</td></tr>@empty<tr><td colspan="6" class="p-8 text-center text-gray-500">No programs yet.</td></tr>@endforelse
            </tbody></table></div></div>

            <div class="grid grid-cols-2 md:grid-cols-7 gap-3">
                <a class="bg-white rounded-xl border p-4 hover:bg-gray-50" href="{{ route('admin.partners.index') }}"><b>Partners</b><span class="block text-xs text-gray-500 mt-1">Approve & manage</span></a>
                <a class="bg-white rounded-xl border p-4 hover:bg-gray-50" href="{{ route('admin.businesses.index') }}"><b>Businesses</b><span class="block text-xs text-gray-500 mt-1">Approve businesses</span></a>
                <a class="bg-white rounded-xl border p-4 hover:bg-gray-50" href="{{ route('admin.programs.index') }}"><b>Programs</b><span class="block text-xs text-gray-500 mt-1">Configure programs</span></a>
                <a class="bg-white rounded-xl border p-4 hover:bg-gray-50" href="{{ route('admin.products.index') }}"><b>Products</b><span class="block text-xs text-gray-500 mt-1">Manage catalog</span></a>
                <a class="bg-white rounded-xl border p-4 hover:bg-gray-50" href="{{ route('admin.commissions.index') }}"><b>Commissions</b><span class="block text-xs text-gray-500 mt-1">Review earnings</span></a>
                <a class="bg-white rounded-xl border p-4 hover:bg-gray-50" href="{{ route('admin.payouts.index') }}"><b>Payouts</b><span class="block text-xs text-gray-500 mt-1">Process payments</span></a>
                <a class="bg-white rounded-xl border p-4 hover:bg-gray-50" href="{{ route('admin.settings') }}"><b>Settings</b><span class="block text-xs text-gray-500 mt-1">Approval rules</span></a>
            </div>
        </div>
    </div>
</x-app-layout>
