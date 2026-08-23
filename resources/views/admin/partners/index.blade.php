<x-app-layout>
<x-slot name="header"><div><p class="text-xs font-black uppercase tracking-widest text-blue-600">People & network</p><h2 class="font-black text-2xl text-gray-900">Partners</h2><p class="text-sm text-gray-500 mt-1">One row per person. Program memberships and applications are shown inside each partner record.</p></div></x-slot>
<div class="py-6 bg-slate-50 min-h-screen"><div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">@include('admin.partials.command-nav')
@if(session('success'))<div class="mb-4 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-700">{{session('success')}}</div>@endif @if(session('error'))<div class="mb-4 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-rose-700">{{session('error')}}</div>@endif
<div class="rounded-2xl border bg-white shadow-sm overflow-hidden"><div class="p-5 border-b flex items-center justify-between"><div><h3 class="font-black">Partner directory</h3><p class="text-sm text-slate-500">Partner = unique account. A program enrollment/application is a separate relationship and never creates another partner.</p></div><a href="{{route('network.index')}}" class="rounded-xl border px-4 py-2 text-sm font-black">Network tree →</a></div>
<div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="p-4 text-left">Partner</th><th class="p-4 text-left">Programs / applications</th><th class="p-4 text-left">Recruiter</th><th class="p-4">Programs</th><th class="p-4">Recruits</th><th class="p-4 text-right">Sales</th><th class="p-4 text-right">Earnings</th></tr></thead><tbody class="divide-y">
@forelse($partners as $partner)
<tr class="hover:bg-blue-50/30 align-top">
<td class="p-4"><div class="font-black">{{ $partner->name }}</div><div class="text-xs text-slate-400">{{ $partner->email }}</div></td>
<td class="p-4 min-w-[360px]"><div class="space-y-2">
@foreach($partner->admin_memberships as $membership)
<div class="rounded-xl border bg-slate-50 p-3"><div class="flex items-start justify-between gap-3"><div><div class="font-bold">{{ $membership->program?->name ?? 'Unknown program' }}</div><div class="text-xs text-slate-500">Enrollment #{{ $membership->id }} · {{ ucfirst($membership->status) }}</div></div><span class="rounded-full px-2 py-1 text-[11px] font-black {{ $membership->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($membership->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">{{ ucfirst($membership->status) }}</span></div>
@if($membership->status === 'pending')<div class="mt-2 flex flex-wrap gap-2">
@if($superAdminApprovalRequired && !$membership->super_admin_approved_at)<form method="POST" action="{{route('admin.partners.approve',$membership)}}">@csrf @method('PATCH')<button class="rounded-lg bg-emerald-600 text-white px-3 py-2 text-xs font-black">Approve platform</button></form>@endif
@if(($membership->program?->settings['partner_business_approval_required'] ?? false) && !$membership->business_approved_at)<form method="POST" action="{{route('admin.partners.approve-for-business',$membership)}}">@csrf @method('PATCH')<button class="rounded-lg bg-blue-600 text-white px-3 py-2 text-xs font-black">Approve for business</button></form>@endif
<form method="POST" action="{{route('admin.partners.reject',$membership)}}">@csrf @method('PATCH')<button class="rounded-lg border border-rose-200 bg-rose-50 text-rose-700 px-3 py-2 text-xs font-black">Reject application</button></form>
</div>@endif
</div>
@endforeach
</div></td>
<td class="p-4">@php $recruiters = $partner->admin_memberships->map(fn($m) => $m->parentPartner?->user?->name)->filter()->unique()->values(); @endphp{{ $recruiters->isEmpty() ? 'Direct / none' : $recruiters->join(', ') }}</td>
<td class="p-4 text-center"><div class="font-black">{{ $partner->admin_metrics['active_programs'] }}</div><div class="text-xs text-slate-400">active</div>@if($partner->admin_metrics['pending_programs'])<div class="text-xs font-bold text-amber-600 mt-1">{{ $partner->admin_metrics['pending_programs'] }} pending</div>@endif</td>
<td class="p-4 text-center font-black">{{ $partner->admin_metrics['recruits'] }}</td>
<td class="p-4 text-right font-black">₦{{ number_format($partner->admin_metrics['sales'],2) }}<small class="block text-xs text-slate-400">{{ $partner->admin_metrics['orders'] }} orders</small></td>
<td class="p-4 text-right font-black text-violet-700">₦{{ number_format($partner->admin_metrics['earnings'],2) }}</td>
</tr>
@empty<tr><td colspan="7" class="p-10 text-center text-slate-400">No partners found.</td></tr>@endforelse</tbody></table></div><div class="p-4">{{$partners->links()}}</div></div>
</div></div></x-app-layout>
