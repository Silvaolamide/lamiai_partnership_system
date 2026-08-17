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
            <p class="mt-2 text-slate-600">Review your order and complete secure payment.</p>
        </div>

        @if(session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
        @endif

        @if(session('warning'))
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">{{ session('warning') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">Order</p>
                            <p class="font-mono text-lg font-bold">{{ $order->order_number }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $order->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-xl font-bold">Your order</h2>
                    <div class="mt-6 space-y-4">
                        @foreach($order->items as $item)
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

                @if($order->partner)
                    <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 sm:p-8">
                        <p class="font-semibold text-emerald-900">Referral attribution active</p>
                        <p class="mt-2 text-sm text-emerald-800">
                            This purchase is attributed to <strong>{{ $order->partner->user->name }}</strong>.
                            Commission is generated only after successful payment.
                        </p>
                    </section>
                @endif
            </div>

            <aside class="lg:col-span-1">
                <div class="sticky top-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-xl font-bold">Order summary</h2>
                    <div class="mt-6 space-y-3 border-b border-slate-200 pb-6">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span>₦{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->discount > 0)
                            <div class="flex justify-between text-emerald-600">
                                <span>Discount</span>
                                <span>-₦{{ number_format($order->discount, 2) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 flex items-end justify-between gap-4">
                        <span class="font-semibold">Total</span>
                        <span class="text-3xl font-bold">₦{{ number_format($order->total, 2) }}</span>
                    </div>

                    @if($order->status === 'pending')
                        <form action="{{ route('checkout.paystack', ['orderId' => $order->id]) }}" method="POST" class="mt-8">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-blue-600 px-5 py-3.5 font-semibold text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md">
                                Pay securely with Paystack
                            </button>
                        </form>

                        <p class="mt-3 text-center text-xs text-slate-500">Card, bank transfer and other available Paystack channels will be shown at checkout.</p>

                        @if(app()->environment('local'))
                            <details class="mt-6 rounded-xl border border-dashed border-slate-300 p-4">
                                <summary class="cursor-pointer text-sm font-semibold text-slate-600">Developer testing</summary>
                                <form action="{{ route('checkout.confirm', ['orderId' => $order->id]) }}" method="POST" class="mt-4">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="demo">
                                    <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">
                                        Complete demo payment
                                    </button>
                                </form>
                            </details>
                        @endif
                    @else
                        <div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800">
                            Payment received. Reference: <span class="font-mono font-semibold">{{ $order->payment_reference }}</span>
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
