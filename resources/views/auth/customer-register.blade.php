<x-guest-layout>
    <div class="mb-8">
        <div class="mb-3 inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-blue-700">Customer account</div>
        <h2 class="text-3xl font-extrabold tracking-tight text-[#171323]">Create your customer account.</h2>
        <p class="mt-2 text-sm leading-6 text-gray-500">Your purchase is complete. Create your account to access your product and future orders.</p>
    </div>

    @if($order)
        <div class="mb-5 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            <p class="font-semibold">Order {{ $order->order_number }}</p>
            <p class="mt-1">Use the email address from your purchase so we can place the product in your dashboard.</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('customer.register') }}" class="space-y-5">
        @csrf
        @if($order)<input type="hidden" name="order" value="{{ $order->id }}">@endif
        <div>
            <x-input-label for="name" value="Your name" class="mb-2 font-semibold text-gray-700" />
            <x-text-input id="name" class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm" type="text" name="name" :value="old('name', $order?->customer_name)" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="email" value="Purchase email address" class="mb-2 font-semibold text-gray-700" />
            <x-text-input id="email" class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm" type="email" name="email" :value="old('email', $order?->customer_email)" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="password" value="Password" class="mb-2 font-semibold text-gray-700" />
                <x-text-input id="password" class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password_confirmation" value="Confirm password" class="mb-2 font-semibold text-gray-700" />
                <x-text-input id="password_confirmation" class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>
        </div>
        <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-violet-700 px-5 py-3.5 text-sm font-bold text-white">Create account & access purchase →</button>
    </form>

    <div class="my-7 flex items-center gap-4"><div class="h-px flex-1 bg-gray-100"></div><span class="text-xs font-medium text-gray-400">ALREADY HAVE AN ACCOUNT?</span><div class="h-px flex-1 bg-gray-100"></div></div>
    <a href="{{ route('customer.login', $order ? ['order' => $order->id] : []) }}" class="flex w-full items-center justify-center rounded-xl border border-gray-200 px-5 py-3.5 text-sm font-bold text-gray-700">Sign in instead</a>
</x-guest-layout>
