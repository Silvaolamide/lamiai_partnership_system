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
            @if($isCustomer) Create your account
            @elseif($isPartner) Become a Partner
            @elseif($isBusiness) Create your Business Account
            @else Create an account
            @endif
        </h1>
        <p class="mt-1 text-sm text-gray-600">
            @if($isCustomer)
                Create your account to continue with your purchase.
            @elseif($isPartner)
                Create your account to continue your partner application.
            @elseif($isBusiness)
                Create your account to begin your business onboarding.
            @else
                Create your account to get started.
            @endif
        </p>
    </div>

    <form method="POST" action="{{ route('register', $authQuery) }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login', $authQuery) }}">{{ __('Already registered?') }}</a>
            <x-primary-button class="ms-4">{{ __('Create Account') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
