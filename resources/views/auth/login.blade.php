<x-guest-layout>
    @php
        $context = $context ?? request('context', 'general');
        $isCustomer = $context === 'customer';
        $isPartner = $context === 'partner';
        $isBusiness = $context === 'business';
        $authQuery = array_filter(request()->query(), fn ($value) => $value !== null && $value !== '');
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            @if($isCustomer) Welcome back
            @elseif($isPartner) Partner Login
            @elseif($isBusiness) Business Login
            @else Sign in
            @endif
        </h1>
        <p class="mt-1 text-sm text-gray-600">
            @if($isCustomer)
                Sign in to continue with your purchase.
            @elseif($isPartner)
                Sign in to continue your partner application.
            @elseif($isBusiness)
                Sign in to continue your business onboarding.
            @else
                Sign in to your account.
            @endif
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login', $authQuery) }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
            @endif
            <x-primary-button class="ms-3">{{ __('Log in') }}</x-primary-button>
        </div>
    </form>

    <div class="mt-6 text-center text-sm text-gray-600">
        Don't have an account?
        <a class="font-semibold text-indigo-700 hover:text-indigo-900 underline" href="{{ route('register', $authQuery) }}">Create one</a>
    </div>
</x-guest-layout>
