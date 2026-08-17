<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Administration</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">

<div class="max-w-5xl mx-auto px-6 py-10">

    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">Order Details</h1>
            <p class="text-gray-600 mt-1">{{ $order->order_number }}</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="text-blue-600 hover:underline">← Back to Orders</a>
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

    <div class="grid lg:grid-cols-3 gap-6 mb-6">

        <!-- Order Information -->
        <div class="lg:col-span-2">

            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Order Summary</h2>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-600 text-sm">Order Number</p>
                        <p class="text-lg font-mono font-bold">{{ $order->order_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Order Date</p>
                        <p class="text-lg font-bold">{{ $order->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Status</p>
                        <p class="text-lg font-bold">
                            <span class="px-3 py-1 rounded-full text-sm
                                @if($order->status === 'paid') bg-green-100 text-green-800
                                @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->status === 'failed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif
                            ">
                                {{ ucfirst($order->status) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Payment Provider</p>
                        <p class="text-lg font-bold">{{ $order->payment_provider ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Customer Information</h2>

                <div class="space-y-3">
                    <div>
                        <p class="text-gray-600 text-sm">Name</p>
                        <p class="font-medium">{{ $order->customer->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Email</p>
                        <p class="font-medium">{{ $order->customer->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Partnership & Referral Information -->
            @if($order->partner)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-bold text-blue-900 mb-4">Referral Information</h2>

                    <div class="space-y-3">
                        <div>
                            <p class="text-blue-700 text-sm">Partnership Program</p>
                            <p class="text-blue-900 font-medium">{{ $order->program->name }}</p>
                        </div>
                        <div>
                            <p class="text-blue-700 text-sm">Partner Name</p>
                            <p class="text-blue-900 font-medium">{{ $order->partner->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-blue-700 text-sm">Partner Code</p>
                            <p class="text-blue-900 font-mono font-bold">{{ $order->partner->partner_code }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold mb-4">Products</h2>

                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium">{{ $item->product->name }}</p>
                                <p class="text-sm text-gray-600">Qty: {{ $item->quantity }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-mono">₦{{ number_format($item->unit_price, 2) }} each</p>
                                <p class="font-bold">₦{{ number_format($item->total, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 p-4 border-t">
                    <div class="flex justify-between mb-2">
                        <span>Subtotal:</span>
                        <span>₦{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between mb-2 text-green-600">
                            <span>Discount:</span>
                            <span>-₦{{ number_format($order->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total:</span>
                        <span>₦{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sidebar Actions -->
        <div>

            <!-- Order Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-bold mb-4">Actions</h2>

                <div class="space-y-3">
                    @if($order->status === 'pending')
                        <form action="{{ route('admin.orders.mark-paid', ['order' => $order->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition font-semibold">
                                Mark as Paid
                            </button>
                        </form>

                        <form action="{{ route('admin.orders.cancel', ['order' => $order->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition font-semibold" onclick="return confirm('Cancel this order?')">
                                Cancel Order
                            </button>
                        </form>
                    @elseif($order->status === 'paid')
                        <form action="{{ route('admin.orders.refund', ['order' => $order->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-orange-600 text-white py-2 rounded hover:bg-orange-700 transition font-semibold" onclick="return confirm('Refund this order and reverse commissions?')">
                                Refund Order
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Commissions -->
            @if($order->commissions->count() > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-bold mb-4">Commissions</h2>

                    <div class="space-y-3">
                        @foreach($order->commissions as $commission)
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-medium">Level {{ $commission->level }}</p>
                                        <p class="text-sm text-gray-600">
                                            {{ $commission->partner->user->name }}
                                        </p>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded
                                        @if($commission->status === 'available') bg-yellow-100 text-yellow-800
                                        @elseif($commission->status === 'paid') bg-green-100 text-green-800
                                        @elseif($commission->status === 'reversed') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif
                                    ">
                                        {{ ucfirst($commission->status) }}
                                    </span>
                                </div>
                                <p class="font-bold">₦{{ number_format($commission->commission_amount, 2) }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $commission->rate }}% of ₦{{ number_format($commission->base_amount, 2) }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 p-3 bg-gray-100 rounded-lg">
                        <p class="text-sm text-gray-600">Total Commissions</p>
                        <p class="text-lg font-bold">
                            ₦{{ number_format($order->commissions->sum('commission_amount'), 2) }}
                        </p>
                    </div>
                </div>
            @else
                @if($order->status === 'paid')
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <p class="text-gray-600 text-sm">No commissions generated yet.</p>
                    </div>
                @endif
            @endif

        </div>

    </div>

</div>

</body>
</html>
