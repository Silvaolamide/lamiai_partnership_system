<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4"><div><h2 class="font-semibold text-xl text-gray-800 leading-tight">Business Payout {{ $businessPayout->reference }}</h2><p class="text-sm text-gray-500 mt-1">{{ $businessPayout->business?->business_name ?? $businessPayout->business?->name }}</p></div><a href="{{ route('admin.business-payouts.index') }}" class="text-sm font-bold text-violet-600">Back to payouts</a></div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))<div class="rounded-xl bg-emerald-50 text-emerald-700 px-4 py-3">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="rounded-xl bg-rose-50 text-rose-700 px-4 py-3">{{ session('error') }}</div>@endif

            <div class="grid gap-4 md:grid-cols-4">
                <div class="bg-white rounded-2xl border p-5"><p class="text-xs uppercase font-bold text-gray-400">Business</p><p class="mt-2 font-black">{{ $businessPayout->business?->business_name ?? $businessPayout->business?->name }}</p></div>
                <div class="bg-white rounded-2xl border p-5"><p class="text-xs uppercase font-bold text-gray-400">Amount</p><p class="mt-2 text-xl font-black">{{ $businessPayout->currency }} {{ number_format((float)$businessPayout->amount,2) }}</p></div>
                <div class="bg-white rounded-2xl border p-5"><p class="text-xs uppercase font-bold text-gray-400">Method</p><p class="mt-2 font-black">{{ $businessPayout->method }}</p></div>
                <div class="bg-white rounded-2xl border p-5"><p class="text-xs uppercase font-bold text-gray-400">Status</p><p class="mt-2 font-black uppercase">{{ $businessPayout->status }}</p></div>
            </div>

            <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
                <div class="p-6 border-b"><h3 class="text-lg font-black">Included sales</h3></div>
                <div class="divide-y">
                    @foreach($businessPayout->orders as $order)
                        <div class="flex flex-wrap items-center justify-between gap-4 p-5"><div><p class="font-bold">{{ $order->order_number }}</p><p class="text-xs text-gray-400">{{ $order->program?->name }} · {{ $order->paid_at?->format('M j, Y') }} · {{ $order->partner?->user?->name ?? 'Direct sale' }}</p></div><p class="font-black">{{ $order->currency }} {{ number_format((float)$order->total,2) }}</p></div>
                    @endforeach
                </div>
            </div>

            @if($businessPayout->status === 'requested')
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('admin.business-payouts.approve', $businessPayout) }}">@csrf @method('PATCH')<button class="rounded-xl bg-emerald-600 px-5 py-3 text-white font-bold">Approve payout</button></form>
                    <form method="POST" action="{{ route('admin.business-payouts.reject', $businessPayout) }}">@csrf @method('PATCH')<button class="rounded-xl bg-rose-600 px-5 py-3 text-white font-bold">Reject payout</button></form>
                </div>
            @elseif(in_array($businessPayout->status, ['approved', 'processing'], true))
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('admin.business-payouts.process', $businessPayout) }}">@csrf @method('PATCH')<button class="rounded-xl bg-violet-600 px-5 py-3 text-white font-bold">Mark as processed</button></form>
                    <form method="POST" action="{{ route('admin.business-payouts.reject', $businessPayout) }}">@csrf @method('PATCH')<button class="rounded-xl bg-rose-600 px-5 py-3 text-white font-bold">Reject payout</button></form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
