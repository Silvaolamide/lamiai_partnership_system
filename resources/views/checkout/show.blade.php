<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - {{ $order->order_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="text-sm font-semibold text-blue-600">LAMI AI</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Complete your purchase</h1>
            <p class="mt-2 text-slate-600">Choose how you'd like to pay. Manual bank transfers are verified by our platform admin.</p>
        </div>

        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">Order</p>
                            <p class="font-mono text-lg font-bold">{{ $order->order_number }}</p>
                        </div>
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-xl font-bold">Your order</h2>
                    <div class="mt-6 space-y-4">
                        @foreach ($order->items as $item)
                            <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 p-4">
                                <div>
                                    <p class="font-semibold">{{ $item->product->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Quantity: {{ $item->quantity }}</p>
                                </div>
                                <p class="font-bold">₦{{ number_format($item->total, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                @if ($order->paymentSubmissions->where('status', 'pending')->isNotEmpty())
                    <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                        <p class="font-bold text-amber-900">Payment proof under review</p>
                        <p class="mt-1 text-sm text-amber-800">We've received your transfer proof. The platform admin will verify it before your order is marked paid.</p>
                    </section>
                @endif
            </div>

            <aside>
                <div class="sticky top-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-xl font-bold">Payment</h2>

                    @if ($order->status === 'pending' && $order->paymentSubmissions->where('status', 'pending')->isEmpty())
                        <form action="{{ route('checkout.paystack', ['orderId' => $order->id]) }}" method="POST" class="mt-6 space-y-4">
                            @csrf

                            <div>
                                <label class="mb-2 block text-sm font-semibold">Full name</label>
                                <input name="customer_name" value="{{ old('customer_name', $order->customer_name) }}" required class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3">
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold">Email address</label>
                                <input type="email" name="customer_email" value="{{ old('customer_email', $order->customer_email) }}" required class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3">
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold">Phone</label>
                                <input name="customer_phone" value="{{ old('customer_phone', $order->customer_phone) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3">
                            </div>

                            <div class="flex items-end justify-between border-t pt-6">
                                <span class="font-semibold">Total</span>
                                <span class="text-3xl font-bold">₦{{ number_format($order->total, 2) }}</span>
                            </div>

                            <button class="w-full rounded-xl bg-blue-600 px-5 py-3.5 font-semibold text-white">
                                Pay securely with Paystack
                            </button>
                        </form>

                        <div class="my-5 flex items-center gap-3 text-xs text-slate-400">
                            <span class="h-px flex-1 bg-slate-200"></span>
                            OR
                            <span class="h-px flex-1 bg-slate-200"></span>
                        </div>

                        <a href="{{ route('checkout.bank-transfer', $order) }}" class="block w-full rounded-xl border-2 border-slate-900 px-5 py-3.5 text-center font-semibold text-slate-900 hover:bg-slate-50">
                            Pay by Bank Transfer
                        </a>
                        <p class="mt-3 text-center text-xs text-slate-500">You'll upload your proof and our admin will verify the transfer.</p>
                    @else
                        <p class="mt-4 text-sm text-slate-600">Your payment is being processed. Keep your order number for support.</p>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
