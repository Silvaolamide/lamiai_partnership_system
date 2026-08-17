<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payouts</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
<div class="max-w-6xl mx-auto px-6 py-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('partner.dashboard') }}" class="text-sm text-slate-500 hover:text-slate-900">← Partner Dashboard</a>
            <h1 class="text-3xl font-bold mt-2">Payouts</h1>
            <p class="text-slate-500 mt-1">Request payment for commissions that are ready to be paid.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4 text-red-700">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    @forelse($payableByProgram as $group)
        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-xl font-semibold">{{ $group['program']->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $group['commissions']->count() }} payable commission(s)</p>
                </div>
                <div class="text-2xl font-bold">₦{{ number_format($group['total'], 2) }}</div>
            </div>

            <form method="POST" action="{{ route('partner.payouts.store') }}">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-slate-500">
                                <th class="py-3 pr-4">Select</th>
                                <th class="py-3 pr-4">Order</th>
                                <th class="py-3 pr-4">Level</th>
                                <th class="py-3 pr-4">Amount</th>
                                <th class="py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($group['commissions'] as $commission)
                            <tr class="border-b last:border-0">
                                <td class="py-3 pr-4">
                                    <input type="checkbox" name="commission_ids[]" value="{{ $commission->id }}" class="commission-check rounded" data-amount="{{ $commission->commission_amount }}">
                                </td>
                                <td class="py-3 pr-4 font-medium">{{ $commission->order->order_number }}</td>
                                <td class="py-3 pr-4">Level {{ $commission->level }}</td>
                                <td class="py-3 pr-4">₦{{ number_format($commission->commission_amount, 2) }}</td>
                                <td class="py-3">{{ $commission->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Payout method</label>
                        <input name="method" required placeholder="e.g. Bank Transfer" class="w-full rounded-xl border-slate-300 px-4 py-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Notes / payment details</label>
                        <input name="notes" placeholder="e.g. Bank: GTBank, Account: 1234567890" class="w-full rounded-xl border-slate-300 px-4 py-3">
                    </div>
                </div>

                <div class="mt-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="font-semibold">Selected: ₦<span class="selected-total">0.00</span></div>
                    <button type="submit" class="rounded-xl bg-slate-900 text-white px-6 py-3 font-semibold hover:bg-slate-800">
                        Request Payout
                    </button>
                </div>
            </form>
        </section>
    @empty
        <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center">
            <h2 class="text-xl font-semibold">No payable commissions yet</h2>
            <p class="text-slate-500 mt-2">Once commissions are marked payable, you can request a payout here.</p>
        </div>
    @endforelse

    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-xl font-semibold mb-5">Payout History</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b text-left text-slate-500"><th class="py-3">Reference</th><th class="py-3">Program</th><th class="py-3">Amount</th><th class="py-3">Status</th><th class="py-3">Requested</th></tr></thead>
                <tbody>
                @forelse($payouts as $payout)
                    <tr class="border-b last:border-0">
                        <td class="py-3 font-medium">{{ $payout->reference }}</td>
                        <td class="py-3">{{ $payout->program->name }}</td>
                        <td class="py-3">₦{{ number_format($payout->amount, 2) }}</td>
                        <td class="py-3 capitalize">{{ $payout->status }}</td>
                        <td class="py-3">{{ $payout->requested_at?->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-slate-500">No payout requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $payouts->links() }}</div>
    </section>
</div>

<script>
document.querySelectorAll('.commission-check').forEach((checkbox) => {
    checkbox.addEventListener('change', () => {
        const form = checkbox.closest('form');
        const total = [...form.querySelectorAll('.commission-check:checked')]
            .reduce((sum, item) => sum + Number(item.dataset.amount || 0), 0);
        form.querySelector('.selected-total').textContent = total.toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    });
});
</script>
</body>
</html>
