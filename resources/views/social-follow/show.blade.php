<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $campaign->headline ?: $campaign->name }}</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-900">
<main class="mx-auto min-h-screen max-w-2xl bg-slate-50 px-4 py-8 sm:px-6">
<div class="rounded-[2rem] bg-white p-6 shadow-xl sm:p-8">
<div class="text-center">
<div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-teal-600 text-2xl text-white">★</div>
<h1 class="mt-5 text-3xl font-black tracking-tight">{{ $campaign->headline ?: $campaign->name }}</h1>
@if($campaign->description)<p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-500">{{ $campaign->description }}</p>@endif
</div>

<div class="mt-7 rounded-3xl bg-slate-900 p-5 text-white">
<div class="flex items-end justify-between gap-4">
<div><p class="text-xs font-black uppercase tracking-widest text-teal-300">Your social score</p><p class="mt-1 text-3xl font-black">{{ $score }} / {{ $campaign->socialAccounts->sum(fn($a)=>(int)($a->pivot->points ?? 1)) }}</p></div>
<div class="text-right text-xs font-bold text-slate-300">Required: {{ $campaign->minimum_score }}</div>
</div>
<div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-700"><div class="h-full rounded-full bg-teal-400 transition-all" style="width:{{ min(100,($score / max(1,$campaign->socialAccounts->sum(fn($a)=>(int)($a->pivot->points ?? 1))))*100) }}%"></div></div>
</div>

<div class="mt-6 rounded-3xl border border-teal-100 bg-teal-50 p-4">
<div class="flex gap-3">
<div class="mt-0.5 shrink-0 text-lg">👉</div>
<div>
<p class="font-black text-teal-950">Follow, then come right back</p>
<p class="mt-1 text-xs leading-5 text-teal-800">Tap a Follow button below. We'll take you to the official account. Follow it, then use your phone's Back button or return to this page and tap <b>I've followed</b>.</p>
</div>
</div>
</div>

<div class="mt-6 space-y-3">
@php($icons=['youtube'=>'▶','tiktok'=>'♪','instagram'=>'◎','facebook'=>'f'])
@foreach($campaign->socialAccounts as $account)
@php($done=in_array($account->id,$verified,true))
@php($followKey='social-follow-'.$campaign->id.'-'.$account->id)
<div class="rounded-3xl border {{ $done?'border-emerald-200 bg-emerald-50':'border-slate-200 bg-white' }} p-5">
<div class="flex items-center gap-4">
<div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl {{ $done?'bg-emerald-600':'bg-slate-900' }} font-black text-white">{{$icons[$account->platform] ?? '↗'}}</div>
<div class="min-w-0 flex-1"><p class="font-black capitalize">{{$account->platform}}</p><p class="truncate text-xs text-slate-500">{{$account->handle ?: 'Follow our official account'}}</p></div>
@if($done)<span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">✓ Done</span>@endif
</div>

<div class="mt-4 space-y-2">
<a href="{{$account->followUrl()}}" data-follow-key="{{$followKey}}" class="follow-platform block w-full rounded-xl {{ $done?'border border-emerald-200 bg-white text-emerald-700':'bg-slate-900 text-white' }} px-4 py-3 text-center text-sm font-black transition active:scale-[.99]">{{ $account->platform==='youtube'?'Subscribe on YouTube':'Follow on '.ucfirst($account->platform) }} <span class="text-xs opacity-70">→</span></a>
@unless($done)
<form method="POST" action="{{route('social-follow.claim',[$campaign->slug,$account->id])}}">
@csrf
<button type="submit" data-claim-key="{{$followKey}}" disabled autocomplete="off" class="claim-follow w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-black text-slate-400 opacity-70 transition" aria-disabled="true">I've followed — check ✓</button>
</form>
<p data-status-key="{{$followKey}}" class="follow-status hidden text-center text-[11px] font-bold text-slate-400">Welcome back. Tap “I've followed” when you're ready.</p>
@endunless
</div>
</div>
@endforeach
</div>

@if($unlocked)
<div class="mt-7 rounded-3xl border border-emerald-200 bg-emerald-50 p-6 text-center"><div class="text-3xl">🎉</div><h2 class="mt-2 text-xl font-black text-emerald-950">You're unlocked!</h2><p class="mt-1 text-sm text-emerald-800">{{ $campaign->resource_title }}</p><form method="POST" action="{{route('social-follow.unlock',$campaign->slug)}}" class="mt-5">@csrf<button class="w-full rounded-2xl bg-emerald-600 px-5 py-4 font-black text-white shadow-lg shadow-emerald-600/20">{{ $campaign->resource_button_text }}</button></form></div>
@else
<div class="mt-7 rounded-3xl bg-amber-50 p-5 text-center"><p class="font-black text-amber-950">Almost there!</p><p class="mt-1 text-sm text-amber-800">Complete enough follows to reach {{ $campaign->minimum_score }} points and unlock your resource.</p></div>
@endif

<p class="mt-8 text-center text-[11px] font-bold uppercase tracking-widest text-slate-400">Your progress is saved on this device while you complete the follows.</p>
</div>
</main>

<script>
(function () {
    var storagePrefix = 'social-follow-started-{{ $campaign->id }}-';

    function enableClaim(key) {
        var claim = document.querySelector('[data-claim-key="' + key + '"]');
        var status = document.querySelector('[data-status-key="' + key + '"]');
        if (!claim) return;

        claim.disabled = false;
        claim.removeAttribute('aria-disabled');
        claim.classList.remove('cursor-not-allowed','border-slate-200','bg-slate-100','text-slate-400','opacity-70');
        claim.classList.add('cursor-pointer','border-teal-200','bg-teal-50','text-teal-700');
        if (status) status.classList.remove('hidden');
    }

    document.querySelectorAll('.follow-platform').forEach(function (link) {
        var key = link.dataset.followKey;
        if (!key) return;

        // If the visitor already started this follow before leaving the page,
        // keep the confirmation button available when they return.
        if (localStorage.getItem(storagePrefix + key) === '1') {
            enableClaim(key);
        }

        link.addEventListener('click', function () {
            localStorage.setItem(storagePrefix + key, '1');
            enableClaim(key);
            // Deliberately do NOT use target="_blank". The visitor stays in
            // the same browser flow, while mobile OSes can still hand the
            // platform URL off to its native app when supported.
        });
    });
})();
</script>
</body>
</html>
