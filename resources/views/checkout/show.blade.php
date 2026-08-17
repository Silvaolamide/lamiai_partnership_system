<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Partnership Program</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
        .gradient-primary { background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%); }
        .badge-pending { background: #FEF3C7; color: #92400E; }
        .badge-paid { background: #ECFDF5; color: #065F46; }
        .card-modern { border: 1px solid #f0f0f0; }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900">Secure Checkout</h1>
        <p class="text-gray-600 mt-2">Review your order and complete payment</p>
    </div>

    @if(session('error'))
        <div class="mb-8 animate-fade-in">
            <div class="bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 rounded-r-xl p-4 flex items-start gap-3 shadow-sm">
                <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <div>
                    <p class="font-semibold text-red-900">Error</p>
                    <p class="text-red-800 text-sm mt-1">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-8">

        <!-- Order Details (Main) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Order Overview -->
            <div class="bg-white card-modern rounded-xl shadow-sm p-8 border-l-4 border-blue-500">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Order Number</p>
                        <p class="text-xl font-mono font-bold text-gray-900">{{ $order->order_number }}</p>
                    </div>
                    <span class="badge-pending px-4 py-2 rounded-full text-sm font-semibold">Pending Payment</span>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 pt-6 border-t border-gray-200">
                    <div>
                        <p class="text-gray-600 text-sm">Order Date</p>
                        <p class="font-semibold text-gray-900">{{ $order->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Time</p>
                        <p class="font-semibold text-gray-900">{{ $order->created_at->format('H:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white card-modern rounded-xl shadow-sm p-8">
                <h2 class="text-lg font-bold text-gray-900 mb-6">Order Items</h2>
                
                @foreach($order->items as $item)
                    <div class="flex items-center gap-4 mb-6 p-4 bg-gradient-to-r from-slate-50 to-blue-50 rounded-lg">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ $item->product->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Quantity: {{ $item->quantity }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-mono text-sm text-gray-600">₦{{ number_format($item->unit_price, 2) }}</p>
                            <p class="font-bold text-lg text-gray-900">₦{{ number_format($item->total, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Referral Information -->
            @if($order->partner)
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl p-6 border border-emerald-200">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 pt-0.5">
                            <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v2h8v-2zM16 11a2 2 0 11-4 0 2 2 0 014 0zM18 14a3 3 0 00-6 0v2h6v-2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-emerald-900">Referred by {{ $order->partner->user->name }}</h3>
                            <p class="text-sm text-emerald-800 mt-1">
                                Upon successful payment, {{ $order->partner->user->name }} will earn a referral commission through the <span class="font-bold">{{ $order->program->name }}</span> partnership program.
                            </p>
                            <p class="text-xs text-emerald-700 mt-2 font-mono">Partner Code: {{ $order->partner->partner_code }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Customer Information -->
            <div class="bg-white card-modern rounded-xl shadow-sm p-8">
                <h2 class="text-lg font-bold text-gray-900 mb-6">Your Information</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2">Full Name</label>
                        <p class="text-gray-900 font-medium">{{ auth()->user()->name }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2">Email Address</label>
                        <p class="text-gray-900 font-medium">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Payment Sidebar -->
        <div class="lg:col-span-1">
            
            <div class="bg-white card-modern rounded-xl shadow-sm p-8 sticky top-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Order Summary</h3>

                <div class="space-y-3 mb-6 pb-6 border-b border-gray-200">
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

                <div class="flex justify-between items-center mb-8">
                    <span class="text-gray-900 font-semibold text-lg">Total</span>
                    <div class="text-right">
                        <div class="text-3xl font-bold gradient-primary bg-clip-text text-transparent">
                            ₦{{ number_format($order->total, 2) }}
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                @if($order->status === 'pending')
                    <form 
                        action="{{ route('checkout.confirm', ['orderId' => $order->id]) }}"
                        method="POST"
                        class="space-y-4"
                    >
                        @csrf

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-4">Select Payment Method</label>
                            
                            <div class="space-y-2">
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition">
                                    <input 
                                        type="radio" 
                                        name="payment_method" 
                                        value="card" 
                                        checked
                                        class="mr-3 w-4 h-4"
                                    >
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">Credit / Debit Card</p>
                                        <p class="text-xs text-gray-600">Visa, Mastercard, Amex</p>
                                    </div>
                                </label>

                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition">
                                    <input 
                                        type="radio" 
                                        name="payment_method" 
                                        value="bank"
                                        class="mr-3 w-4 h-4"
                                    >
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">Bank Transfer</p>
                                        <p class="text-xs text-gray-600">Direct bank account transfer</p>
                                    </div>
                                </label>

                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition">
                                    <input 
                                        type="radio" 
                                        name="payment_method" 
                                        value="demo"
                                        class="mr-3 w-4 h-4"
                                    >
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">Demo / Test Payment</p>
                                        <p class="text-xs text-gray-600">For testing purposes</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button 
                            type="submit"
                            class="w-full gradient-primary text-white py-3 px-4 rounded-lg font-semibold transition-all hover:shadow-lg mt-6"
                        >
                            Complete Purchase
                        </button>
                    </form>
                @else
                    <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl p-6 mb-6 border border-emerald-200">
                        <div class="flex gap-3">
                            <svg class="w-6 h-6 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <div>
                                <p class="font-semibold text-emerald-900">Payment Received</p>
                                <p class="text-emerald-800 text-sm mt-1">Reference: <span class="font-mono">{{ $order->payment_reference }}</span></p>
                            </div>
                        </div>
                    </div>

                    <a 
                        href="{{ route('partner.dashboard') }}"
                        class="w-full block text-center gradient-primary text-white py-3 rounded-lg font-semibold transition-all hover:shadow-lg"
                    >
                        View Dashboard
                    </a>
                @endif

                <!-- Trust Indicators -->
                <div class="mt-6 pt-6 border-t border-gray-200 space-y-3">
                    <div class="flex gap-2 text-sm text-gray-700">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Secure & encrypted</span>
                    </div>
                    <div class="flex gap-2 text-sm text-gray-700">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>30-day guarantee</span>
                    </div>
                    <div class="flex gap-2 text-sm text-gray-700">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Instant access</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

</body>
</html>
