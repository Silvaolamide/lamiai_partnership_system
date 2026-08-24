<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <title>Application submitted | {{ $campaign->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#f3fbfa] text-slate-950">
    @php
        $campaignUrl = route('marketing.campaign.show', $campaign);
        $targetUrl = rtrim($campaign->redirect_url, '/');
        $sameAsCampaign = rtrim($campaignUrl, '/') === $targetUrl;
    @endphp
    <main class="flex min-h-screen items-center justify-center px-5 py-10">
        <section class="w-full max-w-lg text-center">
            <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-teal-100 text-teal-700">
                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <p class="mt-8 text-sm font-black uppercase tracking-[.2em] text-teal-700">{{ $campaign->name }}</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">You're all set!</h1>
            <p class="mx-auto mt-5 max-w-md text-lg leading-8 text-slate-500">
                Thank you. Your information has been submitted successfully.
            </p>

            @if($sameAsCampaign)
                <div class="mx-auto mt-8 max-w-sm rounded-2xl border border-amber-200 bg-amber-50 p-5 text-left text-sm text-amber-800">
                    <strong>Almost done.</strong> The campaign's final link currently points back to this application page, so we won't automatically send you into a loop. The business should update its final link.
                </div>
                <a href="{{ $campaignUrl }}" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-slate-700 px-6 py-4 font-black text-white shadow-lg transition hover:bg-slate-800">
                    Return to application
                </a>
            @else
                <div class="mx-auto mt-8 max-w-sm rounded-2xl border border-teal-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-center gap-2 text-sm font-bold text-slate-600">
                        <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-teal-600"></span>
                        <span>Taking you to the next step in <span id="countdown">3</span>s…</span>
                    </div>
                </div>

                <a href="{{ $campaign->redirect_url }}" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-teal-700 px-6 py-4 font-black text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-200">
                    Continue →
                </a>
                <p class="mt-5 text-xs text-slate-400">You can continue immediately or wait for the automatic redirect.</p>
            @endif
        </section>
    </main>

    @unless($sameAsCampaign)
        <script>
            (() => {
                const target = @json($campaign->redirect_url);
                const countdown = document.getElementById('countdown');
                let seconds = 3;
                const timer = setInterval(() => {
                    seconds -= 1;
                    countdown.textContent = seconds;
                    if (seconds <= 0) {
                        clearInterval(timer);
                        window.location.replace(target);
                    }
                }, 1000);
            })();
        </script>
    @endunless
</body>
</html>
