<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commissions Administration</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">

<div class="max-w-7xl mx-auto px-6 py-10">

    <div class="mb-8 flex justify-between items-center">
        <h1 class="text-4xl font-bold text-gray-900">Commissions</h1>
        <a href="{{ route('admin') }}" class="text-blue-600 hover:underline">← Back to Admin</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-gray-600 text-sm font-medium">Available</p>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['count_available'] }}</p>
            <p class="text-lg font-mono text-gray-700 mt-1">₦{{ number_format($stats['total_available'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-gray-600 text-sm font-medium">Payable</p>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['count_payable'] }}</p>
            <p class="text-lg font-mono text-gray-700 mt-1">₦{{ number_format($stats['total_payable'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-gray-600 text-sm font-medium">Paid</p>
            <p class="text-3xl font-bold text-green-600">-</p>
            <p class="text-lg font-mono text-gray-700 mt-1">₦{{ number_format($stats['total_paid'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-gray-600 text-sm font-medium">Reversed</p>
            <p class="text-3xl font-bold text-red-600">-</p>
            <p class="text-lg font-mono text-gray-700 mt-1">₦{{ number_format($stats['total_reversed'], 2) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.commissions.index') }}" class="grid md:grid-cols-5 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') == $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                <select name="level" class="w-full border rounded px-3 py-2">
                    <option value="">All Levels</option>
                    <option value="1" @selected(request('level') == '1')>Level 1</option>
                    <option value="2" @selected(request('level') == '2')>Level 2</option>
                    <option value="3" @selected(request('level') == '3')>Level 3</option>
                    <option value="4" @selected(request('level') == '4')>Level 4</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Program</label>
                <select name="program_id" class="w-full border rounded px-3 py-2">
                    <option value="">All Programs</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Min Amount</label>
                <input 
                    type="number"
                    name="min_amount"
                    value="{{ request('min_amount') }}"
                    step="0.01"
                    placeholder="0"
                    class="w-full border rounded px-3 py-2"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                <button type="submit" class="w-full bg-black text-white py-2 rounded hover:bg-gray-900">
                    Filter
                </button>
            </div>

        </form>
    </div>

    <!-- Commissions Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold">Partner</th>
                    <th class="text-left px-6 py-4 font-semibold">Order</th>
                    <th class="text-center px-6 py-4 font-semibold">Level</th>
                    <th class="text-left px-6 py-4 font-semibold">Program</th>
                    <th class="text-right px-6 py-4 font-semibold">Amount</th>
                    <th class="text-left px-6 py-4 font-semibold">Status</th>
                    <th class="text-left px-6 py-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($commissions as $commission)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium">{{ $commission->partner->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $commission->partner->partner_code }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.orders.show', ['order' => $commission->order_id]) }}" class="text-blue-600 hover:underline font-mono">
                                {{ $commission->order->order_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 bg-gray-100 rounded">
                                {{ $commission->level }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            {{ $commission->order->program->name }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <strong class="text-lg">₦{{ number_format($commission->commission_amount, 2) }}</strong>
                            <p class="text-xs text-gray-500">{{ $commission->rate }}% of ₦{{ number_format($commission->base_amount, 2) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                @if($commission->status === 'available') bg-yellow-100 text-yellow-800
                                @elseif($commission->status === 'approved') bg-blue-100 text-blue-800
                                @elseif($commission->status === 'payable') bg-purple-100 text-purple-800
                                @elseif($commission->status === 'paid') bg-green-100 text-green-800
                                @elseif($commission->status === 'reversed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif
                            ">
                                {{ ucfirst($commission->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.commissions.show', ['commission' => $commission->id]) }}" class="text-blue-600 hover:underline text-sm">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No commissions found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $commissions->links() }}
    </div>

</div>

</body>
</html>
