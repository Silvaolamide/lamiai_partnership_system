<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - {{ $order->order_number }} | AIPM</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="text-sm font-semibold text-violet-700">AI Powered Marketing (AIPM)</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Complete your purchase</h1>
            <p class="mt-2 text-slate-600">Choose how you'd like to pay. Manual bank transfers are verified by our platform admin.</p>
        </div>

        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-sm text-slate-500">Order</p><p class="font-mono text-lg font-bold">{{ $order->order_number }}</p></div><span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">{{ ucfirst($order->status) }}</span></div>
                </section>
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-xl font-bold">Your order</h2>
                    <div class="mt-6 space-y-4">@foreach ($order->items as $item)<div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 p-4"><div><p class="font-semibold">{{ $item->product->name }}</p><p class="mt-1 text-sm text-slate-500">Quantity: {{ $item->quantity }}</p></div><p class="font-bold">₦{{ number_format($item->total, 2) }}</p></div>@endforeach</div>
                </section>
                @if ($order->paymentSubmissions->where('status', 'pending')->isNotEmpty())
                    <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6"><p class="font-bold text-amber-900">Payment proof under review</p><p class="mt-1 text-sm text-amber-800">We've received your transfer proof. The platform admin will verify it before your order is marked paid.</p></section>
                @endif
            </div>

            <aside>
                <div class="sticky top-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-xl font-bold">Payment</h2>
                    @if ($order->status === 'pending' && $order->paymentSubmissions->where('status', 'pending')->isEmpty())
                        <form action="{{ route('checkout.paystack', ['orderId' => $order->id]) }}" method="POST" class="mt-6 space-y-4">
                            @csrf
                            <div><label class="mb-2 block text-sm font-semibold" for="customer_name">Full name</label><input id="customer_name" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}" required autocomplete="name" placeholder="e.g. Ada Okafor" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3"></div>
                            <div><label class="mb-2 block text-sm font-semibold" for="customer_email">Email address</label><input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email', $order->customer_email) }}" required autocomplete="email" placeholder="you@example.com" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3"></div>
                            <div><label class="mb-2 block text-sm font-semibold" for="customer_phone">Phone</label><input id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $order->customer_phone) }}" autocomplete="tel" placeholder="e.g. 08012345678" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3"></div>
                            <div class="flex items-end justify-between border-t pt-6"><span class="font-semibold">Total</span><span class="text-3xl font-bold">₦{{ number_format($order->total, 2) }}</span></div>
                            <button class="w-full rounded-xl bg-violet-600 px-5 py-3.5 font-semibold text-white hover:bg-violet-700">Pay securely with Paystack</button>
                        </form>

                        @if (app()->environment('local'))
                            <div class="my-5 rounded-xl border border-dashed border-amber-300 bg-amber-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wider text-amber-700">Local testing</p>
                                <p class="mt-1 text-sm text-amber-800">Demo payment is available on your local environment only. It does not process real money.</p>
                                <form action="{{ route('checkout.confirm', ['orderId' => $order->id]) }}" method="POST" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="demo">
                                    <input type="hidden" name="customer_name" value="{{ old('customer_name', $order->customer_name ?: 'Demo Customer') }}">
                                    <input type="hidden" name="customer_email" value="{{ old('customer_email', $order->customer_email ?: 'demo@example.com') }}">
                                    <input type="hidden" name="customer_phone" value="{{ old('customer_phone', $order->customer_phone) }}">
                                    <button type="submit" class="w-full rounded-xl border-2 border-amber-500 bg-white px-5 py-3 font-black text-amber-800 hover:bg-amber-100">Complete demo payment</button>
                                </form>
                            </div>
                        @endif

                        <div class="my-5 flex items-center gap-3 text-xs text-slate-400"><span class="h-px flex-1 bg-slate-200"></span>OR<span class="h-px flex-1 bg-slate-200"></span></div>
                        <a href="{{ route('checkout.bank-transfer', $order) }}" class="block w-full rounded-xl border-2 border-slate-900 px-5 py-3.5 text-center font-semibold text-slate-900 hover:bg-slate-50">Pay by Bank Transfer</a>
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
