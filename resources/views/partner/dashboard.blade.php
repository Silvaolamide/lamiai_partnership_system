<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Dashboard · AIPM</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-violet-600">AIPM Partner Hub</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">Build your income from your WhatsApp Status.</h1>
            <p class="mt-2 text-slate-600">Welcome back, {{ auth()->user()->name }}. Your fastest path to new customers is already in your phone.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('network.index') }}" class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white">My Network</a>
            <a href="{{ route('partner.payouts.index') }}" class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white">Payouts</a>
            <a href="{{ route('profile.edit') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold">Profile</a>
        </div>
    </header>

    @php
        $firstStat = $programStats->first();
        $firstPartner = $firstStat['partner'] ?? null;
        $firstProgram = $firstStat['program'] ?? null;
        $firstRules = $firstProgram?->commissionRules?->where('status', true)->where('event', 'sale') ?? collect();
        $directRule = $firstRules->where('level', 1)->sortByDesc('priority')->first();
        $directRate = $directRule ? ($directRule->commission_type === 'percentage' ? number_format((float)$directRule->value, 2).'%' : '₦'.number_format((float)$directRule->value, 2)) : 'your program rate';
        $storefrontUrl = $firstPartner ? route('partner.storefront', ['partnerCode' => $firstPartner->partner_code]) : route('partner.marketplace.index');
    @endphp

    <!-- WhatsApp-first hero -->
    <section class="relative mb-8 overflow-hidden rounded-[2rem] bg-[#07110d] text-white shadow-2xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_82%_15%,rgba(37,211,102,.28),transparent_28%),radial-gradient(circle_at_12%_90%,rgba(124,58,237,.22),transparent_35%)]"></div>
        <div class="relative grid gap-8 px-5 py-7 sm:px-8 sm:py-10 lg:grid-cols-[1.05fr_.95fr] lg:px-10 lg:py-12">
            <div class="flex flex-col justify-center">
                <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-400/25 bg-emerald-400/10 px-3 py-1.5 text-xs font-black uppercase tracking-wider text-emerald-300">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span> Your easiest promotion channel
                </div>
                <h2 class="mt-5 max-w-2xl text-4xl font-black leading-[1.03] tracking-tight sm:text-5xl">Don't chase people. <span class="text-emerald-300">Put the opportunity on your Status.</span></h2>
                <p class="mt-5 max-w-xl text-base leading-7 text-slate-300 sm:text-lg">Post a simple story. Someone who is interested taps your link. They see the product sales page. If they buy a qualifying product, you can earn the configured partner commission.</p>
                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <button type="button" onclick="shareStatus()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#25D366] px-6 py-3.5 text-sm font-black text-[#061b0e] shadow-lg shadow-emerald-950/30 hover:bg-[#42df7b]">Share to WhatsApp ↗</button>
                    <a href="#status-kit" class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-6 py-3.5 text-sm font-black text-white hover:bg-white/10">Build my Status story</a>
                </div>
                <p class="mt-3 text-xs text-slate-500">Works best on your phone. AIPM never promises guaranteed income; your actual commission depends on qualifying sales and program rules.</p>
            </div>

            <!-- WhatsApp Status visual -->
            <div class="mx-auto w-full max-w-sm">
                <div class="rounded-[2rem] border border-white/10 bg-black p-2 shadow-2xl">
                    <div class="overflow-hidden rounded-[1.55rem] bg-[#101b18]">
                        <div class="flex items-center gap-3 bg-black/70 px-4 py-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-400 text-sm font-black text-slate-950">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                            <div class="min-w-0"><p class="truncate text-sm font-bold">My Status</p><p class="text-[10px] text-slate-400">Just now</p></div>
                            <div class="ml-auto flex gap-1"><span class="h-1 w-8 rounded-full bg-white"></span><span class="h-1 w-8 rounded-full bg-white/30"></span><span class="h-1 w-8 rounded-full bg-white/30"></span></div>
                        </div>
                        <div class="relative flex min-h-[390px] flex-col justify-end overflow-hidden bg-gradient-to-br from-emerald-950 via-slate-950 to-violet-950 p-6">
                            <div class="absolute -right-16 -top-12 h-44 w-44 rounded-full bg-emerald-400/20 blur-2xl"></div>
                            <div class="relative">
                                <span class="rounded-full bg-emerald-400 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-950">A simple side income idea</span>
                                <p class="mt-5 text-3xl font-black leading-tight">I promote products from my phone.</p>
                                <p class="mt-4 text-sm leading-6 text-slate-300">I share my link on WhatsApp Status. Interested people check the product. Qualifying sales can earn me commission.</p>
                                <div class="mt-6 rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-300">My AIPM partner link</p>
                                    <p class="mt-2 truncate text-xs text-white/80">{{ $storefrontUrl }}</p>
                                </div>
                                <p class="mt-5 text-center text-xs font-bold text-white/70">Tap my link • See the products • Decide for yourself</p>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mt-3 text-center text-xs font-semibold text-slate-400">This is the behaviour we want: <span class="text-emerald-300">show → tap → product → purchase</span>.</p>
            </div>
        </div>
    </section>

    <!-- Three-step WhatsApp Status playbook -->
    <section id="status-kit" class="mb-8 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-600">Your 3-status selling system</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight">One day. Three Status posts. One clear journey.</h2>
                <p class="mt-2 text-slate-600">Don't dump a referral link and hope. Tell a tiny story that makes the next action obvious.</p>
            </div>
            <button type="button" onclick="copyAllStatuses(this)" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-black text-white">Copy all 3 scripts</button>
        </div>

        <div class="mt-7 grid gap-4 lg:grid-cols-3">
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 text-white">
                <div class="flex items-center justify-between border-b border-white/10 px-4 py-3"><span class="text-xs font-black uppercase tracking-wider text-emerald-300">Status 01 · Curiosity</span><span class="text-[10px] text-slate-500">10:00 AM</span></div>
                <div class="flex min-h-60 flex-col justify-end bg-gradient-to-br from-violet-950 to-slate-950 p-5"><p class="text-2xl font-black leading-tight">What if your WhatsApp could help you find customers?</p><p class="mt-3 text-sm leading-6 text-slate-300">I've started using a simple partner model to promote products I genuinely want people to see.</p><p class="mt-5 text-xs font-bold text-emerald-300">Reply “INFO” if you want to see how it works.</p></div>
                <div class="p-3"><button type="button" onclick="copyScript(1,this)" class="w-full rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold hover:bg-white/15">Copy Status 1</button></div>
            </article>
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 text-white">
                <div class="flex items-center justify-between border-b border-white/10 px-4 py-3"><span class="text-xs font-black uppercase tracking-wider text-emerald-300">Status 02 · Show</span><span class="text-[10px] text-slate-500">2:00 PM</span></div>
                <div class="flex min-h-60 flex-col justify-end bg-gradient-to-br from-emerald-950 to-slate-950 p-5"><p class="text-2xl font-black leading-tight">Here's the part I like: no shop, no stock room.</p><p class="mt-3 text-sm leading-6 text-slate-300">I can share product sales pages from my phone. People choose what they want and the platform tracks qualifying referrals.</p><p class="mt-5 text-xs font-bold text-emerald-300">See my product page → {{ $storefrontUrl }}</p></div>
                <div class="p-3"><button type="button" onclick="copyScript(2,this)" class="w-full rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold hover:bg-white/15">Copy Status 2</button></div>
            </article>
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 text-white">
                <div class="flex items-center justify-between border-b border-white/10 px-4 py-3"><span class="text-xs font-black uppercase tracking-wider text-emerald-300">Status 03 · Invite</span><span class="text-[10px] text-slate-500">7:00 PM</span></div>
                <div class="flex min-h-60 flex-col justify-end bg-gradient-to-br from-blue-950 to-slate-950 p-5"><p class="text-2xl font-black leading-tight">Want to try it with me?</p><p class="mt-3 text-sm leading-6 text-slate-300">You can look through the products, see how the partner model works and decide if it's for you. No pressure.</p><p class="mt-5 text-xs font-bold text-emerald-300">Tap the link → explore → join if it makes sense.</p></div>
                <div class="p-3"><button type="button" onclick="copyScript(3,this)" class="w-full rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold hover:bg-white/15">Copy Status 3</button></div>
            </article>
        </div>

        <div class="mt-5 flex flex-col gap-3 rounded-2xl bg-emerald-50 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="font-black text-emerald-950">Make the CTA frictionless.</p><p class="mt-1 text-sm text-emerald-800">Use your storefront link in Status 2 and 3. It already connects prospects to your products and referral attribution.</p></div>
            <div class="flex gap-2"><input id="main_storefront_link" readonly value="{{ $storefrontUrl }}" class="min-w-0 flex-1 rounded-xl border border-emerald-200 bg-white px-3 py-2.5 text-xs text-slate-600"><button type="button" onclick="copyText('main_storefront_link',this)" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-black text-white">Copy link</button></div>
        </div>
    </section>

    <!-- Earnings proof without hype -->
    <section class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">Gross Sales</p><p class="mt-2 text-3xl font-black">₦{{ number_format($totalSalesAmount,2) }}</p><p class="mt-1 text-xs text-slate-500">{{ $totalSales }} completed orders</p></div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm"><p class="text-sm font-semibold text-emerald-700">Available Commission</p><p class="mt-2 text-3xl font-black text-emerald-700">₦{{ number_format($totalPending,2) }}</p><p class="mt-1 text-xs text-emerald-700">Eligible for payout</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">Paid Commission</p><p class="mt-2 text-3xl font-black">₦{{ number_format($totalPaid,2) }}</p><p class="mt-1 text-xs text-slate-500">Lifetime paid earnings</p></div>
        <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5 shadow-sm"><p class="text-sm font-semibold text-violet-700">Partners Recruited</p><p class="mt-2 text-3xl font-black text-violet-950">{{ $totalRecruited }}</p><p class="mt-1 text-xs text-violet-700">Across your programs</p></div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm"><p class="text-sm font-semibold text-blue-700">Business Net Revenue</p><p class="mt-2 text-3xl font-black text-blue-950">₦{{ number_format($totalNetBusinessRevenue,2) }}</p><p class="mt-1 text-xs text-blue-700">Sales less commissions</p></div>
    </section>

    <!-- Program/product promotion -->
    <div id="programs" class="space-y-6">
    @forelse($programStats as $programStat)
        @php
            $program = $programStat['program'];
            $partner = $programStat['partner'];
            $rules = $program->commissionRules->where('status', true)->where('event', 'sale');
            $level1Rule = $rules->where('level', 1)->sortByDesc('priority')->first();
            $level2Rule = $rules->where('level', 2)->sortByDesc('priority')->first();
            $storefrontUrl = route('partner.storefront', ['partnerCode' => $partner->partner_code]);
        @endphp
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="bg-slate-950 px-6 py-6 text-white sm:px-8"><div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-xs font-black uppercase tracking-widest text-emerald-300">Program</p><h2 class="mt-1 text-2xl font-black">{{ $program->name }}</h2><p class="mt-2 text-sm text-slate-400">Your referral code: <span class="font-mono font-bold text-white">{{ $partner->partner_code }}</span> · Direct commission: <span class="font-bold text-emerald-300">{{ $level1Rule ? ($level1Rule->commission_type === 'percentage' ? number_format($level1Rule->value,2).'%' : '₦'.number_format($level1Rule->value,2)) : 'Program-defined' }}</span></p></div><a href="{{ $storefrontUrl }}" target="_blank" class="rounded-xl bg-emerald-500 px-5 py-3 text-center text-sm font-black text-slate-950 hover:bg-emerald-400">Open my storefront →</a></div></div>
            <div class="p-6 sm:p-8">
                <div class="flex flex-col gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-black text-emerald-950">Your Status link is ready.</p><p class="mt-1 text-sm text-emerald-800">Share this once and let your storefront explain the products and opportunity.</p></div><div class="flex gap-2"><input id="storefront_{{ $partner->id }}" readonly value="{{ $storefrontUrl }}" class="min-w-0 flex-1 rounded-xl border border-emerald-200 bg-white px-3 py-2.5 text-xs text-slate-600"><button type="button" onclick="copyText('storefront_{{ $partner->id }}',this)" class="rounded-xl bg-slate-950 px-4 py-2.5 text-xs font-black text-white">Copy</button></div></div>
                <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-5"><div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Gross sales</p><p class="mt-1 text-2xl font-black">₦{{ number_format($programStat['gross_sales'],2) }}</p></div><div class="rounded-xl bg-emerald-50 p-4"><p class="text-xs text-emerald-700">Your earnings</p><p class="mt-1 text-2xl font-black text-emerald-800">₦{{ number_format($programStat['direct_commission'] + $programStat['recruiter_commission'],2) }}</p></div><div class="rounded-xl bg-violet-50 p-4"><p class="text-xs text-violet-700">Recruiter earnings</p><p class="mt-1 text-2xl font-black text-violet-900">₦{{ number_format($programStat['recruiter_commission'],2) }}</p></div><div class="rounded-xl bg-amber-50 p-4"><p class="text-xs text-amber-700">All commissions</p><p class="mt-1 text-2xl font-black text-amber-900">₦{{ number_format($programStat['total_commissions'],2) }}</p></div><div class="rounded-xl bg-blue-50 p-4"><p class="text-xs text-blue-700">Business net</p><p class="mt-1 text-2xl font-black text-blue-900">₦{{ number_format($programStat['net_business_revenue'],2) }}</p></div></div>
                <div class="mt-7"><div class="mb-4 flex items-end justify-between gap-4"><div><h3 class="text-xl font-black">Products to put on your Status</h3><p class="mt-1 text-sm text-slate-500">Every button opens the product sales page with your referral code attached.</p></div><a href="{{ $storefrontUrl }}#products" target="_blank" class="text-sm font-black text-violet-600">See storefront →</a></div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($program->products as $product)
                        @php $productUrl = route('product.show', ['slug' => $product->slug, 'ref' => $partner->partner_code]); @endphp
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5"><div class="flex items-start justify-between gap-3"><div><p class="text-lg font-black">{{ $product->name }}</p><p class="mt-1 text-sm font-bold text-slate-600">{{ $product->currency ?? 'NGN' }} {{ number_format((float)$product->price,2) }}</p></div><span class="rounded-full bg-violet-100 px-2.5 py-1 text-[10px] font-black uppercase text-violet-700">Product</span></div><div class="mt-5 flex gap-2"><a target="_blank" href="{{ $productUrl }}" class="flex-1 rounded-xl bg-violet-600 px-3 py-2.5 text-center text-sm font-black text-white">Sales page →</a><button type="button" onclick="copyValue(@js($productUrl),this)" class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white">Copy</button></div></article>
                    @empty
                        <p class="text-sm text-slate-500">No products are currently attached to this program.</p>
                    @endforelse
                    </div>
                </div>
            </div>
        </section>
    @empty
        <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center"><h2 class="text-xl font-black">No active partnership program yet.</h2><p class="mt-2 text-slate-600">Find a program to start promoting products.</p><a href="{{ route('partner.marketplace.index') }}" class="mt-5 inline-flex rounded-xl bg-violet-600 px-5 py-3 font-bold text-white">Find Programs →</a></section>
    @endforelse
    </div>
