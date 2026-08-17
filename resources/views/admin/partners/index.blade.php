<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-semibold text-xl text-gray-800 leading-tight">Partners</h2><p class="text-sm text-gray-500 mt-1">Review partner applications and approval requirements.</p></div>
            <a href="{{route('admin.settings')}}" class="rounded-xl border px-4 py-2 text-sm font-bold">Approval settings</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))<div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6">{{ session('error') }}</div>@endif
            <div class="bg-white border rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto"><table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500"><tr><th class="text-left px-5 py-4">Partner</th><th class="text-left px-5 py-4">Program</th><th class="text-left px-5 py-4">Requirements</th><th class="text-left px-5 py-4">Status</th><th class="text-left px-5 py-4">Action</th></tr></thead>
                    <tbody class="divide-y">
                    @forelse($partners as $partner)
                        @php($businessRequired = (bool)($partner->program?->settings['partner_business_approval_required'] ?? false))
                        <tr>
                            <td class="px-5 py-4"><div class="font-medium">{{ $partner->user->name }}</div><div class="text-sm text-gray-500">{{ $partner->user->email }}</div></td>
                            <td class="px-5 py-4">{{ $partner->program->name }}</td>
                            <td class="px-5 py-4"><div class="space-y-1 text-xs"><span class="inline-block rounded-full px-2 py-1 {{ $partner->user->hasVerifiedEmail() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">Email {{ $partner->user->hasVerifiedEmail() ? 'verified' : 'pending' }}</span><span class="inline-block rounded-full px-2 py-1 {{ $superAdminApprovalRequired ? ($partner->super_admin_approved_at ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') : 'bg-slate-100 text-slate-500' }}">Super Admin {{ $superAdminApprovalRequired ? ($partner->super_admin_approved_at ? 'approved' : 'pending') : 'not required' }}</span><span class="inline-block rounded-full px-2 py-1 {{ $businessRequired ? ($partner->business_approved_at ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') : 'bg-slate-100 text-slate-500' }}">Business {{ $businessRequired ? ($partner->business_approved_at ? 'approved' : 'pending') : 'not required' }}</span></div></td>
                            <td class="px-5 py-4">{{ ucfirst($partner->status) }}</td>
                            <td class="px-5 py-4">
                                @if($partner->status === 'pending' && $superAdminApprovalRequired)
                                    <div class="flex gap-2"><form method="POST" action="{{route('admin.partners.approve',$partner)}}">@csrf @method('PATCH')<button class="bg-green-600 text-white px-3 py-2 rounded-lg text-xs font-bold">Approve</button></form><form method="POST" action="{{route('admin.partners.reject',$partner)}}">@csrf @method('PATCH')<button class="bg-red-50 text-red-600 border border-red-200 px-3 py-2 rounded-lg text-xs font-bold">Reject</button></form></div>
                                @elseif($partner->status === 'pending')<span class="text-xs text-gray-500">Super Admin approval is off</span>
                                @else<span class="text-gray-500">No action</span>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">No partners found.</td></tr>
                    @endforelse
                    </tbody>
                </table></div>
            </div>
            <div class="mt-6">{{ $partners->links() }}</div>
        </div>
    </div>
</x-app-layout>
