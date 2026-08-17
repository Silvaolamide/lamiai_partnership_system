<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Partnership Program</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">

<div class="max-w-4xl mx-auto px-6 py-12">

    <h1 class="text-3xl font-bold mb-8">
        Order Checkout
    </h1>

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid md:grid-cols-3 gap-6">

        <!-- Order Details -->
        <div class="md:col-span-2">
            
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Order Summary</h2>
                
                <div class="space-y-4 border-b pb-4 mb-4">
                    @foreach($order->items as $item)
                        <div class="flex justify-between">
                            <div>
                                <p class="font-semibold">{{ $item->product->name }}</p>
                                <p class="text-gray-500 text-sm">Qty: {{ $item->quantity }}</p>
                            </div>
                            <p class="font-semibold">
                                ₦{{ number_format($item->total, 2) }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center mb-4">
                    <span class="text-gray-600">Subtotal:</span>
                    <span>₦{{ number_format($order->subtotal, 2) }}</span>
                </div>

                @if($order->discount > 0)
                    <div class="flex justify-between items-center mb-4 text-green-600">
                        <span>Discount:</span>
                        <span>-₦{{ number_format($order->discount, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between items-center text-lg font-bold border-t pt-4">
                    <span>Total:</span>
                    <span>₦{{ number_format($order->total, 2) }}</span>
                </div>
            </div>

            <!-- Referral Info -->
            @if($order->partner)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h3 class="font-bold text-blue-900 mb-2">Referral Information</h3>
                    <p class="text-blue-800">
                        This purchase is referred by <strong>{{ $order->partner->user->name }}</strong>. 
                        Upon successful payment, they will earn a referral commission as configured in the 
                        <strong>{{ $order->program->name }}</strong> partnership program.
                    </p>
                </div>
            @endif

            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold mb-4">Customer Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-600 font-medium mb-2">Name</label>
                        <p class="text-gray-900">{{ auth()->user()->name }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-600 font-medium mb-2">Email</label>
                        <p class="text-gray-900">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Payment Section -->
        <div>
            
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4">
                <h2 class="text-xl font-bold mb-4">Payment</h2>

                <!-- Order Status -->
                <div class="mb-6 p-4 rounded-lg" :class="@json($order->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800')">
                    <p class="font-semibold capitalize">
                        Status: {{ $order->status }}
                    </p>
                </div>

                <!-- Payment Method Selection -->
                @if($order->status === 'pending')
                    <form 
                        action="{{ route('checkout.confirm', ['orderId' => $order->id]) }}"
                        method="POST"
                        class="space-y-4"
                    >
                        @csrf

                        <div class="mb-6">
                            <label class="block font-medium mb-3">Select Payment Method</label>
                            
                            <div class="space-y-3">
                                <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-black">
                                    <input 
                                        type="radio" 
                                        name="payment_method" 
                                        value="card" 
                                        checked
                                        class="mr-3"
                                    >
                                    <span class="font-medium">Credit/Debit Card</span>
                                </label>

                                <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-black">
                                    <input 
                                        type="radio" 
                                        name="payment_method" 
                                        value="bank"
                                        class="mr-3"
                                    >
                                    <span class="font-medium">Bank Transfer</span>
                                </label>

                                <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-black">
                                    <input 
                                        type="radio" 
                                        name="payment_method" 
                                        value="demo"
                                        class="mr-3"
                                    >
                                    <span class="font-medium">Demo/Test Payment</span>
                                </label>
                            </div>
                        </div>

                        <button 
                            type="submit"
                            class="w-full bg-black text-white py-3 rounded-lg font-semibold hover:bg-gray-900 transition"
                        >
                            Complete Purchase
                        </button>
                    </form>
                @else
                    <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
                        <p class="font-semibold">✓ Payment Received</p>
                        <p class="text-sm mt-1">Reference: {{ $order->payment_reference }}</p>
                    </div>

                    <a 
                        href="{{ route('partner.dashboard') }}"
                        class="w-full block text-center bg-gray-200 text-gray-900 py-2 rounded-lg font-semibold hover:bg-gray-300 transition"
                    >
                        View Dashboard
                    </a>
                @endif

                <p class="text-center text-gray-500 text-xs mt-6">
                    Secure payment processing<br>
                    Your data is encrypted and protected
                </p>
            </div>

        </div>

    </div>

</div>

</body>
</html>
