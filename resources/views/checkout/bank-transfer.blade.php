<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bank Transfer - {{ $order->order_number }} | AIPM</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto max-w-3xl px-4 py-8 sm:py-12">
        <div class="mb-6">
            <a href="{{ route('checkout.show', ['orderId' => $order->id]) }}" class="text-sm font-semibold text-violet-600">← Back to checkout</a>
            <p class="mt-4 text-sm font-bold text-violet-700">AI Powered Marketing (AIPM)</p>
            <h1 class="mt-2 text-3xl font-bold">Pay by bank transfer</h1>
            <p class="mt-2 text-slate-600">Transfer the exact amount below, then submit your proof. Payment is confirmed by our platform admin.</p>
        </div>

        @if(session('error'))
            <div class="mb-5 rounded-xl bg-red-50 p-4 text-red-700">{{ session('error') }}</div>
        @endif

        @if(session('success'))
            <div class="mb-5 rounded-xl bg-emerald-50 p-4 text-emerald-700">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-xl bg-red-50 p-4 text-red-700">
                <ul class="ml-5 list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-5">
            <section class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm">
                <p class="text-sm text-slate-300">Amount to transfer</p>
                <p class="mt-1 text-4xl font-extrabold">₦{{ number_format($order->total, 2) }}</p>
                <p class="mt-3 text-sm text-slate-300">Order {{ $order->order_number }}</p>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold">Platform bank account</h2>
                <dl class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs uppercase text-slate-500">Bank</dt>
                        <dd class="mt-1 font-bold">{{ $paymentSettings->bank_name ?: 'Not configured' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-slate-500">Account name</dt>
                        <dd class="mt-1 font-bold">{{ $paymentSettings->account_name ?: 'Not configured' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-slate-500">Account number</dt>
                        <dd class="mt-1 font-bold">{{ $paymentSettings->account_number ?: 'Not configured' }}</dd>
                    </div>
                </dl>
            </section>

            <form action="{{ route('checkout.bank-transfer.submit', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                <h2 class="text-xl font-bold">Submit payment proof</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="customer_name" class="block text-sm font-semibold">Full name</label>
                        <input id="customer_name" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}" placeholder="e.g. Ada Okafor" required class="mt-1 w-full rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label for="customer_email" class="block text-sm font-semibold">Email</label>
                        <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email', $order->customer_email) }}" placeholder="you@example.com" required class="mt-1 w-full rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label for="customer_phone" class="block text-sm font-semibold">Phone</label>
                        <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $order->customer_phone) }}" placeholder="e.g. 08012345678" class="mt-1 w-full rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Amount transferred</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount', $order->total) }}" required class="mt-1 w-full rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Your bank</label>
                        <input name="bank_name" value="{{ old('bank_name') }}" placeholder="e.g. Access Bank" required class="mt-1 w-full rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Transaction reference</label>
                        <input name="transaction_reference" value="{{ old('transaction_reference') }}" placeholder="Your bank transaction reference" required class="mt-1 w-full rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Transfer date</label>
                        <input type="date" name="transfer_date" value="{{ old('transfer_date', now()->toDateString()) }}" required class="mt-1 w-full rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Proof of payment</label>
                        <input type="file" name="proof" accept="image/*,.pdf" required class="mt-1 w-full rounded-xl border-slate-200 p-2">
                    </div>
                </div>
                <button class="w-full rounded-xl bg-violet-600 px-5 py-3.5 font-bold text-white hover:bg-violet-700">Submit payment proof</button>
            </form>

            <section class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
                <h3 class="font-bold">Need help with this payment?</h3>
                <p class="mt-1 text-sm text-slate-700">Quote <strong>{{ $order->order_number }}</strong> when contacting support.</p>
                <div class="mt-3 flex flex-wrap gap-4 text-sm font-semibold">
                    @if($paymentSettings->support_phone)
                        <a href="tel:{{ $paymentSettings->support_phone }}">📞 {{ $paymentSettings->support_phone }}</a>
                    @endif
                    @if($paymentSettings->support_whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $paymentSettings->support_whatsapp) }}">💬 WhatsApp</a>
                    @endif
                    @if($paymentSettings->support_email)
                        <a href="mailto:{{ $paymentSettings->support_email }}">✉ {{ $paymentSettings->support_email }}</a>
                    @endif
                </div>
            </section>

            @if(Auth::check())
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h3 class="font-bold">Report a payment problem</h3>
                    <form method="POST" action="{{ route('checkout.payment-dispute', $order) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                        @csrf
                        <select name="reason" required class="w-full rounded-xl border-slate-200">
                            <option value="">Select a problem</option>
                            <option>I made the transfer but payment is still pending</option>
                            <option>My payment was rejected</option>
                            <option>I was charged but my order wasn't confirmed</option>
                            <option>I transferred the wrong amount</option>
                            <option>Other</option>
                        </select>
                        <textarea name="message" required maxlength="5000" placeholder="Tell our support team what happened" class="w-full rounded-xl border-slate-200"></textarea>
                        <input type="file" name="attachment" accept="image/*,.pdf" class="w-full rounded-xl border-slate-200 p-2">
                        <button class="w-full rounded-xl bg-slate-900 px-4 py-3 font-bold text-white">Submit payment dispute</button>
                    </form>
                </section>
            @endif
        </div>
    </main>
</body>
</html>
