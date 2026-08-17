<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Partner application</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8">
                <h1 class="text-2xl font-black text-slate-900">Your partner account is being prepared</h1>
                <p class="mt-2 text-slate-500">You can access your partner dashboard as soon as the requirements configured for your program are complete.</p>
                <div class="mt-7 space-y-3">
                    @foreach(auth()->user()->programPartners()->with('program')->get() as $partner)
                        <div class="rounded-2xl border p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div><p class="font-bold">{{ $partner->program?->name }}</p><p class="text-sm text-slate-500">{{ ucfirst($partner->status) }}</p></div>
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ strtoupper($partner->status) }}</span>
                            </div>
                            <div class="grid sm:grid-cols-3 gap-3 mt-4 text-sm">
                                <div><span class="text-slate-400">Email</span><p class="font-semibold text-emerald-600">Verified</p></div>
                                <div><span class="text-slate-400">Super Admin</span><p class="font-semibold {{ $partner->super_admin_approved_at ? 'text-emerald-600' : 'text-amber-600' }}">{{ $partner->super_admin_approved_at ? 'Approved' : 'Pending' }}</p></div>
                                <div><span class="text-slate-400">Business</span><p class="font-semibold {{ $partner->business_approved_at ? 'text-emerald-600' : 'text-amber-600' }}">{{ $partner->business_approved_at ? 'Approved' : (($partner->program?->settings['partner_business_approval_required'] ?? false) ? 'Pending' : 'Not required') }}</p></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 flex gap-3"><a href="{{ route('partner.marketplace.index') }}" class="rounded-xl bg-violet-600 px-5 py-3 text-white font-bold">Browse programs</a><a href="{{ route('dashboard') }}" class="rounded-xl border px-5 py-3 font-bold">Back</a></div>
            </div>
        </div>
    </div>
</x-app-layout>
