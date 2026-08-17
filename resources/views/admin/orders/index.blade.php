<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Administration</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">

<div class="max-w-7xl mx-auto px-6 py-10">

    <div class="mb-8 flex justify-between items-center">
        <h1 class="text-4xl font-bold text-gray-900">Orders</h1>
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

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid md:grid-cols-4 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input 
                    type="text" 
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Order # or customer email"
                    class="w-full border rounded px-3 py-2"
                >
            </div>

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
                <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                <button type="submit" class="w-full bg-black text-white py-2 rounded hover:bg-gray-900">
                    Filter
                </button>
            </div>

        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-6 py-4 font-semibold">Order #</th>
                    <th class="text-left px-6 py-4 font-semibold">Customer</th>
                    <th class="text-left px-6 py-4 font-semibold">Program</th>
                    <th class="text-left px-6 py-4 font-semibold">Partner</th>
                    <th class="text-right px-6 py-4 font-semibold">Amount</th>
                    <th class="text-left px-6 py-4 font-semibold">Status</th>
                    <th class="text-left px-6 py-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.orders.show', ['order' => $order->id]) }}" class="text-blue-600 hover:underline font-mono">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium">{{ $order->customer->name }}</p>
                                <p class="text-sm text-gray-500">{{ $order->customer->email }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            {{ $order->program->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($order->partner)
                                <div>
                                    <p class="font-medium">{{ $order->partner->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $order->partner->partner_code }}</p>
                                </div>
                            @else
                                <p class="text-gray-500">Direct (no partner)</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <strong>₦{{ number_format($order->total, 2) }}</strong>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-sm font-semibold
                                @if($order->status === 'paid') bg-green-100 text-green-800
                                @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->status === 'failed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif
                            ">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.orders.show', ['order' => $order->id]) }}" class="text-blue-600 hover:underline">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No orders found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $orders->links() }}
    </div>

</div>

</body>
</html>
