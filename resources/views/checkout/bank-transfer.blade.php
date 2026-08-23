<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bank Transfer | AIPM</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<main class="mx-auto max-w-3xl px-4 py-8 sm:py-12">
    <div class="mb-6">
        <a href="{{ route('checkout.show', ['product' => $product->id]) }}" class="text-sm font-semibold text-violet-600">← Back to checkout</a>
        <p class="mt-4 text-sm font-bold text-violet-700">AI Powered Marketing (AIPM)</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Pay by bank transfer</h1>
        <p class="mt-3 text-base leading-7 text-slate-600">Follow the 3 simple steps below. <strong>Your order will only be placed when you click Submit Payment Proof.</strong></p>
    </div>

    @if(session('success'))
        <section class="mb-6 rounded-2xl border-2 border-emerald-200 bg-emerald-50 p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xl font-black text-white">✓</div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-black text-emerald-900">Payment proof submitted successfully</h2>
                    <p class="mt-1 leading-6 text-emerald-800">{{ session('success') }}</p>

                    @if(session('bank_transfer_account_created'))
                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-white p-5">
                            <p class="font-black text-slate-900">Your customer account has been created.</p>

                            @if(session('bank_transfer_password_email_sent'))
                                @php
                                    $customerEmail = session('bank_transfer_customer_email');
                                    $emailDomain = $customerEmail && str_contains($customerEmail, '@')
                                        ? strtolower(substr(strrchr($customerEmail, '@'), 1))
                                        : null;

                                    $providerLinks = [
                                        'gmail.com' => ['https://mail.google.com/', 'Open Gmail'],
                                        'googlemail.com' => ['https://mail.google.com/', 'Open Gmail'],
                                        'outlook.com' => ['https://outlook.live.com/mail/', 'Open Outlook'],
                                        'hotmail.com' => ['https://outlook.live.com/mail/', 'Open Outlook'],
                                        'live.com' => ['https://outlook.live.com/mail/', 'Open Outlook'],
                                        'msn.com' => ['https://outlook.live.com/mail/', 'Open Outlook'],
                                        'yahoo.com' => ['https://mail.yahoo.com/', 'Open Yahoo Mail'],
                                        'ymail.com' => ['https://mail.yahoo.com/', 'Open Yahoo Mail'],
                                    ];

                                    $emailProviderUrl = null;
                                    $emailProviderLabel = 'Open Your Email';

                                    if ($emailDomain && isset($providerLinks[$emailDomain])) {
                                        [$emailProviderUrl, $emailProviderLabel] = $providerLinks[$emailDomain];
                                    } elseif ($emailDomain) {
                                        $emailProviderUrl = 'https://' . $emailDomain;
                                    }
                                @endphp

                                <div class="mt-3 rounded-xl bg-violet-50 p-4">
                                    <p class="font-black text-violet-900">📧 Check your email</p>
                                    <p class="mt-1 text-sm leading-6 text-violet-800">
                                        We sent a password setup email to
                                        <strong class="break-all">{{ $customerEmail }}</strong>.
                                    </p>

                                    @if($emailProviderUrl)
                                        <a href="{{ $emailProviderUrl }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-violet-600 px-5 py-3.5 font-black text-white shadow-sm transition hover:bg-violet-700 sm:w-auto">
                                            {{ $emailProviderLabel }} →
                                        </a>
                                    @endif

                                    <p class="mt-2 text-xs text-violet-700">Check your Spam/Junk folder if you don't see it in your inbox.</p>
                                </div>
                            @else
                                <p class="mt-2 leading-6 text-emerald-800">Please use the password reset option on the customer login page to set your password.</p>
                            @endif
                        </div>
                    @endif

                    @if(session('bank_transfer_order_number'))
                        <p class="mt-4 font-semibold text-emerald-900">Order number: <span class="font-mono">{{ session('bank_transfer_order_number') }}</span></p>
                    @endif

                    @if(session('bank_transfer_show_dashboard'))
                        <a href="{{ route('customer.dashboard') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 font-black text-white shadow-sm transition hover:bg-emerald-700">Go to My Dashboard →</a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if(session('error'))<div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700"><p class="font-bold">Please correct the following:</p><ul class="ml-5 mt-2 list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if(!session('bank_transfer_submitted'))
        <section class="mb-6 rounded-2xl border-2 border-violet-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-black">How to pay by bank transfer</h2>
            <div class="mt-5 space-y-4">
                <div class="flex gap-4 rounded-xl bg-violet-50 p-4"><div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-600 font-black text-white">1</div><div><p class="font-black">Make the payment</p><p class="mt-1 text-sm leading-6 text-slate-600">Transfer the <strong>exact amount</strong> shown below into the AIPM bank account.</p></div></div>
                <div class="flex gap-4 rounded-xl bg-blue-50 p-4"><div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-600 font-black text-white">2</div><div><p class="font-black">Attach your proof</p><p class="mt-1 text-sm leading-6 text-slate-600">After the transfer, complete the form below and attach your bank transfer receipt or proof of payment.</p></div></div>
                <div class="flex gap-4 rounded-xl bg-emerald-50 p-4"><div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-600 font-black text-white">3</div><div><p class="font-black">Submit the proof — this places your order</p><p class="mt-1 text-sm leading-6 text-slate-600">When everything is complete, click <strong>Submit Payment Proof</strong>. This is the action that creates your order and sends the transfer for admin verification.</p></div></div>
            </div>
        </section>

        <div class="space-y-5">
            <section class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm sm:p-7"><p class="text-sm font-bold text-slate-300">EXACT AMOUNT TO TRANSFER</p><p class="mt-1 text-4xl font-black tracking-tight">₦{{ number_format($product->price, 2) }}</p><p class="mt-3 text-sm text-slate-300">Product: {{ $product->name }}</p></section>
            <section class="rounded-2xl border-2 border-violet-200 bg-white p-6 shadow-sm sm:p-7">
                <div class="flex items-center justify-between gap-4"><div><p class="text-xs font-black uppercase tracking-wider text-violet-700">Step 1</p><h2 class="mt-1 text-xl font-black">Make payment into this account</h2></div><span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-black text-violet-700">PAY HERE</span></div>
                <dl class="mt-6 divide-y divide-slate-100 rounded-xl border border-slate-200"><div class="flex items-center justify-between gap-4 p-4"><dt class="text-sm text-slate-500">Bank</dt><dd class="text-right font-black">{{ $paymentSettings->bank_name ?: 'Not configured' }}</dd></div><div class="flex items-center justify-between gap-4 p-4"><dt class="text-sm text-slate-500">Account name</dt><dd class="text-right font-black">{{ $paymentSettings->account_name ?: 'Not configured' }}</dd></div><div class="flex items-center justify-between gap-4 p-4"><dt class="text-sm text-slate-500">Account number</dt><dd class="text-right font-black tracking-wide">{{ $paymentSettings->account_number ?: 'Not configured' }}</dd></div><div class="flex items-center justify-between gap-4 bg-violet-50 p-4"><dt class="font-bold text-violet-800">Amount</dt><dd class="text-right text-xl font-black text-violet-800">₦{{ number_format($product->price, 2) }}</dd></div></dl>
            </section>
            <form id="bank-transfer-form" action="{{ route('checkout.bank-transfer.submit', ['product' => $product->id]) }}" method="POST" enctype="multipart/form-data" class="rounded-2xl border-2 border-emerald-200 bg-white p-6 shadow-sm sm:p-7">
                @csrf
                <div class="mb-6"><p class="text-xs font-black uppercase tracking-wider text-emerald-700">Steps 2 & 3</p><h2 class="mt-1 text-xl font-black">Attach proof and submit</h2><p class="mt-2 text-sm leading-6 text-slate-600">Make your transfer first. Then complete these details, attach your proof, and click the button at the bottom. <strong>The button places your order.</strong></p></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label for="customer_name" class="block text-sm font-semibold">Full name</label><input id="customer_name" name="customer_name" value="{{ old('customer_name', Auth::user()?->name) }}" placeholder="e.g. Ada Okafor" required class="mt-1 w-full rounded-xl border-slate-200"></div>
                    <div><label for="customer_email" class="block text-sm font-semibold">Email</label><input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email', Auth::user()?->email) }}" placeholder="you@example.com" required class="mt-1 w-full rounded-xl border-slate-200"></div>
                    <div><label for="customer_phone" class="block text-sm font-semibold">Phone</label><input id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="e.g. 08012345678" class="mt-1 w-full rounded-xl border-slate-200"></div>
                    <div><label for="amount" class="block text-sm font-semibold">Amount transferred</label><input id="amount" type="number" step="0.01" name="amount" value="{{ old('amount', $product->price) }}" required class="mt-1 w-full rounded-xl border-slate-200"></div>
                    <div><label for="bank_name" class="block text-sm font-semibold">Your bank</label><input id="bank_name" name="bank_name" value="{{ old('bank_name') }}" placeholder="e.g. Access Bank" required class="mt-1 w-full rounded-xl border-slate-200"></div>
                    <div><label for="transaction_reference" class="block text-sm font-semibold">Transaction reference</label><input id="transaction_reference" name="transaction_reference" value="{{ old('transaction_reference') }}" placeholder="Your bank transaction reference" required class="mt-1 w-full rounded-xl border-slate-200"></div>
                    <div><label for="transfer_date" class="block text-sm font-semibold">Transfer date</label><input id="transfer_date" type="date" name="transfer_date" value="{{ old('transfer_date', now()->toDateString()) }}" required class="mt-1 w-full rounded-xl border-slate-200"></div>
                    <div><label for="proof" class="block text-sm font-semibold">Proof of payment</label><input id="proof" type="file" name="proof" accept="image/*,.pdf" required class="mt-1 w-full rounded-xl border-slate-200 p-2"><p class="mt-1 text-xs text-slate-500">JPG, PNG, WEBP or PDF. Maximum 5MB.</p></div>
                </div>
                <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4"><p class="text-sm font-bold text-amber-900">Important</p><p class="mt-1 text-sm leading-6 text-amber-800">Only click the button below after you have made the transfer and attached your proof. Clicking it will place the order and send your proof to the payment team.</p></div>
                <button id="submit-payment-proof" type="submit" class="mt-5 w-full rounded-xl bg-emerald-600 px-5 py-4 text-base font-black text-white shadow-lg hover:bg-emerald-700">Submit Payment Proof</button>
            </form>
        </div>
    @endif

    <section class="mt-6 rounded-2xl border border-blue-100 bg-blue-50 p-5"><h3 class="font-bold">Need help with this payment?</h3><p class="mt-1 text-sm text-slate-700">Contact support and quote the product name: <strong>{{ $product->name }}</strong>.</p><div class="mt-3 flex flex-wrap gap-4 text-sm font-semibold">@if($paymentSettings->support_phone)<a href="tel:{{ $paymentSettings->support_phone }}">📞 {{ $paymentSettings->support_phone }}</a>@endif @if($paymentSettings->support_whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/', '', $paymentSettings->support_whatsapp) }}">💬 WhatsApp</a>@endif @if($paymentSettings->support_email)<a href="mailto:{{ $paymentSettings->support_email }}">✉ {{ $paymentSettings->support_email }}</a>@endif</div></section>
</main>
<script>document.getElementById('bank-transfer-form')?.addEventListener('submit', function () { const button = document.getElementById('submit-payment-proof'); if (button) { button.disabled = true; button.textContent = 'Submitting Payment Proof…'; button.classList.add('opacity-70', 'cursor-not-allowed'); } });</script>
</body>
</html>