</div>

<script>
const storefront = @json($storefrontUrl);
const scripts = {
    1: 'What if your WhatsApp could help you find customers?\n\nI started using a simple partner model to promote products I genuinely want people to see.\n\nReply “INFO” if you want to see how it works.',
    2: `Here is the part I like: no shop, no stock room.\n\nI can share product sales pages from my phone. People choose what they want and the platform tracks qualifying referrals.\n\nSee my product page → ${storefront}`,
    3: `Want to try it with me?\n\nLook through the products, see how the partner model works and decide if it is for you. No pressure.\n\nTap the link → explore → join if it makes sense.\n\n${storefront}`
};
function copyValue(value, button){ navigator.clipboard?.writeText(value).then(()=>{const old=button.textContent;button.textContent='Copied';setTimeout(()=>button.textContent=old,1400);}); }
function copyText(id, button){ const el=document.getElementById(id); if(el) copyValue(el.value,button); }
function copyScript(n, button){ copyValue(scripts[n],button); }
function copyAllStatuses(button){ copyValue(Object.values(scripts).join('\n\n──────────\n\n'),button); }
function shareStatus(){
    const text = scripts[2] + '\n\n' + storefront;
    if (navigator.share) navigator.share({title:'AIPM Partner Opportunity',text}).catch(()=>{});
    else { copyValue(text, document.querySelector('button[onclick="shareStatus()"]')); window.open('https://wa.me/?text='+encodeURIComponent(text),'_blank'); }
}
</script>
</body>
</html>
