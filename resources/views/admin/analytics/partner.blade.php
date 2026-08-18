@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1500px]">
        <a href="{{ route('admin.analytics.business', $business) }}" class="mb-5 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:border-violet-200 hover:text-violet-700">← Business portfolio</a>
        <div class="mb-6 flex flex-col gap-2"><p class="text-[10px] font-black uppercase tracking-[0.22em] text-violet-600">Partner intelligence</p><h1 class="text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">{{ $programPartner->user->name }}</h1><p class="text-sm text-slate-500">{{ $programPartner->user->email }} · {{ $programPartner->program->name }}</p></div>

        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach([['Sales generated','₦'.number_format($orders->sum('total'),2),'violet'],['Sales count',$orders->count(),'blue'],['Partners recruited',$recruited->count(),'emerald']] as $stat)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $stat[0] }}</span><div class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $stat[1] }}</div></div>
            @endforeach
        </div>

        <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-black text-slate-950">Partners recruited by {{ $programPartner->user->name }}</h2><p class="text-xs text-slate-400">Direct recruits in this partner's network</p></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3 text-left">Partner</th><th class="px-5 py-3 text-left">Email</th><th class="px-5 py-3 text-left">Program</th><th class="px-5 py-3 text-left">Joined</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($recruited as $child)<tr class="transition hover:bg-violet-50/30"><td class="px-5 py-4 font-black text-slate-900">{{ $child->user->name }}</td><td class="px-5 py-4 text-slate-500">{{ $child->user->email }}</td><td class="px-5 py-4 font-semibold">{{ $child->program->name }}</td><td class="px-5 py-4 text-slate-500">{{ optional($child->created_at)->format('d M Y') }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">No recruited partners.</td></tr>@endforelse</tbody></table></div></section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-black text-slate-950">Sales made by this partner</h2><p class="text-xs text-slate-400">Orders attributed to this partner</p></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3 text-left">Order</th><th class="px-5 py-3 text-left">Customer</th><th class="px-5 py-3 text-left">Amount</th><th class="px-5 py-3 text-left">Date</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($orders as $order)<tr class="transition hover:bg-violet-50/30"><td class="px-5 py-4 font-black">{{ $order->order_number }}</td><td class="px-5 py-4">{{ optional($order->customer)->name ?? $order->customer_email }}</td><td class="px-5 py-4 font-black">₦{{ number_format($order->total,2) }}</td><td class="px-5 py-4 text-slate-500">{{ $order->created_at->format('d M Y H:i') }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">No sales.</td></tr>@endforelse</tbody></table></div></section>
    </div>
</div>
@endsection
