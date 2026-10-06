<x-app-layout>
<x-slot name="header"><div><h2 class="font-black text-xl text-gray-900">Social Follow — Accounts</h2><p class="text-sm text-gray-500 mt-1">Configure the social accounts or groups customers should follow or join.</p></div></x-slot>
<div class="py-8"><div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
@if(session('success'))<div class="mb-5 rounded-2xl bg-emerald-50 p-4 font-bold text-emerald-800">{{session('success')}}</div>@endif
@if($errors->any())<div class="mb-5 rounded-2xl bg-red-50 p-4 text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{route('business.social-follow.accounts.save')}}" class="space-y-5">@csrf
@php($meta=[
'youtube'=>['YouTube','▶','Your channel URL, e.g. https://youtube.com/@yourchannel','Channel URL'],
'tiktok'=>['TikTok','♪','Your TikTok profile URL','Profile URL'],
'instagram'=>['Instagram','◎','Your Instagram profile URL','Profile URL'],
'facebook'=>['Facebook','f','Your Facebook profile URL','Profile URL'],
'telegram_group'=>['Telegram Group','✈','Your Telegram group invite URL, e.g. https://t.me/yourgroup','Group Invite URL'],
'whatsapp_group'=>['WhatsApp Group','◉','Your WhatsApp group invite URL, e.g. https://chat.whatsapp.com/...','Group Invite URL']])
@foreach($platforms as $platform)
@php($account=$accounts->get($platform))
<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex flex-col gap-5 md:flex-row md:items-start"><div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-slate-900 text-xl font-black text-white">{{$meta[$platform][1]}}</div><div class="min-w-0 flex-1"><div class="flex flex-wrap items-center justify-between gap-3"><div><h3 class="text-lg font-black">{{$meta[$platform][0]}}</h3><p class="text-sm text-slate-500">{{$meta[$platform][2]}}</p></div><label class="flex items-center gap-2 text-sm font-bold"><input type="hidden" name="accounts[{{$platform}}][enabled]" value="0"><input type="checkbox" name="accounts[{{$platform}}][enabled]" value="1" @checked($account?->is_enabled) class="rounded border-slate-300 text-teal-700 focus:ring-teal-600"> Enable</label></div><div class="mt-4 grid gap-4 md:grid-cols-2"><div><label class="text-xs font-black uppercase tracking-wider text-slate-400">Handle / Name (optional)</label><input name="accounts[{{$platform}}][handle]" value="{{old('accounts.'.$platform.'.handle',$account?->handle)}}" placeholder="@yourhandle or Group name" class="mt-2 w-full rounded-xl border-slate-200"></div><div><label class="text-xs font-black uppercase tracking-wider text-slate-400">{{$meta[$platform][3]}}</label><input type="url" required name="accounts[{{$platform}}][profile_url]" value="{{old('accounts.'.$platform.'.profile_url',$account?->profile_url)}}" placeholder="https://..." class="mt-2 w-full rounded-xl border-slate-200"></div></div>@if($platform==='youtube')<p class="mt-3 text-xs font-semibold text-red-600">YouTube links will automatically use <code>?sub_confirmation=1</code> when the customer clicks Subscribe.</p>@endif</div></div></div>
@endforeach
<div class="flex flex-wrap justify-between gap-3"><a href="{{route('business.dashboard')}}" class="rounded-xl px-5 py-3 font-bold text-slate-500">Back to dashboard</a><button class="rounded-xl bg-teal-700 px-6 py-3 font-black text-white hover:bg-teal-800">Save social accounts</button></div>
</form></div></div>
</x-app-layout>
