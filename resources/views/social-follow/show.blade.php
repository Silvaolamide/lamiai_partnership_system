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

<div class="mt-6 rounded-3xl border-2 border-teal-200 bg-teal-50 p-5 shadow-sm">
<div class="flex gap-3">
<div class="shrink-0 text-xl">⚠️</div>
<div>
<p class="text-base font-black uppercase tracking-tight text-teal-950">IMPORTANT — COME BACK HERE</p>
<p class="mt-2 text-sm font-bold leading-6 text-teal-900">Tap <b>Follow</b> to open the social app. If the app is not installed, the account will open in a new browser tab.</p>
<p class="mt-2 text-sm font-black leading-6 text-teal-950">After you follow the account, <u>COME BACK TO THIS PAGE</u> and tap <b>“I've followed — check ✓”</b> to continue.</p>
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
<div class="mt-4 flex flex-wrap gap-2">
<a href="{{$account->followUrl()}}" target="_blank" rel="noopener" data-follow-key="{{$followKey}}" data-platform="{{$account->platform}}" class="follow-platform flex-1 rounded-xl {{ $done?'border border-emerald-200 bg-white text-emerald-700':'bg-slate-900 text-white' }} px-4 py-3 text-center text-sm font-black">{{ $account->platform==='youtube'?'Subscribe on YouTube':'Follow on '.ucfirst($account->platform) }} →</a>
@unless($done)
<form class="flex-1" method="POST" action="{{route('social-follow.claim',[$campaign->slug,$account->id])}}">
@csrf
<button type="submit" data-claim-key="{{$followKey}}" disabled autocomplete="off" class="claim-follow w-full cursor-not-allowed pointer-events-none rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-black text-slate-400 opacity-70 transition" aria-disabled="true">I've followed — check ✓</button>
</form>
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
<p class="mt-8 text-center text-[11px] font-bold uppercase tracking-widest text-slate-400">Your progress is waiting here. Follow the account, then come back and confirm.</p>
</div>
</main>

<script>
(function () {
    function enableClaim(key) {
        var claim = document.querySelector('[data-claim-key="' + key + '"]');
        if (!claim) return;
        claim.disabled = false;
        claim.removeAttribute('aria-disabled');
        claim.classList.remove('cursor-not-allowed','pointer-events-none','border-slate-200','bg-slate-100','text-slate-400','opacity-70');
        claim.classList.add('cursor-pointer','pointer-events-auto','border-teal-200','bg-teal-50','text-teal-700');
    }

    function androidIntent(url, platform) {
        var parsed;
        try { parsed = new URL(url); } catch (e) { return null; }
        var packages = {
            instagram: 'com.instagram.android',
            facebook: 'com.facebook.katana',
            tiktok: 'com.zhiliaoapp.musically',
            youtube: 'com.google.android.youtube'
        };
        var pkg = packages[platform];
        if (!pkg || parsed.protocol !== 'https:') return null;
        var fallback = encodeURIComponent(url);
        return 'intent://' + parsed.host + parsed.pathname + parsed.search +
            '#Intent;scheme=https;package=' + pkg +
            ';S.browser_fallback_url=' + fallback + ';end';
    }

    document.querySelectorAll('.follow-platform').forEach(function (link) {
        var key = link.dataset.followKey;
        var platform = (link.dataset.platform || '').toLowerCase();
        if (!key) return;

        link.addEventListener('click', function (event) {
            enableClaim(key);

            // On Android, the new tab created by target=_blank remains the
            // campaign's return point while the intent hands the user to the
            // installed native app. If the app is unavailable, Android uses
            // the supplied HTTPS fallback in that new tab.
            if (/Android/i.test(navigator.userAgent)) {
                var intent = androidIntent(link.href, platform);
                if (intent) {
                    event.preventDefault();
                    var fallbackTab = window.open('about:blank', '_blank');
                    if (fallbackTab) {
                        fallbackTab.location.href = intent;
                    } else {
                        window.location.href = intent;
                    }
                }
            }
            // iOS and desktop use the normal new-tab HTTPS link. iOS may hand
            // that URL to the installed app through Universal Links; otherwise
            // the profile remains available in the newly opened browser tab.
        });
    });
})();
</script>
</body>
</html>
