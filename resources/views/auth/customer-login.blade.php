<x-guest-layout>
    <div class="mb-8">
        <div class="mb-3 inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-blue-700">Customer access</div>
        <h2 class="text-3xl font-extrabold tracking-tight text-[#171323]">Access your purchase.</h2>
        <p class="mt-2 text-sm leading-6 text-gray-500">Sign in to access your customer dashboard and purchased products.</p>
    </div>
    @if(session('status'))<div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>@endif
    @if($order)<div class="mb-5 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800"><p class="font-semibold">Purchase confirmed</p><p class="mt-1">Order {{ $order->order_number }} is ready to be added to your account.</p></div>@endif
    @if ($errors->any())<div class="mb-5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700"><p class="font-semibold">We couldn't sign you in.</p><p class="mt-1">{{ $errors->first() }}</p></div>@endif
    <form method="POST" action="{{ route('customer.login') }}" class="space-y-5">
        @csrf
        @if($order)<input type="hidden" name="order" value="{{ $order->id }}">@endif
        <div><x-input-label for="email" value="Purchase email address" class="mb-2 font-semibold text-gray-700" /><x-text-input id="email" class="brand-input block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm" type="email" name="email" :value="old('email', $order?->customer_email)" required autofocus autocomplete="username" placeholder="you@example.com" /><x-input-error :messages="$errors->get('email')" class="mt-2" /></div>
        <div><x-input-label for="password" value="Password" class="mb-2 font-semibold text-gray-700" /><x-text-input id="password" class="brand-input block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" /><x-input-error :messages="$errors->get('password')" class="mt-2" /></div>
        <label class="flex items-center gap-3 text-sm text-gray-500"><input type="checkbox" name="remember" class="rounded border-gray-300"> Keep me signed in</label>
        <button type="submit" class="brand-btn flex w-full items-center justify-center rounded-xl px-5 py-3.5 text-sm font-bold text-white">Continue to my dashboard →</button>
    </form>
    <div class="my-7 flex items-center gap-4"><div class="h-px flex-1 bg-gray-100"></div><span class="text-xs font-medium text-gray-400">NEW CUSTOMER?</span><div class="h-px flex-1 bg-gray-100"></div></div>
    <a href="{{ route('customer.register', $order ? ['order' => $order->id] : []) }}" class="flex w-full items-center justify-center rounded-xl border border-gray-200 px-5 py-3.5 text-sm font-bold text-gray-700">Create a customer account</a>
    <p class="mt-6 text-center text-xs text-gray-400">Business account? <a href="{{ route('login') }}" class="font-semibold text-violet-700">Business login</a>.</p>
</x-guest-layout>
