<x-app-layout>
    <x-slot name="header">
        <div><h2 class="font-semibold text-xl text-gray-800 leading-tight">Business Payouts</h2><p class="text-sm text-gray-500 mt-1">Review and process business sale proceeds.</p></div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))<div class="rounded-xl bg-emerald-50 text-emerald-700 px-4 py-3">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="rounded-xl bg-rose-50 text-rose-700 px-4 py-3">{{ session('error') }}</div>@endif

            <div class="grid md:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl border p-5"><p class="text-sm text-gray-500">Pending amount</p><p class="mt-2 text-2xl font-black">₦{{ number_format((float)$stats['requested'],2) }}</p></div>
                <div class="bg-white rounded-2xl border p-5"><p class="text-sm text-gray-500">Paid to businesses</p><p class="mt-2 text-2xl font-black">₦{{ number_format((float)$stats['paid'],2) }}</p></div>
                <div class="bg-white rounded-2xl border p-5"><p class="text-sm text-gray-500">Pending requests</p><p class="mt-2 text-2xl font-black">{{ number_format($stats['count_pending']) }}</p></div>
            </div>

            <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left"><tr><th class="p-4">Business</th><th class="p-4">Reference</th><th class="p-4">Amount</th><th class="p-4">Sales</th><th class="p-4">Status</th><th class="p-4"></th></tr></thead>
                        <tbody class="divide-y">
                        @forelse($payouts as $payout)
                            <tr>
                                <td class="p-4 font-bold">{{ $payout->business?->business_name ?? $payout->business?->name }}</td>
                                <td class="p-4">{{ $payout->reference }}</td>
                                <td class="p-4 font-black">{{ $payout->currency }} {{ number_format((float)$payout->amount,2) }}</td>
                                <td class="p-4">{{ $payout->orders_count }}</td>
                                <td class="p-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase">{{ $payout->status }}</span></td>
                                <td class="p-4"><a class="font-bold text-violet-600" href="{{ route('admin.business-payouts.show', $payout) }}">Review →</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-10 text-center text-gray-400">No business payout requests.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $payouts->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
