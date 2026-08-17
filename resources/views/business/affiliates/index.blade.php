@extends('business.portal')
@section('title','Affiliates')
@section('heading','Affiliates')
@section('content')
<div class="mb-6">
    <p class="text-slate-500">Manage the people selling products through your affiliate programs.</p>
</div>

<div class="bg-white border rounded-2xl shadow-soft overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                <tr>
                    <th class="text-left p-4">Affiliate</th>
                    <th class="text-left p-4">Program</th>
                    <th class="text-left p-4">Code</th>
                    <th class="text-left p-4">Orders</th>
                    <th class="text-left p-4">Commissions</th>
                    <th class="text-left p-4">Status</th>
                    <th class="text-right p-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($affiliates as $affiliate)
                    @php($businessApproval = (bool) ($affiliate->program?->settings['partner_business_approval_required'] ?? false))
                    <tr class="border-t">
                        <td class="p-4">
                            <b>{{ $affiliate->user?->name ?? 'Unknown' }}</b>
                            <p class="text-xs text-slate-400">{{ $affiliate->user?->email }}</p>
                        </td>
                        <td class="p-4">{{ $affiliate->program?->name }}</td>
                        <td class="p-4 font-mono text-xs">{{ $affiliate->partner_code }}</td>
                        <td class="p-4 font-bold">{{ $affiliate->orders_count }}</td>
                        <td class="p-4">{{ $affiliate->commissions_count }}</td>
                        <td class="p-4">
                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $affiliate->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($affiliate->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ strtoupper($affiliate->status) }}
                            </span>
                            @if($businessApproval && $affiliate->status === 'pending')
                                <p class="text-[11px] text-slate-400 mt-2">
                                    {{ $affiliate->business_approved_at ? 'Business approved' : 'Business approval required' }}
                                    · {{ $affiliate->super_admin_approved_at ? 'Super admin approved' : 'Super admin approval required' }}
                                </p>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            @if($businessApproval && $affiliate->status === 'pending')
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('business.affiliates.approve', [$affiliate->program, $affiliate]) }}">
                                        @csrf @method('PATCH')
                                        <button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('business.affiliates.reject', [$affiliate->program, $affiliate]) }}">
                                        @csrf @method('PATCH')
                                        <button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600">Reject</button>
                                    </form>
                                </div>
                            @elseif(!$businessApproval && $affiliate->status === 'pending')
                                <span class="text-xs text-slate-400">Waiting for super admin</span>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-12 text-center text-slate-500">No affiliates have joined your programs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-6">{{ $affiliates->links() }}</div>
@endsection
