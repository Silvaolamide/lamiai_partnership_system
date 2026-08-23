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
                    <p class="mt-2 text-sm leading-6 text-slate-600">Pay securely with Paystack or make a bank transfer using the account details below.</p>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5">
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-violet-600 font-black text-white">1</div>
                                <div>
                                    <h3 class="font-black">Pay securely with Paystack</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">Click the Paystack button to continue securely. Your saved customer account details will be used for the payment.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-900 font-black text-white">2</div>
                                <div>
                                    <h3 class="font-black">Pay by bank transfer</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">Transfer the exact amount below, then click the bank transfer button to attach your proof of payment.</p>
                                </div>
                            </div>

                            <dl class="mt-5 divide-y divide-slate-200 overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <div class="flex items-center justify-between gap-4 p-4">
                                    <dt class="text-sm text-slate-500">Bank</dt>
                                    <dd class="text-right font-black">{{ $paymentSettings->bank_name ?: 'Not configured' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 p-4">
                                    <dt class="text-sm text-slate-500">Account name</dt>
                                    <dd class="text-right font-black">{{ $paymentSettings->account_name ?: 'Not configured' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 p-4">
                                    <dt class="text-sm text-slate-500">Account number</dt>
                                    <dd class="text-right font-black tracking-wide">{{ $paymentSettings->account_number ?: 'Not configured' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 bg-slate-100 p-4">
                                    <dt class="font-bold text-slate-700">Exact amount</dt>
                                    <dd class="text-right text-xl font-black">₦{{ number_format($product->price, 2) }}</dd>
                                </div>
                            </dl>
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

                    @auth
                        <p class="mt-6 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                            Paying as <strong>{{ Auth::user()->name }}</strong><br>
                            <span>{{ Auth::user()->email }}</span>
                        </p>
                        <form action="{{ route('checkout.paystack', ['product' => $product->id]) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-violet-600 px-5 py-3.5 font-semibold text-white hover:bg-violet-700">Pay securely with Paystack</button>
                        </form>
                    @else
                        <a href="{{ route('customer.login', ['intended' => route('checkout.show', ['product' => $product->id])]) }}" class="mt-6 block w-full rounded-xl bg-violet-600 px-5 py-3.5 text-center font-semibold text-white hover:bg-violet-700">Sign in to pay with Paystack</a>
                        <p class="mt-3 text-center text-xs leading-5 text-slate-500">Sign in or create a customer account to use Paystack.</p>
                    @endauth

                    @if (app()->environment('local'))
                        <div class="my-5 rounded-xl border border-dashed border-amber-300 bg-amber-50 p-4">
                            <p class="text-xs font-black uppercase tracking-wider text-amber-700">Local testing</p>
                            <p class="mt-1 text-sm text-amber-800">Demo payment is available on your local environment only. It does not process real money.</p>
                        </div>
                    @endif

                    <div class="my-5 flex items-center gap-3 text-xs text-slate-400"><span class="h-px flex-1 bg-slate-200"></span>OR<span class="h-px flex-1 bg-slate-200"></span></div>

                    <a href="{{ route('checkout.bank-transfer', ['product' => $product->id]) }}" class="block w-full rounded-xl border-2 border-slate-900 px-5 py-3.5 text-center font-black text-slate-900 hover:bg-slate-50">Pay by Bank Transfer</a>
                    <p class="mt-3 text-center text-xs leading-5 text-slate-500">The bank details are shown beside this payment option. Make your transfer first, then attach your proof to place the order.</p>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
