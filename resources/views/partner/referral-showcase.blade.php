<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $partner->user?->name ?? 'Partner' }} · {{ $program->name }} · AIPM</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-900">
    <main>
        <section class="relative overflow-hidden bg-slate-950 text-white">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(124,58,237,.35),transparent_42%),radial-gradient(circle_at_bottom_left,rgba(37,99,235,.25),transparent_40%)]"></div>
            <div class="relative mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8 lg:py-20">
                <div class="flex items-center justify-between gap-4">
                    <div class="text-sm font-black tracking-[0.22em] text-violet-300">AIPM PARTNER STORE</div>
                    <a href="{{ route('home') }}" class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-semibold text-white/90 hover:bg-white/10">AI Powered Marketing</a>
                </div>
                <div class="grid items-center gap-12 pt-14 lg:grid-cols-[1.15fr_.85fr]">
                    <div>
                        <span class="inline-flex rounded-full border border-emerald-300/20 bg-emerald-400/10 px-3 py-1 text-sm font-semibold text-emerald-200">Official partner invitation</span>
                        <h1 class="mt-5 text-4xl font-black tracking-tight sm:text-5xl lg:text-6xl">Build your income with <span class="text-violet-300">{{ $partner->user?->name ?? 'my' }}</span>.</h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">{{ $partner->user?->name ?? 'Your partner' }} is inviting you to join <strong class="text-white">{{ $program->name }}</strong> — promote real products, build a customer base and unlock additional earning opportunities as your network grows.</p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ $joinUrl }}" class="inline-flex items-center justify-center rounded-xl bg-violet-500 px-6 py-3.5 font-bold text-white shadow-lg shadow-violet-950/40 hover:bg-violet-400">Join {{ $partner->user?->name ?? 'this partner' }}'s network →</a>
                            <a href="#products" class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-6 py-3.5 font-bold text-white hover:bg-white/10">Explore products</a>
                        </div>
                        <p class="mt-4 text-xs text-slate-400">Your application is connected to {{ $partner->user?->name ?? 'this partner' }} using recruiter code <span class="font-mono text-slate-300">{{ $partner->partner_code }}</span>.</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/[.06] p-6 shadow-2xl backdrop-blur sm:p-8">
                        <p class="text-sm font-semibold text-slate-400">Why join this program?</p>
                        <div class="mt-6 space-y-4">
                            <div class="rounded-2xl bg-white/5 p-4"><p class="font-bold">Sell products people can actually buy</p><p class="mt-1 text-sm text-slate-400">Every product below opens its own sales page with your referral attribution.</p></div>
                            <div class="rounded-2xl bg-white/5 p-4"><p class="font-bold">Earn from qualifying sales</p><p class="mt-1 text-sm text-slate-400">{{ $directRule ? ($directRule->commission_type === 'percentage' ? number_format((float)$directRule->value, 2).'%' : '₦'.number_format((float)$directRule->value, 2)) : 'Program-defined' }} direct commission on qualifying sales.</p></div>
                            <div class="rounded-2xl bg-white/5 p-4"><p class="font-bold">Grow beyond your own sales</p><p class="mt-1 text-sm text-slate-400">{{ $recruiterRule ? ($recruiterRule->commission_type === 'percentage' ? number_format((float)$recruiterRule->value, 2).'%' : '₦'.number_format((float)$recruiterRule->value, 2)) : 'Additional' }} recruiter-level earning opportunity when eligible partners you introduce generate qualifying sales.</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="products" class="bg-white py-14 sm:py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-violet-600">Products you can promote</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Start with products your audience already wants.</h2>
                    <p class="mt-4 text-lg leading-8 text-slate-600">Choose a product, open its sales page and share it. The referral code is already attached, so your qualifying sales can be attributed to this partner.</p>
                </div>

                @if($products->isNotEmpty())
                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($products as $product)
                            <article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                <div class="flex min-h-40 items-center justify-center bg-gradient-to-br from-slate-100 via-white to-violet-100 p-6">
                                    <div class="rounded-2xl bg-white px-5 py-4 text-center shadow-sm">
                                        <span class="text-xs font-black uppercase tracking-widest text-violet-600">AIPM Product</span>
                                        <p class="mt-1 max-w-[210px] text-xl font-black text-slate-950">{{ $product->name }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col p-6">
                                    <h3 class="text-xl font-black text-slate-950">{{ $product->name }}</h3>
                                    @if($product->description)<p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ $product->description }}</p>@endif
                                    <div class="mt-auto pt-6">
                                        <div class="flex items-end justify-between gap-4">
                                            <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Price</p><p class="mt-1 text-2xl font-black text-slate-950">{{ $product->currency ?? 'NGN' }} {{ number_format((float)$product->price, 2) }}</p></div>
                                            <a href="{{ route('product.show', ['slug' => $product->slug, 'ref' => $partner->partner_code]) }}" class="rounded-xl bg-slate-950 px-4 py-3 text-sm font-bold text-white transition group-hover:bg-violet-600">View sales page →</a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="mt-10 rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center"><h3 class="text-xl font-bold text-slate-900">Products are being prepared</h3><p class="mt-2 text-slate-600">Check back soon or join the network to receive updates.</p></div>
                @endif
            </div>
        </section>

        <section class="bg-slate-100 py-14">
            <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
                <span class="text-sm font-black uppercase tracking-[0.18em] text-violet-600">Ready to grow?</span>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Don't just watch {{ $partner->user?->name ?? 'the opportunity' }} build. Build with them.</h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg leading-8 text-slate-600">Create your partner account, choose the programs available to you and start building your own customer and partner network.</p>
                <a href="{{ $joinUrl }}" class="mt-7 inline-flex rounded-xl bg-violet-600 px-7 py-3.5 font-bold text-white shadow-lg shadow-violet-200 hover:bg-violet-700">Join the network →</a>
            </div>
        </section>
    </main>
</body>
</html>
