<x-app-layout>
<x-slot name="header">
    <div>
        <p class="text-xs font-black uppercase tracking-widest text-violet-600">Platform control</p>
        <h2 class="font-black text-2xl text-gray-900">Registration Center</h2>
        <p class="text-sm text-gray-500 mt-1">See every non-admin account created through the platform and resolve incomplete registration issues.</p>
    </div>
</x-slot>

<div class="py-6 bg-slate-50 min-h-screen">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
        @include('admin.partials.command-nav')

        @if(session('success'))
            <div class="mb-4 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-700 font-semibold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-rose-700 font-semibold">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
            <div class="rounded-2xl bg-white border p-4"><span class="text-xs text-slate-400">Registered accounts</span><b class="block text-2xl mt-1">{{ $users->total() }}</b></div>
            <div class="rounded-2xl bg-white border p-4"><span class="text-xs text-slate-400">Needs attention</span><b class="block text-2xl mt-1 text-amber-600">{{ $users->getCollection()->filter(fn($u) => ! $u->email_verified_at || ! $u->hasRole('program_manager') || ! $u->business_super_admin_approved_at)->count() }}</b></div>
            <div class="rounded-2xl bg-white border p-4"><span class="text-xs text-slate-400">Verified on page</span><b class="block text-2xl mt-1 text-emerald-600">{{ $users->getCollection()->whereNotNull('email_verified_at')->count() }}</b></div>
            <div class="rounded-2xl bg-white border p-4"><span class="text-xs text-slate-400">Business role on page</span><b class="block text-2xl mt-1 text-violet-600">{{ $users->getCollection()->filter(fn($u) => $u->hasRole('program_manager'))->count() }}</b></div>
        </div>

        <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
            <div class="p-5 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h3 class="font-black">Registration recovery queue</h3>
                    <p class="text-sm text-slate-500">Fix role, email verification and business approval problems without touching the database manually.</p>
                </div>
                <a href="{{ route('admin.businesses.index') }}" class="rounded-xl bg-violet-600 text-white px-4 py-2 text-sm font-black">Business directory →</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="p-4 text-left">User</th>
                        <th class="p-4 text-left">Email</th>
                        <th class="p-4">Email status</th>
                        <th class="p-4">Business role</th>
                        <th class="p-4">Approval</th>
                        <th class="p-4 text-left">Registration state</th>
                        <th class="p-4 text-left">Admin actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($users as $user)
                        @php
                            $verified = (bool) $user->email_verified_at;
                            $businessRole = $user->hasRole('program_manager');
                            $approved = (bool) $user->business_super_admin_approved_at;
                            $rejected = (bool) $user->business_rejected_at;
                            $complete = $verified && $businessRole && $approved;
                        @endphp
                        <tr class="hover:bg-violet-50/40 align-top">
                            <td class="p-4"><div class="font-black">{{ $user->business_name ?: $user->name }}</div><div class="text-xs text-slate-400">ID #{{ $user->id }} · {{ $user->created_at?->format('d M Y H:i') }}</div></td>
                            <td class="p-4 text-slate-600">{{ $user->email }}</td>
                            <td class="p-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $verified ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $verified ? 'Verified' : 'Not verified' }}</span></td>
                            <td class="p-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $businessRole ? 'bg-violet-100 text-violet-700' : 'bg-rose-100 text-rose-700' }}">{{ $businessRole ? 'Program manager' : 'Missing' }}</span></td>
                            <td class="p-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $approved ? 'bg-emerald-100 text-emerald-700' : ($rejected ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">{{ $approved ? 'Approved' : ($rejected ? 'Rejected' : 'Pending') }}</span></td>
                            <td class="p-4 min-w-[210px]">
                                @if($complete)
                                    <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700">✓ Registration complete</span>
                                @else
                                    <div class="rounded-xl bg-amber-50 border border-amber-100 p-3 text-xs text-amber-800"><div class="font-black mb-1">Needs attention</div><ul class="space-y-1 list-disc list-inside">@unless($verified)<li>Email verification</li>@endunless @unless($businessRole)<li>Business role</li>@endunless @unless($approved)<li>Super admin approval</li>@endunless</ul></div>
                                @endif
                            </td>
                            <td class="p-4 min-w-[320px]">
                                <div class="flex flex-wrap gap-2">
                                    @unless($verified)
                                        <form method="POST" action="{{ route('admin.registrations.verify-email', $user) }}">@csrf @method('PATCH')<button class="rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-xs font-black text-emerald-700">Verify email</button></form>
                                        <form method="POST" action="{{ route('admin.registrations.resend-verification', $user) }}">@csrf <button class="rounded-lg bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-black text-slate-700">Resend email</button></form>
                                    @endunless
                                    @unless($businessRole)
                                        <form method="POST" action="{{ route('admin.registrations.assign-business-role', $user) }}">@csrf @method('PATCH')<button class="rounded-lg bg-violet-50 border border-violet-200 px-3 py-2 text-xs font-black text-violet-700">Assign business role</button></form>
                                    @endunless
                                    @unless($approved)
                                        <form method="POST" action="{{ route('admin.registrations.approve', $user) }}">@csrf @method('PATCH')<button class="rounded-lg bg-emerald-600 text-white px-3 py-2 text-xs font-black">Approve business</button></form>
                                    @endunless
                                    @if(!$complete)
                                        <form method="POST" action="{{ route('admin.registrations.repair', $user) }}" onsubmit="return confirm('Repair this registration by verifying the email, assigning the business role and approving the business?')">@csrf @method('PATCH')<button class="rounded-lg bg-slate-900 text-white px-3 py-2 text-xs font-black">Repair all</button></form>
                                    @endif
                                    @if(!$complete)
                                        <form method="POST" action="{{ route('admin.registrations.destroy', $user) }}" onsubmit="return confirm('Permanently delete {{ addslashes($user->email) }}? This cannot be undone. The deletion will only proceed if this registration has no associated business activity.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-50 border border-rose-200 px-3 py-2 text-xs font-black text-rose-700 hover:bg-rose-100">Delete registration</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-10 text-center text-slate-400">No registered accounts found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $users->links() }}</div>
        </div>
    </div>
</div>
</x-app-layout>
