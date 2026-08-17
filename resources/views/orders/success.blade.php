<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - Partnership Program</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
        .gradient-primary { background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%); }
        .gradient-success { background: linear-gradient(135deg, #00B894 0%, #00A878 100%); }
        @keyframes bounce-in {
            0% { opacity: 0; transform: scale(0.5); }
            100% { opacity: 1; transform: scale(1); }
        }
        .animate-bounce-in { animation: bounce-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-float { animation: float 2s ease-in-out infinite; }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-green-50 to-emerald-50 min-h-screen">

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <!-- Success Header -->
    <div class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-20 h-20 gradient-success rounded-full mb-6 animate-bounce-in shadow-lg">
            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
        </div>
        <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-3 leading-tight">
            Order Confirmed! 🎉
        </h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Thank you for your purchase. We're preparing your access to the course and will send you confirmation details shortly.
        </p>
    </div>

    <!-- Main Content Grid -->
    <div class="grid lg:grid-cols-3 gap-8 mb-12">

        <!-- Order Details (Larger) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Order Overview Card -->
            <div class="bg-white rounded-2xl shadow-md p-8 border-t-4 border-emerald-500">
                <div class="grid sm:grid-cols-2 gap-6 mb-8">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-2">Order Number</p>
                        <p class="text-2xl font-mono font-bold text-gray-900">{{ $order->order_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-2">Order Date & Time</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $order->created_at->format('M d, Y') }}</p>
                        <p class="text-gray-600 text-sm">{{ $order->created_at->format('H:i A') }}</p>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-200">
                    <span class="inline-block bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full text-sm font-semibold">✓ Payment Received</span>
                </div>
            </div>

            <!-- Products Purchased -->
            <div class="bg-white rounded-2xl shadow-md p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Your Purchase</h2>
                
                @foreach($order->items as $item)
                    <div class="flex items-center gap-4 p-5 bg-gradient-to-r from-slate-50 to-emerald-50 rounded-xl mb-4 border border-gray-100">
                        <div class="flex-shrink-0">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg gradient-primary">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900">{{ $item->product->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Quantity: <span class="font-semibold">{{ $item->quantity }}</span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600 font-mono">₦{{ number_format($item->unit_price, 2) }}</p>
                            <p class="text-xl font-bold text-gray-900">₦{{ number_format($item->total, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Payment Information -->
            <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-2xl p-8 border border-emerald-200">
                <div class="flex gap-4">
                    <div class="flex-shrink-0 pt-1">
                        <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-emerald-900 mb-2">Payment Confirmed</h3>
                        <p class="text-emerald-800 text-sm">Reference: <span class="font-mono font-semibold">{{ $order->payment_reference }}</span></p>
                        <p class="text-emerald-700 text-sm mt-2">A detailed receipt has been sent to <span class="font-semibold">{{ auth()->user()->email }}</span></p>
                    </div>
                </div>
            </div>

            <!-- Referral Info -->
            @if($order->partner)
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-8 border border-blue-200">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 pt-1">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v2h8v-2zM16 11a2 2 0 11-4 0 2 2 0 014 0zM18 14a3 3 0 00-6 0v2h6v-2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-blue-900 mb-2">Referral Commission</h3>
                            <p class="text-blue-800 text-sm">
                                Referred by <span class="font-bold">{{ $order->partner->user->name }}</span>
                            </p>
                            <p class="text-blue-700 text-sm mt-2">
                                According to the <span class="font-bold">{{ $order->program->name }}</span> partnership program terms, {{ $order->partner->user->name }} will receive a commission on this purchase.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <!-- Sidebar: Next Steps & Summary -->
        <div class="lg:col-span-1 space-y-6">

            <!-- Order Total -->
            <div class="bg-white rounded-2xl shadow-md p-8">
                <p class="text-gray-600 text-sm font-medium mb-4">Order Total</p>
                <div class="space-y-2 mb-6 pb-6 border-b border-gray-200">
                    <div class="flex justify-between text-gray-700">
                        <span>Subtotal</span>
                        <span class="font-semibold">₦{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-emerald-600">
                            <span>Discount</span>
                            <span class="font-semibold">-₦{{ number_format($order->discount, 2) }}</span>
                        </div>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-gray-600 text-sm mb-1">Amount Paid</p>
                    <p class="text-4xl font-bold gradient-primary bg-clip-text text-transparent">
                        ₦{{ number_format($order->total, 2) }}
                    </p>
                </div>
            </div>

            <!-- What's Next -->
            <div class="bg-white rounded-2xl shadow-md p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">What's Next?</h3>
                <ol class="space-y-4">
                    <li class="flex gap-3">
                        <span class="flex items-center justify-center h-6 w-6 rounded-full bg-blue-100 text-blue-600 text-sm font-bold flex-shrink-0">1</span>
                        <span class="text-gray-700 text-sm"><strong>Check Email</strong> for access instructions</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex items-center justify-center h-6 w-6 rounded-full bg-blue-100 text-blue-600 text-sm font-bold flex-shrink-0">2</span>
                        <span class="text-gray-700 text-sm"><strong>Log In</strong> to access your course</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex items-center justify-center h-6 w-6 rounded-full bg-blue-100 text-blue-600 text-sm font-bold flex-shrink-0">3</span>
                        <span class="text-gray-700 text-sm"><strong>Start Learning</strong> immediately</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex items-center justify-center h-6 w-6 rounded-full bg-blue-100 text-blue-600 text-sm font-bold flex-shrink-0">4</span>
                        <span class="text-gray-700 text-sm"><strong>Join Community</strong> for support</span>
                    </li>
                </ol>
            </div>

            <!-- Quick Actions -->
            <div class="space-y-3">
                <a 
                    href="{{ route('partner.dashboard') }}"
                    class="w-full block text-center gradient-primary text-white py-3 px-4 rounded-lg font-semibold transition-all hover:shadow-lg"
                >
                    Go to Dashboard
                </a>
                <a 
                    href="/"
                    class="w-full block text-center bg-white text-gray-900 py-3 px-4 rounded-lg font-semibold border border-gray-200 transition-all hover:border-gray-300 hover:shadow-sm"
                >
                    Continue Shopping
                </a>
            </div>

        </div>

    </div>

    <!-- Support Banner -->
    <div class="bg-white rounded-2xl shadow-md p-8 border-b-4 border-blue-500">
        <div class="grid sm:grid-cols-3 gap-6">
            <div class="flex gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0zM8 9a1 1 0 100-2 1 1 0 000 2zm5-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/></svg>
                <div>
                    <p class="font-semibold text-gray-900">24/7 Support</p>
                    <p class="text-sm text-gray-600">Questions? Contact our team anytime</p>
                </div>
            </div>
            <div class="flex gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 011 1v1h1a1 1 0 011 1v1h1a1 1 0 011 1v1h1a1 1 0 011 1v1h1a1 1 0 011 1v1h1a1 1 0 011 1v1h1a1 1 0 01-1 1H4a1 1 0 01-1-1V3a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                <div>
                    <p class="font-semibold text-gray-900">30-Day Guarantee</p>
                    <p class="text-sm text-gray-600">Not satisfied? Full refund available</p>
                </div>
            </div>
            <div class="flex gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2.5 1A1.5 1.5 0 001 2.5v15A1.5 1.5 0 002.5 19h15a1.5 1.5 0 001.5-1.5v-15A1.5 1.5 0 0017.5 1h-15zm0 1h15v15h-15v-15z"/></svg>
                <div>
                    <p class="font-semibold text-gray-900">Lifetime Access</p>
                    <p class="text-sm text-gray-600">Learn at your own pace, forever</p>
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>
