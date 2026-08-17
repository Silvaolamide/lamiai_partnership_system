<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-semibold text-xl text-gray-800 leading-tight">Business Applications</h2><p class="text-sm text-gray-500 mt-1">Approve businesses before they can start building affiliate programs.</p></div>
            <a href="{{route('admin.settings')}}" class="rounded-xl border px-4 py-2 text-sm font-bold">Approval settings</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))<div class="mb-5 rounded-xl bg-emerald-50 text-emerald-700 px-4 py-3">{{session('success')}}</div>@endif
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500"><tr><th class="text-left p-4">Business</th><th class="text-left p-4">Contact</th><th class="text-left p-4">Email</th><th class="text-left p-4">Email verification</th><th class="text-left p-4">Approval</th><th class="text-right p-4">Action</th></tr></thead>
                        <tbody class="divide-y">
                        @forelse($businesses as $business)
                            <tr>
                                <td class="p-4"><b>{{ $business->business_name ?: $business->name }}</b><p class="text-xs text-gray-400">{{ $business->business_industry ?: 'Business profile not completed' }}</p></td>
                                <td class="p-4">{{ $business->name }}<p class="text-xs text-gray-400">{{ $business->business_phone ?: '—' }}</p></td>
                                <td class="p-4">{{ $business->email }}</td>
                                <td class="p-4"><span class="rounded-full px-2 py-1 text-xs font-bold {{ $business->hasVerifiedEmail() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $business->hasVerifiedEmail() ? 'VERIFIED' : 'UNVERIFIED' }}</span></td>
                                <td class="p-4"><span class="rounded-full px-2 py-1 text-xs font-bold {{ $business->business_super_admin_approved_at ? 'bg-emerald-100 text-emerald-700' : ($business->business_rejected_at ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $business->business_super_admin_approved_at ? 'APPROVED' : ($business->business_rejected_at ? 'REJECTED' : 'PENDING') }}</span></td>
                                <td class="p-4 text-right">
                                    @if(!$business->business_super_admin_approved_at)
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" action="{{route('admin.businesses.approve',$business)}}">@csrf @method('PATCH')<button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white">Approve</button></form>
                                            <form method="POST" action="{{route('admin.businesses.reject',$business)}}">@csrf @method('PATCH')<button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600">Reject</button></form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">Approved</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-12 text-center text-gray-500">No business applications yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-6">{{$businesses->links()}}</div>
        </div>
    </div>
</x-app-layout>
