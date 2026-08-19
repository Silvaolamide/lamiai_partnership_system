<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $partner->user?->name ?? 'Partner' }} · {{ $program->name }} · AIPM</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<main>
    <section class="relative overflow-hidden bg-[#06130d] text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_85%_10%,rgba(37,211,102,.25),transparent_30%),radial-gradient(circle_at_10%_80%,rgba(124,58,237,.28),transparent_38%)]"></div>
        <div class="relative mx-auto max-w-6xl px-4 py-7 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-between"><span class="text-sm font-black tracking-[.22em] text-emerald-300">AIPM PARTNER STORE</span><a href="{{ route('home') }}" class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-bold">AI Powered Marketing</a></nav>

            <div class="grid items-center gap-10 py-12 lg:grid-cols-[1.08fr_.92fr] lg:py-16">
                <div>
                    <span class="inline-flex rounded-full border border-emerald-300/20 bg-emerald-400/10 px-3 py-1.5 text-xs font-black uppercase tracking-wider text-emerald-300">A simple way to start</span>
                    <h1 class="mt-5 max-w-3xl text-4xl font-black leading-[1.02] tracking-tight sm:text-5xl lg:text-6xl">Build your income with your <span class="text-emerald-300">WhatsApp Status.</span></h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300"><strong class="text-white">{{ $partner->user?->name ?? 'AIPM Partner' }}</strong> has created this page so you can see the products they promote, understand the model and decide whether becoming a partner is right for you.</p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ $joinUrl }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-500 px-6 py-3.5 font-black text-slate-950 shadow-lg shadow-emerald-950/30 hover:bg-emerald-400">Join {{ $partner->user?->name ?? 'this partner' }}'s network →</a>
                        <a href="#products" class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-6 py-3.5 font-bold hover:bg-white/10">See products</a>
                    </div>
                    <p class="mt-4 text-xs text-slate-500">No guaranteed-income promises. Your actual earnings depend on qualifying sales and the rules of the program.</p>
                </div>

                <div class="mx-auto w-full max-w-sm">
                    <div class="rounded-[2rem] border border-white/10 bg-black p-2 shadow-2xl">
                        <div class="overflow-hidden rounded-[1.6rem] bg-[#101b18]">
                            <div class="flex items-center gap-3 bg-black/80 px-4 py-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-400 font-black text-slate-950">{{ strtoupper(substr($partner->user?->name ?? 'P',0,1)) }}</div>
                                <div><p class="text-sm font-bold">{{ $partner->user?->name ?? 'Partner' }}</p><p class="text-[10px] text-slate-400">WhatsApp Status · just now</p></div>
                                <div class="ml-auto flex gap-1"><span class="h-1 w-8 rounded-full bg-white"></span><span class="h-1 w-8 rounded-full bg-white/30"></span><span class="h-1 w-8 rounded-full bg-white/30"></span></div>
                            </div>
                            <div class="relative flex min-h-[390px] flex-col justify-end overflow-hidden bg-gradient-to-br from-emerald-950 via-slate-950 to-violet-950 p-6">
                                <div class="absolute -right-12 -top-10 h-44 w-44 rounded-full bg-emerald-400/20 blur-3xl"></div>
                                <div class="relative">
                                    <span class="rounded-full bg-emerald-400 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-950">What I am doing</span>
                                    <h2 class="mt-5 text-3xl font-black leading-tight">I promote products from my phone.</h2>
                                    <p class="mt-4 text-sm leading-6 text-slate-300">I share useful products on my Status. People who are interested tap my link, check the sales page and decide for themselves.</p>
                                    <div class="mt-6 rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur"><p class="text-[10px] font-black uppercase tracking-widest text-emerald-300">My product page</p><p class="mt-2 truncate text-xs text-white/80">{{ url()->current() }}</p></div>
                                    <p class="mt-5 text-center text-xs font-bold text-white/70">See the products • Choose what you want • Join if it makes sense</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 text-center text-xs font-semibold text-slate-400">The model is simple: <span class="text-emerald-300">Status → interest → product → sale → qualifying commission</span>.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl"><p class="text-xs font-black uppercase tracking-[.18em] text-emerald-600">Why WhatsApp Status?</p><h2 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">You already have an audience. Use it intelligently.</h2><p class="mt-4 text-lg leading-8 text-slate-600">You don't need to cold-message everyone you know. A Status lets interested people come to you. Show what you are promoting, let them explore and give them an easy next step.</p></div>
            <div class="mt-10 grid gap-5 md:grid-cols-3">
                <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6"><div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 font-black text-violet-700">01</div><h3 class="mt-5 text-xl font-black">Post the story</h3><p class="mt-2 leading-7 text-slate-600">Share a useful product or explain what you are building. Keep it natural enough that people want to tap.</p></article>
                <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6"><div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 font-black text-emerald-700">02</div><h3 class="mt-5 text-xl font-black">Let people explore</h3><p class="mt-2 leading-7 text-slate-600">Your link brings them here. They can read about the program and inspect real product sales pages before deciding.</p></article>
                <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6"><div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 font-black text-blue-700">03</div><h3 class="mt-5 text-xl font-black">Earn when you qualify</h3><p class="mt-2 leading-7 text-slate-600">Qualifying sales are attributed through the partner link according to the commission rules of the program.</p></article>
            </div>
        </div>
    </section>

    <section class="bg-slate-950 py-14 text-white sm:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"><div class="max-w-3xl"><p class="text-xs font-black uppercase tracking-[.18em] text-emerald-300">Your first three Status posts</p><h2 class="mt-2 text-3xl font-black sm:text-4xl">You don't have to figure out what to say.</h2><p class="mt-3 text-slate-400">Use this simple sequence to introduce the idea without sounding like you're begging people to buy.</p></div><button type="button" onclick="copyAllStatuses(this)" class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-black text-slate-950">Copy all 3 scripts</button></div>
            <div class="mt-9 grid gap-5 lg:grid-cols-3">
                <article class="overflow-hidden rounded-3xl border border-white/10 bg-white/5"><div class="border-b border-white/10 px-5 py-4 text-xs font-black uppercase tracking-wider text-emerald-300">Status 01 · Curiosity</div><div class="p-5"><p class="text-2xl font-black leading-tight">“What if your WhatsApp could help you find customers?”</p><p class="mt-4 text-sm leading-7 text-slate-400">I've started using a simple partner model to promote products I genuinely want people to see.</p><p class="mt-5 text-xs font-bold text-emerald-300">Reply “INFO” if you want to see how it works.</p><button type="button" onclick="copyScript(1,this)" class="mt-6 w-full rounded-xl bg-white/10 px-4 py-3 text-sm font-bold">Copy Status 1</button></div></article>
                <article class="overflow-hidden rounded-3xl border border-white/10 bg-white/5"><div class="border-b border-white/10 px-5 py-4 text-xs font-black uppercase tracking-wider text-emerald-300">Status 02 · Show</div><div class="p-5"><p class="text-2xl font-black leading-tight">“Here's the part I like: no shop, no stock room.”</p><p class="mt-4 text-sm leading-7 text-slate-400">I can share product sales pages from my phone. People choose what they want and qualifying referrals are tracked.</p><p class="mt-5 text-xs font-bold text-emerald-300">See my products → {{ url()->current() }}</p><button type="button" onclick="copyScript(2,this)" class="mt-6 w-full rounded-xl bg-white/10 px-4 py-3 text-sm font-bold">Copy Status 2</button></div></article>
                <article class="overflow-hidden rounded-3xl border border-white/10 bg-white/5"><div class="border-b border-white/10 px-5 py-4 text-xs font-black uppercase tracking-wider text-emerald-300">Status 03 · Invite</div><div class="p-5"><p class="text-2xl font-black leading-tight">“Want to try it with me?”</p><p class="mt-4 text-sm leading-7 text-slate-400">Look through the products, see how the partner model works and decide if it's for you. No pressure.</p><p class="mt-5 text-xs font-bold text-emerald-300">Tap the link → explore → join if it makes sense.</p><button type="button" onclick="copyScript(3,this)" class="mt-6 w-full rounded-xl bg-white/10 px-4 py-3 text-sm font-bold">Copy Status 3</button></div></article>
            </div>
        </div>
    </section>

    <section id="products" class="bg-white py-14 sm:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl"><p class="text-xs font-black uppercase tracking-[.18em] text-violet-600">Products {{ $partner->user?->name ?? 'this partner' }} promotes</p><h2 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Choose a product. Open its sales page. Share it.</h2><p class="mt-4 text-lg leading-8 text-slate-600">Every product below connects to its own sales page with this partner's referral attribution attached.</p></div>
            @if($products->isNotEmpty())
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($products as $product)
                        <article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="flex min-h-44 items-center justify-center bg-gradient-to-br from-slate-100 via-white to-violet-100 p-6"><div class="rounded-2xl bg-white px-5 py-4 text-center shadow-sm"><span class="text-xs font-black uppercase tracking-widest text-violet-600">AIPM Product</span><p class="mt-1 max-w-[220px] text-xl font-black">{{ $product->name }}</p></div></div>
                            <div class="flex flex-1 flex-col p-6"><h3 class="text-xl font-black">{{ $product->name }}</h3>@if($product->description)<p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ $product->description }}</p>@endif<div class="mt-auto flex items-end justify-between gap-4 pt-6"><div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Price</p><p class="mt-1 text-2xl font-black">{{ $product->currency ?? 'NGN' }} {{ number_format((float)$product->price,2) }}</p></div><a href="{{ route('product.show', ['slug' => $product->slug, 'ref' => $partner->partner_code]) }}" class="rounded-xl bg-slate-950 px-4 py-3 text-sm font-bold text-white group-hover:bg-violet-600">View sales page →</a></div></div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mt-10 rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center"><h3 class="text-xl font-bold">Products are being prepared</h3><p class="mt-2 text-slate-600">Check back soon or join the network to receive updates.</p></div>
            @endif
        </div>
    </section>

    <section class="bg-slate-100 py-14 sm:py-20">
        <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8"><span class="text-xs font-black uppercase tracking-[.18em] text-violet-600">Ready to build?</span><h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Turn your Status into a storefront.</h2><p class="mx-auto mt-4 max-w-2xl text-lg leading-8 text-slate-600">Join {{ $partner->user?->name ?? 'this partner' }}'s network, get your own referral link and start sharing products with the people who already know you.</p><a href="{{ $joinUrl }}" class="mt-7 inline-flex rounded-xl bg-violet-600 px-7 py-3.5 font-black text-white shadow-lg shadow-violet-200 hover:bg-violet-700">Join the network →</a></div>
    </section>
</main>
<script>
const scripts={1:`What if your WhatsApp could help you find customers?\n\nI've started using a simple partner model to promote products I genuinely want people to see.\n\nReply “INFO” if you want to see how it works.`,2:`Here's the part I like: no shop, no stock room.\n\nI can share product sales pages from my phone. People choose what they want and qualifying referrals are tracked.\n\nSee my products → {{ url()->current() }}`,3:`Want to try it with me?\n\nLook through the products, see how the partner model works and decide if it's for you. No pressure.\n\nTap the link → explore → join if it makes sense.`};
function copyScript(n,b){navigator.clipboard?.writeText(scripts[n]);const old=b.textContent;b.textContent='Copied!';setTimeout(()=>b.textContent=old,1600)}
function copyAllStatuses(b){navigator.clipboard?.writeText(Object.values(scripts).join('\n\n---\n\n'));const old=b.textContent;b.textContent='All 3 copied!';setTimeout(()=>b.textContent=old,1600)}
</script>
</body>
</html>
