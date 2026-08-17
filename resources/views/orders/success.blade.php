<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - Partnership Program</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">

<div class="max-w-2xl mx-auto px-6 py-12">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
        </div>
        <h1 class="text-4xl font-bold text-gray-900 mb-2">
            Order Confirmed!
        </h1>
        <p class="text-xl text-gray-600">
            Thank you for your purchase. Your order has been successfully processed.
        </p>
    </div>

    <!-- Order Details -->
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <div class="grid md:grid-cols-2 gap-8 mb-8 pb-8 border-b">
            
            <div>
                <p class="text-gray-600 font-medium mb-1">Order Number</p>
                <p class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</p>
            </div>

            <div>
                <p class="text-gray-600 font-medium mb-1">Order Date</p>
                <p class="text-2xl font-bold text-gray-900">{{ $order->created_at->format('M d, Y') }}</p>
            </div>

        </div>

        <!-- Products -->
        <div class="mb-8">
            <h2 class="text-lg font-bold mb-4">Product Purchased</h2>
            @foreach($order->items as $item)
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold">{{ $item->product->name }}</p>
                        <p class="text-gray-600 text-sm">Quantity: {{ $item->quantity }}</p>
                    </div>
                    <p class="text-lg font-bold">₦{{ number_format($item->total, 2) }}</p>
                </div>
            @endforeach
        </div>

        <!-- Order Summary -->
        <div class="space-y-3 mb-8 pb-8 border-b">
            <div class="flex justify-between">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-semibold">₦{{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if($order->discount > 0)
                <div class="flex justify-between text-green-600">
                    <span>Discount:</span>
                    <span>-₦{{ number_format($order->discount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-lg font-bold">
                <span>Total Amount Paid:</span>
                <span>₦{{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="mb-8">
            <h2 class="text-lg font-bold mb-4">Payment Information</h2>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <p class="text-green-800 font-semibold mb-2">✓ Payment Confirmed</p>
                <p class="text-green-700">
                    <strong>Payment Reference:</strong> {{ $order->payment_reference }}
                </p>
                <p class="text-green-700 text-sm mt-2">
                    A confirmation email has been sent to {{ auth()->user()->email }}
                </p>
            </div>
        </div>

        <!-- Referral Commission Info -->
        @if($order->partner)
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <p class="font-semibold text-blue-900 mb-2">Referral Commission</p>
                <p class="text-blue-800">
                    This purchase was referred by <strong>{{ $order->partner->user->name }}</strong>. 
                    Based on the <strong>{{ $order->program->name }}</strong> partnership rules, 
                    they will receive a commission on this sale.
                </p>
            </div>
        @endif

    </div>

    <!-- Next Steps -->
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <h2 class="text-lg font-bold mb-4">Next Steps</h2>
        <ol class="space-y-3 list-decimal list-inside">
            <li class="text-gray-700">Check your email for access instructions to the course</li>
            <li class="text-gray-700">Log in to your account to access the learning materials</li>
            <li class="text-gray-700">Complete your profile for better learning experience</li>
            <li class="text-gray-700">Join our community for support and networking</li>
        </ol>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4 justify-center">
        <a 
            href="{{ route('partner.dashboard') }}"
            class="inline-block bg-black text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-900 transition"
        >
            Back to Dashboard
        </a>
        <a 
            href="/"
            class="inline-block bg-gray-200 text-gray-900 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition"
        >
            Continue Shopping
        </a>
    </div>

</div>

</body>
</html>
