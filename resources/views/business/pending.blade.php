<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Business application</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8 text-center">
                @if(auth()->user()->business_rejected_at)
                    <div class="mx-auto mb-5 h-14 w-14 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-2xl">!</div>
                    <h1 class="text-2xl font-black text-slate-900">Application not approved</h1>
                    <p class="mt-3 text-slate-500">Your business onboarding application was not approved by the platform administrator. Please contact support if you believe this was a mistake.</p>
                @else
                    <div class="mx-auto mb-5 h-14 w-14 rounded-full bg-violet-50 text-violet-600 flex items-center justify-center text-2xl">✓</div>
                    <h1 class="text-2xl font-black text-slate-900">Your application is under review</h1>
                    <p class="mt-3 text-slate-500">Your email must be verified and your business must be approved by the LAMI AI Super Admin before you can continue onboarding.</p>
                    <div class="mt-6 grid sm:grid-cols-2 gap-3 text-left">
                        <div class="rounded-2xl border p-4">
                            <p class="text-xs uppercase tracking-wider text-slate-400">Email</p>
                            <p class="mt-1 font-bold {{ auth()->user()->hasVerifiedEmail() ? 'text-emerald-600' : 'text-amber-600' }}">{{ auth()->user()->hasVerifiedEmail() ? 'Verified' : 'Verification required' }}</p>
                        </div>
                        <div class="rounded-2xl border p-4">
                            <p class="text-xs uppercase tracking-wider text-slate-400">Super Admin</p>
                            <p class="mt-1 font-bold {{ auth()->user()->business_super_admin_approved_at ? 'text-emerald-600' : 'text-amber-600' }}">{{ auth()->user()->business_super_admin_approved_at ? 'Approved' : 'Approval required' }}</p>
                        </div>
                    </div>
                    @if(!auth()->user()->hasVerifiedEmail())
                        <form method="POST" action="{{ route('verification.send') }}" class="mt-6">@csrf<button class="rounded-xl bg-violet-600 px-5 py-3 text-white font-bold">Resend verification email</button></form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
