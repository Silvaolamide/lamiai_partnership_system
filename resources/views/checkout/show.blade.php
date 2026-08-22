<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | AIPM</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="text-sm font-semibold text-violet-700">AI Powered Marketing (AIPM)</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Complete your purchase</h1>
            <p class="mt-2 text-slate-600">Choose how you'd like to pay. Your order is created only when you proceed with a payment method.</p>
        </div>

        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <p class="text-sm font-semibold text-violet-700">Your purchase</p>
                    <h2 class="mt-2 text-2xl font-black">{{ $product->name }}</h2>
                    @if($product->description)
                        <p class="mt-3 leading-7 text-slate-600">{{ $product->description }}</p>
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-xl font-bold">Choose your payment method</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">For bank transfer, you will first make the transfer, attach your payment proof, and then submit it. The order is created when you submit the proof.</p>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5">
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-violet-600 font-black text-white">1</div>
                                <div>
                                    <h3 class="font-black">Pay securely with Paystack</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">Your order is created when you click the Paystack payment button, then you are taken to Paystack to complete payment.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-900 font-black text-white">2</div>
                                <div>
                                    <h3 class="font-black">Pay by bank transfer</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">View the account details, make the transfer, attach your proof of payment, and click <strong>Submit Payment Proof</strong>. That button places the order and sends your proof for verification.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside>
                <div class="sticky top-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-xl font-bold">Order total</h2>
                    <div class="mt-4 flex items-end justify-between gap-4 border-b border-slate-200 pb-6">
                        <span class="font-semibold text-slate-600">{{ $product->name }}</span>
                        <span class="text-3xl font-black">₦{{ number_format($product->price, 2) }}</span>
                    </div>

                    <form action="{{ route('checkout.paystack', ['product' => $product->id]) }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        <div>
                            <label class="mb-2 block text-sm font-semibold" for="customer_name">Full name</label>
                            <input id="customer_name" name="customer_name" value="{{ old('customer_name', Auth::user()?->name) }}" required autocomplete="name" placeholder="e.g. Ada Okafor" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold" for="customer_email">Email address</label>
                            <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email', Auth::user()?->email) }}" required autocomplete="email" placeholder="you@example.com" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold" for="customer_phone">Phone</label>
                            <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" autocomplete="tel" placeholder="e.g. 08012345678" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3">
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-violet-600 px-5 py-3.5 font-semibold text-white hover:bg-violet-700">Pay securely with Paystack</button>
                    </form>

                    @if (app()->environment('local'))
                        <div class="my-5 rounded-xl border border-dashed border-amber-300 bg-amber-50 p-4">
                            <p class="text-xs font-black uppercase tracking-wider text-amber-700">Local testing</p>
                            <p class="mt-1 text-sm text-amber-800">Demo payment is available on your local environment only.</p>
                        </div>
                    @endif

                    <div class="my-5 flex items-center gap-3 text-xs text-slate-400"><span class="h-px flex-1 bg-slate-200"></span>OR<span class="h-px flex-1 bg-slate-200"></span></div>

                    <a href="{{ route('checkout.bank-transfer', ['product' => $product->id]) }}" class="block w-full rounded-xl border-2 border-slate-900 px-5 py-3.5 text-center font-black text-slate-900 hover:bg-slate-50">Pay by Bank Transfer</a>
                    <p class="mt-3 text-center text-xs leading-5 text-slate-500">Make your transfer first. On the next page, attach your proof and click <strong>Submit Payment Proof</strong> to place the order.</p>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
