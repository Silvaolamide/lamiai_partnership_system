<x-app-layout>
<x-slot name="header"><div class="flex items-center justify-between gap-4"><div><h2 class="font-black text-xl text-gray-900">Marketing Campaigns</h2><p class="text-sm text-gray-500 mt-1">Create ad-ready lead funnels and choose where qualified leads go.</p></div><div class="flex flex-wrap gap-2"><a href="{{ route('business.social-follow.campaigns.index') }}" class="rounded-xl border border-teal-200 bg-teal-50 px-5 py-3 text-sm font-black text-teal-700">Social Follow</a><a href="{{ route('business.campaigns.create') }}" class="rounded-xl bg-teal-700 px-5 py-3 text-sm font-black text-white hover:bg-teal-800">+ Create campaign</a></div></div></x-slot>
<div class="py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
@if(session('success'))<div class="rounded-xl bg-teal-50 px-4 py-3 text-teal-800 font-semibold">{{ session('success') }}</div>@endif
<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
@forelse($campaigns as $campaign)
<div class="rounded-2xl border border-teal-100 bg-white p-6 shadow-sm">
<div class="flex items-start justify-between gap-3"><div><p class="text-xs font-black uppercase tracking-wider text-teal-700">{{ $campaign->status }}</p><h3 class="mt-2 text-lg font-black text-gray-900">{{ $campaign->name }}</h3></div><span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-black text-teal-700">{{ $campaign->leads_count }} leads</span></div>
<p class="mt-3 text-sm text-gray-500 line-clamp-2">{{ $campaign->headline }}</p>
<div class="mt-5 rounded-xl bg-slate-50 p-3"><p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Campaign link</p><a target="_blank" href="{{ url('/campaign/'.$campaign->slug) }}" class="mt-1 block truncate text-sm font-bold text-teal-700">{{ url('/campaign/'.$campaign->slug) }}</a></div>
<div class="mt-5 flex flex-wrap gap-2"><a href="{{ route('business.campaigns.edit', $campaign) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700">Edit</a><a href="{{ route('business.campaigns.leads', $campaign) }}" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-bold text-white">View leads</a><form method="POST" action="{{ route('business.campaigns.toggle', $campaign) }}">@csrf @method('PATCH')<button class="rounded-lg border border-teal-200 px-3 py-2 text-sm font-bold text-teal-700">{{ $campaign->status === 'active' ? 'Pause' : 'Activate' }}</button></form></div>
</div>
@empty
<div class="md:col-span-2 xl:col-span-3 rounded-3xl border-2 border-dashed border-teal-200 bg-teal-50/40 px-6 py-16 text-center"><div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-teal-100 text-2xl text-teal-700">↗</div><h3 class="mt-5 text-xl font-black">Create your first campaign</h3><p class="mx-auto mt-2 max-w-lg text-sm text-gray-500">Build the same six-step qualification funnel you just saw, give it a destination URL, and send traffic from your ads.</p><a href="{{ route('business.campaigns.create') }}" class="mt-6 inline-block rounded-xl bg-teal-700 px-5 py-3 text-sm font-black text-white">Create campaign</a></div>
@endforelse
</div>
@if($campaigns->hasPages())<div>{{ $campaigns->links() }}</div>@endif
</div></div>
</x-app-layout>
