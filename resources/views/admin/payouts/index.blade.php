<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Payouts</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
<div class="max-w-7xl mx-auto px-6 py-10">
    <div class="mb-8">
        <a href="{{ url('/admin') }}" class="text-sm text-slate-500">← Admin</a>
        <h1 class="text-3xl font-bold mt-2">Payout Management</h1>
        <p class="text-slate-500 mt-1">Review and settle partner payout requests.</p>
    </div>

    @if(session('success'))<div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-700">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4 text-red-700">{{ session('error') }}</div>@endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach(['pending'=>'Pending','approved'=>'Approved','paid'=>'Paid','rejected'=>'Rejected'] as $key => $label)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="text-2xl font-bold mt-1">₦{{ number_format($stats[$key], 2) }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b flex flex-wrap gap-2">
            @foreach([''=>'All','pending'=>'Pending','approved'=>'Approved','paid'=>'Paid','rejected'=>'Rejected'] as $value => $label)
                <a href="{{ route('admin.payouts.index', $value ? ['status'=>$value] : []) }}" class="px-4 py-2 rounded-lg {{ request('status') === $value ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b bg-slate-50 text-left text-slate-500"><th class="p-4">Reference</th><th class="p-4">Partner</th><th class="p-4">Program</th><th class="p-4">Amount</th><th class="p-4">Status</th><th class="p-4"></th></tr></thead>
                <tbody>
                @forelse($payouts as $payout)
                    <tr class="border-b last:border-0">
                        <td class="p-4 font-medium">{{ $payout->reference }}</td>
                        <td class="p-4">{{ $payout->partner->user->name }}<div class="text-xs text-slate-500">{{ $payout->partner->user->email }}</div></td>
                        <td class="p-4">{{ $payout->program->name }}</td>
                        <td class="p-4 font-semibold">₦{{ number_format($payout->amount, 2) }}</td>
                        <td class="p-4 capitalize">{{ $payout->status }}</td>
                        <td class="p-4"><a href="{{ route('admin.payouts.show', $payout) }}" class="text-blue-600 font-medium">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-10 text-center text-slate-500">No payout requests found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-5">{{ $payouts->links() }}</div>
    </div>
</div>
</body>
</html>
