<x-guest-layout>
    <div class="mb-6">
        <div class="mb-3 inline-flex items-center rounded-full bg-violet-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-violet-700">Email verification</div>
        <h2 class="text-3xl font-extrabold tracking-tight text-[#171323]">Check your inbox.</h2>
        <p class="mt-2 text-sm leading-6 text-gray-500">We've sent a verification link to your registered email address.</p>
    </div>

    <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <p class="font-bold">Can't find the email?</p>
        <p class="mt-1">Please check your <strong>Spam, Junk or Promotions</strong> folder. If you find it there, mark AI Powered Marketing (AIPM) as a trusted sender.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">A new verification link has been sent to your email address.</div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">@csrf<x-primary-button>Resend Verification Email</x-primary-button></form>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="rounded-md text-sm text-gray-600 underline hover:text-gray-900">Log Out</button></form>
    </div>
</x-guest-layout>
