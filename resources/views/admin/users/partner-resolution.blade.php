@php($partners = $data['programPartners'])
<section class="rounded-3xl border bg-white p-6 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Partner resolution</p><h3 class="text-xl font-black">Programs, attribution & commissions</h3></div>
        <a href="{{ route('admin.commissions.index') }}" class="rounded-xl bg-violet-600 text-white px-4 py-2 text-sm font-black">Open commission center →</a>
    </div>
    <div class="mt-5 grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="rounded-2xl bg-slate-50 p-4"><span class="text-xs text-slate-500">Programs</span><b class="block text-2xl">{{ $partners->count() }}</b></div>
        <div class="rounded-2xl bg-emerald-50 p-4"><span class="text-xs text-emerald-700">Active</span><b class="block text-2xl text-emerald-900">{{ $partners->where('status','active')->count() }}</b></div>
        <div class="rounded-2xl bg-amber-50 p-4"><span class="text-xs text-amber-700">Pending</span><b class="block text-2xl text-amber-900">{{ $partners->where('status','pending')->count() }}</b></div>
        <div class="rounded-2xl bg-violet-50 p-4"><span class="text-xs text-violet-700">Commissions</span><b class="block text-2xl text-violet-900">{{ $data['commissionCount'] }}</b></div>
    </div>
    <div class="mt-5 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead><tr class="border-b text-slate-400"><th class="p-2 text-left">Program</th><th class="p-2">Partner code</th><th class="p-2">Status</th><th class="p-2">Joined</th><th class="p-2 text-right">Action</th></tr></thead>
            <tbody>
            @forelse($partners as $partner)
                <tr class="border-b">
                    <td class="p-3 font-bold">{{ optional($partner->program)->name ?: '—' }}</td>
                    <td class="p-3 font-mono">{{ $partner->partner_code }}</td>
                    <td class="p-3"><span class="rounded-full px-2 py-1 text-xs font-black {{ $partner->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($partner->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">{{ ucfirst($partner->status) }}</span></td>
                    <td class="p-3">{{ optional($partner->joined_at)->format('d M Y') ?: '—' }}</td>
                    <td class="p-3 text-right"><a class="font-black text-violet-700" href="{{ route('admin.commissions.index', ['partner_id' => $partner->id]) }}">Review commissions →</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-6 text-center text-slate-400">This user has no partner enrollments.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
