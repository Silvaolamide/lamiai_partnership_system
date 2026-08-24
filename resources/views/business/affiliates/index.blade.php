@extends('business.portal')
@section('title','Affiliates')
@section('heading','Affiliates')
@section('content')
<div class="mb-6">
    <p class="text-slate-500">Manage unique people selling through your affiliate programs. Each program enrollment is shown separately under the partner, including the products available through that program.</p>
</div>

<div class="bg-white border rounded-2xl shadow-soft overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                <tr>
                    <th class="text-left p-4">Partner</th>
                    <th class="text-left p-4">Program enrollments & products</th>
                    <th class="text-left p-4">Orders</th>
                    <th class="text-left p-4">Commissions</th>
                    <th class="text-left p-4">Programs</th>
                </tr>
            </thead>
            <tbody>
                @forelse($affiliates as $affiliate)
                    <tr class="border-t align-top">
                        <td class="p-4">
                            <b>{{ $affiliate->name }}</b>
                            <p class="text-xs text-slate-400">{{ $affiliate->email }}</p>
                        </td>
                        <td class="p-4 min-w-[520px]">
                            <div class="space-y-3">
                                @foreach($affiliate->affiliate_memberships as $membership)
                                    @php($businessApproval = (bool) ($membership->program?->settings['partner_business_approval_required'] ?? false))
                                    <div class="rounded-xl border bg-slate-50 p-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="font-bold">{{ $membership->program?->name }}</div>
                                                <div class="text-xs text-slate-400">Code: {{ $membership->partner_code }} · Enrollment #{{ $membership->id }}</div>
                                            </div>
                                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $membership->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($membership->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ strtoupper($membership->status) }}</span>
                                        </div>

                                        <div class="mt-3 rounded-lg border border-slate-200 bg-white p-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Products in this program</p>
                                                <span class="text-[11px] font-bold text-slate-400">{{ $membership->program?->products?->count() ?? 0 }}</span>
                                            </div>
                                            @if(($membership->program?->products?->count() ?? 0) > 0)
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    @foreach($membership->program->products as $product)
                                                        <span class="inline-flex items-center rounded-lg bg-teal-50 px-2.5 py-1.5 text-xs font-bold text-teal-800 ring-1 ring-inset ring-teal-100">
                                                            {{ $product->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="mt-2 text-xs text-slate-400">No products are currently attached to this program.</p>
                                            @endif
                                        </div>

                                        @if($businessApproval && $membership->status === 'pending')
                                            <p class="text-[11px] text-slate-400 mt-2">
                                                {{ $membership->business_approved_at ? 'Business approved' : 'Business approval required' }}
                                                · {{ $membership->super_admin_approved_at ? 'Super admin approved' : 'Super admin approval required' }}
                                            </p>
                                            <div class="mt-2 flex gap-2">
                                                <form method="POST" action="{{ route('business.affiliates.approve', [$membership->program, $membership]) }}">
                                                    @csrf @method('PATCH')
                                                    <button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('business.affiliates.reject', [$membership->program, $membership]) }}">
                                                    @csrf @method('PATCH')
                                                    <button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600">Reject</button>
                                                </form>
                                            </div>
                                        @elseif(!$businessApproval && $membership->status === 'pending')
                                            <span class="mt-2 block text-xs text-slate-400">Waiting for super admin approval</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="p-4 font-bold">{{ $affiliate->affiliate_metrics['orders'] }}</td>
                        <td class="p-4">{{ $affiliate->affiliate_metrics['commissions'] }}</td>
                        <td class="p-4 text-center"><div class="font-black">{{ $affiliate->affiliate_metrics['active_programs'] }}</div><div class="text-xs text-slate-400">active</div>@if($affiliate->affiliate_metrics['pending_programs'])<div class="text-xs font-bold text-amber-600">{{ $affiliate->affiliate_metrics['pending_programs'] }} pending</div>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-12 text-center text-slate-500">No partners have joined your programs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-6">{{ $affiliates->links() }}</div>
@endsection
