<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission Details - Administration</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">

<div class="max-w-5xl mx-auto px-6 py-10">

    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">Commission Details</h1>
            <p class="text-gray-600 mt-1">Level {{ $commission->level }} Commission</p>
        </div>
        <a href="{{ route('admin.commissions.index') }}" class="text-blue-600 hover:underline">← Back to Commissions</a>
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

    <div class="grid lg:grid-cols-3 gap-6">

        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Commission Summary -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold mb-6">Commission Summary</h2>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600 text-sm">Commission Status</p>
                            <p class="text-lg font-bold mt-1">
                                <span class="px-3 py-1 rounded-full text-sm
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
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Commission Level</p>
                            <p class="text-lg font-bold">{{ $commission->level }}</p>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-gray-600 text-sm">Commission Amount</p>
                        <p class="text-3xl font-bold text-gray-900">₦{{ number_format($commission->commission_amount, 2) }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                        <div>
                            <p class="text-gray-600 text-xs">Base Amount</p>
                            <p class="font-mono font-bold">₦{{ number_format($commission->base_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-xs">Rate</p>
                            <p class="font-mono font-bold">{{ $commission->rate }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Partner Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Partner Information</h2>

                <div class="space-y-3">
                    <div>
                        <p class="text-gray-600 text-sm">Partner Name</p>
                        <p class="font-medium">{{ $commission->partner->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Partner Code</p>
                        <p class="font-mono font-bold">{{ $commission->partner->partner_code }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Email</p>
                        <p class="font-medium">{{ $commission->partner->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Partnership Status</p>
                        <p class="font-medium">
                            <span class="px-2 py-1 rounded text-sm
                                @if($commission->partner->status === 'active') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800
                                @endif
                            ">
                                {{ ucfirst($commission->partner->status) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Order Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Order Information</h2>

                <div class="space-y-3">
                    <div>
                        <p class="text-gray-600 text-sm">Order Number</p>
                        <p class="font-mono font-bold text-lg">
                            <a href="{{ route('admin.orders.show', ['order' => $commission->order_id]) }}" class="text-blue-600 hover:underline">
                                {{ $commission->order->order_number }}
                            </a>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Customer</p>
                        <p class="font-medium">{{ $commission->order->customer->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Order Total</p>
                        <p class="font-mono font-bold">₦{{ number_format($commission->order->total, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Order Date</p>
                        <p class="font-medium">{{ $commission->order->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-lg font-bold mb-4">Timestamps</h2>

                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-600">Created</p>
                        <p class="font-mono">{{ $commission->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    @if($commission->available_at)
                        <div>
                            <p class="text-gray-600">Available Since</p>
                            <p class="font-mono">{{ $commission->available_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    @endif
                    @if($commission->paid_at)
                        <div>
                            <p class="text-gray-600">Paid</p>
                            <p class="font-mono">{{ $commission->paid_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    @endif
                    @if($commission->reversed_at)
                        <div>
                            <p class="text-gray-600">Reversed</p>
                            <p class="font-mono">{{ $commission->reversed_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Sidebar Actions -->
        <div>

            <!-- Commission Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Actions</h2>

                <div class="space-y-3">
                    @if($commission->status === 'available')
                        <form action="{{ route('admin.commissions.approve', ['commission' => $commission->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition font-semibold">
                                Approve
                            </button>
                        </form>

                        <form action="{{ route('admin.commissions.mark-payable', ['commission' => $commission->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-purple-600 text-white py-2 rounded hover:bg-purple-700 transition font-semibold">
                                Mark Payable
                            </button>
                        </form>
                    @elseif($commission->status === 'approved')
                        <form action="{{ route('admin.commissions.mark-payable', ['commission' => $commission->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-purple-600 text-white py-2 rounded hover:bg-purple-700 transition font-semibold">
                                Mark Payable
                            </button>
                        </form>
                    @endif

                    @if(!in_array($commission->status, ['reversed', 'paid']))
                        <form action="{{ route('admin.commissions.reverse', ['commission' => $commission->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition font-semibold" onclick="return confirm('Reverse this commission?')">
                                Reverse
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Status Flow Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
                <h3 class="font-bold text-blue-900 mb-3">Commission Status Flow</h3>
                <div class="space-y-2 text-sm text-blue-900">
                    <p>1. <strong>Available</strong> - Auto-generated on order payment</p>
                    <p>2. <strong>Approved</strong> - Reviewed and approved by admin</p>
                    <p>3. <strong>Payable</strong> - Ready for payout processing</p>
                    <p>4. <strong>Paid</strong> - Payout processed to partner</p>
                </div>
            </div>

        </div>

    </div>

</div>

</body>
</html>
