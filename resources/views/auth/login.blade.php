<x-guest-layout>
    <div class="mb-8">
        <div class="mb-3 inline-flex items-center rounded-full bg-violet-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-violet-700">Business portal</div>
        <h2 class="text-3xl font-extrabold tracking-tight text-[#171323]">Welcome back.</h2>
        <p class="mt-2 text-sm leading-6 text-gray-500">Sign in to manage your affiliate programs, partners and performance.</p>
    </div>

    <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">We couldn't sign you in.</p>
            <p class="mt-1 text-red-600">Please check your details and try again.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('business.login') }}" class="space-y-5">
        @csrf
        <div>
            <x-input-label for="email" value="Business email" class="mb-2 font-semibold text-gray-700" />
            <x-text-input id="email" class="brand-input block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <x-input-label for="password" value="Password" class="font-semibold text-gray-700" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-violet-700 hover:text-violet-900">Forgot password?</a>
                @endif
            </div>
            <x-text-input id="password" class="brand-input block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="flex cursor-pointer items-center gap-3 text-sm text-gray-500">
            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-violet-700 shadow-sm focus:ring-violet-600" name="remember">
            <span>Keep me signed in</span>
        </label>

        <button type="submit" class="brand-btn flex w-full items-center justify-center rounded-xl px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-violet-700/15">
            Sign in to business portal <span class="ml-2 text-lg">→</span>
        </button>
    </form>

    <div class="my-7 flex items-center gap-4"><div class="h-px flex-1 bg-gray-100"></div><span class="text-xs font-medium text-gray-400">NEW BUSINESS?</span><div class="h-px flex-1 bg-gray-100"></div></div>
    <a href="{{ route('business.register') }}" class="flex w-full items-center justify-center rounded-xl border border-gray-200 px-5 py-3.5 text-sm font-bold text-gray-700 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700">Create a business account</a>

    <p class="mt-6 text-center text-xs text-gray-400">Buying a product? <a href="{{ route('customer.login') }}" class="font-semibold text-violet-700">Customer login</a>.</p>
</x-guest-layout>
