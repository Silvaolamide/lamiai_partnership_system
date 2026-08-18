<x-guest-layout>
    <div class="mb-8">
        <div class="mb-3 inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-orange-600">Start growing</div>
        <h2 class="text-3xl font-extrabold tracking-tight text-[#171323]">Create your business account.</h2>
        <p class="mt-2 text-sm leading-6 text-gray-500">Launch an affiliate program and turn your network into a predictable sales channel.</p>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">A few details need your attention.</p>
            <ul class="mt-1 list-inside list-disc text-red-600">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf
        <div>
            <x-input-label for="name" value="Your name" class="mb-2 font-semibold text-gray-700" />
            <x-text-input id="name" class="brand-input block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="e.g. Olamide Agunkejoye" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Business email" class="mb-2 font-semibold text-gray-700" />
            <x-text-input id="email" class="brand-input block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="password" value="Password" class="mb-2 font-semibold text-gray-700" />
                <x-text-input id="password" class="brand-input block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm" type="password" name="password" required autocomplete="new-password" placeholder="8+ characters" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password_confirmation" value="Confirm password" class="mb-2 font-semibold text-gray-700" />
                <x-text-input id="password_confirmation" class="brand-input block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3.5 text-sm" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <p class="text-xs leading-5 text-gray-400">By creating an account, you agree to use AIPM responsibly and to the platform's terms.</p>

        <button type="submit" class="brand-btn flex w-full items-center justify-center rounded-xl px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-violet-700/15">
            Create business account <span class="ml-2 text-lg">→</span>
        </button>
    </form>

    <div class="my-7 flex items-center gap-4"><div class="h-px flex-1 bg-gray-100"></div><span class="text-xs font-medium text-gray-400">ALREADY A MEMBER?</span><div class="h-px flex-1 bg-gray-100"></div></div>
    <a href="{{ route('login') }}" class="flex w-full items-center justify-center rounded-xl border border-gray-200 px-5 py-3.5 text-sm font-bold text-gray-700 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700">Sign in instead</a>
</x-guest-layout>
