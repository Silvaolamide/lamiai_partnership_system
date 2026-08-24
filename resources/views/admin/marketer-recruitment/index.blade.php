<x-app-layout>
    <x-slot name="header"><div><h2 class="font-semibold text-xl text-gray-800 leading-tight">Marketer Recruitment</h2><p class="text-sm text-gray-500 mt-1">Manage your recruitment redirect and review leads from your ads.</p></div></x-slot>
    <div class="py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))<div class="rounded-xl bg-emerald-50 px-4 py-3 text-emerald-700">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-xl bg-red-50 px-4 py-3 text-red-700"><ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="grid gap-6 lg:grid-cols-[.8fr_1.2fr]">
            <form method="POST" action="{{ route('admin.marketer-recruitment.settings') }}" class="rounded-2xl border border-teal-100 bg-white p-6 shadow-sm h-fit">
                @csrf @method('PUT')
                <div class="flex items-start gap-3"><div class="grid h-10 w-10 place-items-center rounded-xl bg-teal-50 text-teal-700">↗</div><div><h3 class="font-black text-gray-900">After-submit redirect</h3><p class="mt-1 text-sm leading-6 text-gray-500">Applicants are redirected here immediately after a successful submission.</p></div></div>
                <label class="mt-6 block text-sm font-bold text-gray-800">Redirect URL</label>
                <input type="url" name="redirect_url" value="{{ old('redirect_url', $redirectUrl) }}" placeholder="https://wa.me/234..." class="mt-2 w-full rounded-xl border-gray-300 focus:border-teal-600 focus:ring-teal-600">
                <p class="mt-2 text-xs text-gray-400">Examples: WhatsApp, a thank-you page, booking page or application group.</p>
                <button class="mt-5 rounded-xl bg-teal-700 px-5 py-3 text-sm font-black text-white hover:bg-teal-800">Save redirect</button>
            </form>
            <div class="rounded-2xl border border-teal-100 bg-teal-900 p-6 text-white shadow-sm">
                <p class="text-xs font-black uppercase tracking-[.18em] text-teal-300">Lead funnel</p><p class="mt-3 text-4xl font-black">{{ number_format($leadCount) }}</p><p class="mt-1 text-teal-100/70">total marketer applications</p>
                <div class="mt-7 grid gap-3 sm:grid-cols-3"><div class="rounded-xl bg-white/10 p-4"><b>6</b><p class="mt-1 text-xs text-teal-100/70">questions</p></div><div class="rounded-xl bg-white/10 p-4"><b>1 min</b><p class="mt-1 text-xs text-teal-100/70">target completion</p></div><div class="rounded-xl bg-white/10 p-4"><b>UTM</b><p class="mt-1 text-xs text-teal-100/70">ad tracking</p></div></div>
            </div>
        </div>
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b px-6 py-5"><h3 class="font-black text-gray-900">Recent applications</h3><p class="mt-1 text-sm text-gray-500">Newest leads first.</p></div>
            <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500"><tr><th class="px-6 py-3">Applicant</th><th class="px-6 py-3">WhatsApp</th><th class="px-6 py-3">Experience</th><th class="px-6 py-3">Sales</th><th class="px-6 py-3">Source</th><th class="px-6 py-3">Date</th></tr></thead><tbody class="divide-y divide-gray-100">@forelse($leads as $lead)<tr class="hover:bg-teal-50/40"><td class="px-6 py-4"><div class="font-bold text-gray-900">{{ $lead->name }}</div><div class="text-xs text-gray-500">{{ $lead->email }}</div><div class="mt-1 max-w-xs truncate text-xs text-gray-400">{{ $lead->what_sold ?: '—' }}</div></td><td class="px-6 py-4 whitespace-nowrap">{{ $lead->whatsapp_number }}</td><td class="px-6 py-4">{{ $lead->has_sold_online ? 'Yes' : 'No' }}</td><td class="px-6 py-4">{{ $lead->sales_result ? ucwords(str_replace('_', ' ', $lead->sales_result)) : '—' }}</td><td class="px-6 py-4">{{ $lead->utm_source ?: 'Direct' }}{{ $lead->utm_campaign ? ' · '.$lead->utm_campaign : '' }}</td><td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $lead->created_at->format('d M Y, H:i') }}</td></tr>@empty<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No marketer applications yet.</td></tr>@endforelse</tbody></table></div>
            @if($leads->hasPages())<div class="border-t px-6 py-4">{{ $leads->links() }}</div>@endif
        </div>
    </div></div>
</x-app-layout>
