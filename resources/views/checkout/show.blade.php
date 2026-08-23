<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | AIPM</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <p class="text-sm font-semibold text-violet-700">AI Powered Marketing (AIPM)</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Complete your purchase</h1>
        <p class="mt-2 text-slate-600">Pay first. If you're new to AIPM, we'll create your customer account automatically after successful payment.</p>
    </div>

    @if (session('error'))<div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800"><p class="font-semibold">Please check your details.</p><ul class="mt-2 list-disc pl-5 text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-sm font-semibold text-violet-700">Your purchase</p><h2 class="mt-2 text-2xl font-black">{{ $product->name }}</h2>
                @if($product->description)<p class="mt-3 leading-7 text-slate-600">{{ $product->description }}</p>@endif
            </section>

            <section class="rounded-2xl border border-violet-200 bg-violet-50 p-6 shadow-sm sm:p-8">
                <h2 class="text-xl font-bold">Your details</h2>
                @guest<p class="mt-2 text-sm leading-6 text-slate-600">No account is required before payment. Enter your details once and we'll take care of the account setup after payment.</p>@else<p class="mt-2 text-sm leading-6 text-slate-600">You're signed in as <strong>{{ Auth::user()->email }}</strong>. We'll use your account details.</p>@endguest
                <form action="{{ route('checkout.paystack', ['product' => $product->id]) }}" method="POST" class="mt-5 grid gap-4 sm:grid-cols-2">
                    @csrf
                    <div class="sm:col-span-2"><label for="customer_name" class="block text-sm font-semibold text-slate-700">Full name</label><input id="customer_name" name="customer_name" value="{{ old('customer_name', Auth::user()?->name) }}" @auth readonly @endauth required class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-100"></div>
                    <div><label for="customer_email" class="block text-sm font-semibold text-slate-700">Email address</label><input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email', Auth::user()?->email) }}" @auth readonly @endauth required class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-100"></div>
                    <div><label for="customer_phone" class="block text-sm font-semibold text-slate-700">Phone <span class="font-normal text-slate-400">(optional)</span></label><input id="customer_phone" name="customer_phone" value="{{ old('customer_phone', Auth::user()?->phone) }}" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-100"></div>
                    <div class="sm:col-span-2 rounded-xl bg-white/80 p-4 text-sm text-slate-600"><strong>After payment:</strong> Paystack confirms the transaction, your purchase is attached to your customer account, and you'll go straight to your dashboard. New customers get an account automatically.</div>
                    <button type="submit" class="sm:col-span-2 w-full rounded-xl bg-violet-600 px-5 py-3.5 font-semibold text-white hover:bg-violet-700">Pay securely with Paystack</button>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-xl font-bold">Pay by bank transfer</h2><p class="mt-2 text-sm leading-6 text-slate-600">Transfer the exact amount below, then submit your proof of payment.</p>
                <dl class="mt-5 divide-y divide-slate-200 overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="flex items-center justify-between gap-4 p-4"><dt class="text-sm text-slate-500">Bank</dt><dd class="text-right font-black">{{ $paymentSettings->bank_name ?: 'Not configured' }}</dd></div>
                    <div class="flex items-center justify-between gap-4 p-4"><dt class="text-sm text-slate-500">Account name</dt><dd class="text-right font-black">{{ $paymentSettings->account_name ?: 'Not configured' }}</dd></div>
                    <div class="flex items-center justify-between gap-4 p-4"><dt class="text-sm text-slate-500">Account number</dt><dd class="text-right font-black tracking-wide">{{ $paymentSettings->account_number ?: 'Not configured' }}</dd></div>
                    <div class="flex items-center justify-between gap-4 bg-slate-100 p-4"><dt class="font-bold text-slate-700">Exact amount</dt><dd class="text-right text-xl font-black">₦{{ number_format($product->price, 2) }}</dd></div>
                </dl>
                <a href="{{ route('checkout.bank-transfer', ['product' => $product->id]) }}" class="mt-5 block w-full rounded-xl border-2 border-slate-900 px-5 py-3.5 text-center font-black text-slate-900 hover:bg-slate-50">Pay by Bank Transfer</a>
            </section>
        </div>
        <aside><div class="sticky top-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"><h2 class="text-xl font-bold">Order total</h2><div class="mt-4 flex items-end justify-between gap-4 border-b border-slate-200 pb-6"><span class="font-semibold text-slate-600">{{ $product->name }}</span><span class="text-3xl font-black">₦{{ number_format($product->price, 2) }}</span></div><div class="mt-6 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-600"><strong>Secure payment</strong><br>Your payment is processed by Paystack. AIPM marks the order paid only after verification.</div></div></aside>
    </div>
</div>
</body>
</html>
