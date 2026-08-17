<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payout {{ $payout->reference }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
<div class="max-w-5xl mx-auto px-6 py-10">
    <a href="{{ route('admin.payouts.index') }}" class="text-sm text-slate-500">← Payouts</a>
    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mt-3 mb-8">
        <div><h1 class="text-3xl font-bold">{{ $payout->reference }}</h1><p class="text-slate-500 mt-1">{{ $payout->partner->user->name }} · {{ $payout->program->name }}</p></div>
        <div class="text-2xl font-bold">₦{{ number_format($payout->amount, 2) }}</div>
    </div>

    @if(session('success'))<div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-700">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4 text-red-700">{{ session('error') }}</div>@endif

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-8">
        <div class="grid md:grid-cols-3 gap-6">
            <div><p class="text-sm text-slate-500">Status</p><p class="font-semibold capitalize mt-1">{{ $payout->status }}</p></div>
            <div><p class="text-sm text-slate-500">Method</p><p class="font-semibold mt-1">{{ $payout->method }}</p></div>
            <div><p class="text-sm text-slate-500">Requested</p><p class="font-semibold mt-1">{{ $payout->requested_at?->format('d M Y H:i') }}</p></div>
        </div>
        @if($payout->notes)<div class="mt-6 p-4 rounded-xl bg-slate-50"><p class="text-sm text-slate-500">Payment details / notes</p><p class="mt-1">{{ $payout->notes }}</p></div>@endif
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
        <div class="p-5 border-b"><h2 class="font-semibold">Included commissions</h2></div>
        <table class="w-full text-sm">
            <thead><tr class="border-b bg-slate-50 text-left text-slate-500"><th class="p-4">Order</th><th class="p-4">Level</th><th class="p-4">Amount</th><th class="p-4">Status</th></tr></thead>
            <tbody>
            @foreach($payout->commissions as $commission)
                <tr class="border-b last:border-0"><td class="p-4">{{ $commission->order->order_number }}</td><td class="p-4">Level {{ $commission->level }}</td><td class="p-4">₦{{ number_format($commission->commission_amount, 2) }}</td><td class="p-4 capitalize">{{ $commission->status }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap gap-3">
        @if($payout->status === 'pending')
            <form method="POST" action="{{ route('admin.payouts.approve', $payout) }}">@csrf @method('PATCH')<button class="rounded-xl bg-slate-900 text-white px-5 py-3 font-semibold">Approve Payout</button></form>
            <form method="POST" action="{{ route('admin.payouts.reject', $payout) }}">@csrf @method('PATCH')<button class="rounded-xl bg-red-600 text-white px-5 py-3 font-semibold">Reject</button></form>
        @elseif($payout->status === 'approved')
            <form method="POST" action="{{ route('admin.payouts.process', $payout) }}">@csrf @method('PATCH')<button class="rounded-xl bg-emerald-600 text-white px-5 py-3 font-semibold">Mark as Paid</button></form>
            <form method="POST" action="{{ route('admin.payouts.reject', $payout) }}">@csrf @method('PATCH')<button class="rounded-xl bg-red-600 text-white px-5 py-3 font-semibold">Reject</button></form>
        @endif
    </div>
</div>
</body>
</html>
