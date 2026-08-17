<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LAMI AI Business Marketing helps businesses create affiliate programs, recruit partners, track sales and reward affiliates with commissions.">
    <title>LAMI AI Business Marketing — Turn Your Network Into a Sales Force</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
                    colors: { lami: { 50:'#f5f3ff',100:'#ede9fe',500:'#7c3aed',600:'#6d28d9',700:'#5b21b6',950:'#1e123d' } },
                    boxShadow: { glow: '0 0 80px rgba(124,58,237,.18)' }
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior:smooth; }
        body { background:#fbfbfd; color:#111827; }
        .grid-bg { background-image: linear-gradient(rgba(124,58,237,.055) 1px, transparent 1px), linear-gradient(90deg, rgba(124,58,237,.055) 1px, transparent 1px); background-size:42px 42px; }
        .gradient-text { background:linear-gradient(90deg,#6d28d9,#9333ea,#c026d3); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .hero-orb { filter:blur(1px); background:radial-gradient(circle,rgba(139,92,246,.22),rgba(236,72,153,.08) 45%,transparent 70%); }
    </style>
</head>
<body class="font-sans antialiased">
    <nav class="fixed inset-x-0 top-0 z-50 border-b border-gray-200/70 bg-white/85 backdrop-blur-xl">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-gray-950 text-lg font-black text-white shadow-lg">L</span>
                <span class="text-xl font-black tracking-tight">LAMI <span class="text-violet-600">AI</span></span>
            </a>
            <div class="hidden items-center gap-8 text-sm font-semibold text-gray-600 md:flex">
                <a href="#how-it-works" class="transition hover:text-violet-600">How it works</a>
                <a href="#businesses" class="transition hover:text-violet-600">For Businesses</a>
                <a href="#affiliates" class="transition hover:text-violet-600">For Affiliates</a>
                <a href="#features" class="transition hover:text-violet-600">Features</a>
                <a href="#programs" class="transition hover:text-violet-600">Programs</a>
            </div>
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ auth()->user()->hasRole('super_admin') ? route('admin') : route('dashboard') }}" class="hidden rounded-xl px-4 py-2.5 text-sm font-bold text-gray-700 sm:block">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-xl px-4 py-2.5 text-sm font-bold text-gray-700 sm:block">Log in</a>
                @endauth
                <a href="{{ route('register') }}" class="rounded-xl bg-gray-950 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-gray-900/15 transition hover:-translate-y-0.5 hover:bg-violet-700">Get started</a>
            </div>
        </div>
    </nav>

    <main>
        <section class="relative overflow-hidden pt-32 lg:pt-40">
            <div class="absolute inset-0 grid-bg"></div>
            <div class="hero-orb absolute -right-40 top-10 h-[650px] w-[650px]"></div>
            <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-5 pb-24 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:pb-32">
                <div>
                    <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-4 py-2 text-xs font-bold uppercase tracking-[.16em] text-violet-700">
                        <span class="h-2 w-2 rounded-full bg-violet-600"></span> Performance marketing infrastructure
                    </div>
                    <h1 class="max-w-4xl text-5xl font-black leading-[.98] tracking-[-.045em] text-gray-950 sm:text-6xl lg:text-7xl">
                        Turn your network into your <span class="gradient-text">sales force.</span>
                    </h1>
                    <p class="mt-7 max-w-2xl text-lg leading-8 text-gray-600 sm:text-xl">
                        LAMI AI Business Marketing gives businesses everything they need to create affiliate programs, recruit partners, track referrals and reward people for generating real sales.
                    </p>
                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-2xl bg-gray-950 px-6 py-4 font-bold text-white shadow-xl shadow-gray-900/20 transition hover:-translate-y-1 hover:bg-violet-700">Create an Affiliate Program <span class="ml-2">→</span></a>
                        <a href="{{ route('partner.apply') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 bg-white px-6 py-4 font-bold text-gray-800 transition hover:border-violet-300 hover:text-violet-700">Become an Affiliate</a>
                    </div>
                    <div class="mt-8 flex flex-wrap gap-x-7 gap-y-3 text-sm font-semibold text-gray-500">
                        <span>✓ Performance-based</span><span>✓ Real-time tracking</span><span>✓ Automated commissions</span>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -inset-6 rounded-[2rem] bg-violet-500/10 blur-3xl"></div>
                    <div class="relative rounded-[2rem] border border-gray-200 bg-white p-4 shadow-2xl shadow-violet-950/10">
                        <div class="rounded-[1.5rem] bg-gray-950 p-5 text-white sm:p-7">
                            <div class="flex items-center justify-between">
                                <div><p class="text-xs font-bold uppercase tracking-widest text-gray-400">Affiliate program</p><p class="mt-1 font-bold">Growth Program</p></div>
                                <span class="rounded-full bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-300">● Active</span>
                            </div>
                            <div class="mt-7 grid grid-cols-2 gap-3">
                                <div class="rounded-2xl bg-white/5 p-4"><p class="text-xs text-gray-400">Revenue generated</p><p class="mt-2 text-2xl font-black">₦4.85m</p><p class="mt-1 text-xs font-semibold text-emerald-300">↑ 24.8%</p></div>
                                <div class="rounded-2xl bg-white/5 p-4"><p class="text-xs text-gray-400">Affiliate sales</p><p class="mt-2 text-2xl font-black">186</p><p class="mt-1 text-xs text-gray-400">this month</p></div>
                                <div class="rounded-2xl bg-white/5 p-4"><p class="text-xs text-gray-400">Active affiliates</p><p class="mt-2 text-2xl font-black">74</p><p class="mt-1 text-xs text-gray-400">partners</p></div>
                                <div class="rounded-2xl bg-white/5 p-4"><p class="text-xs text-gray-400">Commissions</p><p class="mt-2 text-2xl font-black">₦727k</p><p class="mt-1 text-xs text-gray-400">earned by partners</p></div>
                            </div>
                            <div class="mt-4 rounded-2xl bg-white/5 p-5">
                                <div class="flex justify-between text-xs font-bold text-gray-400"><span>Sales performance</span><span>Last 30 days</span></div>
                                <div class="mt-5 flex h-28 items-end gap-1.5">
                                    <div class="h-[28%] flex-1 rounded-t bg-violet-400/50"></div><div class="h-[40%] flex-1 rounded-t bg-violet-400/50"></div><div class="h-[34%] flex-1 rounded-t bg-violet-400/50"></div><div class="h-[52%] flex-1 rounded-t bg-violet-400/60"></div><div class="h-[48%] flex-1 rounded-t bg-violet-400/60"></div><div class="h-[68%] flex-1 rounded-t bg-violet-400/70"></div><div class="h-[62%] flex-1 rounded-t bg-violet-400/70"></div><div class="h-[82%] flex-1 rounded-t bg-violet-400/80"></div><div class="h-[75%] flex-1 rounded-t bg-violet-400/80"></div><div class="h-full flex-1 rounded-t bg-violet-400"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-y border-gray-200 bg-white">
            <div class="mx-auto grid max-w-7xl grid-cols-2 divide-x divide-gray-200 sm:grid-cols-4">
                <div class="p-7 text-center"><p class="text-2xl font-black text-gray-950">One</p><p class="mt-1 text-xs font-bold uppercase tracking-widest text-gray-500">Platform</p></div>
                <div class="p-7 text-center"><p class="text-2xl font-black text-gray-950">Multiple</p><p class="mt-1 text-xs font-bold uppercase tracking-widest text-gray-500">Programs</p></div>
                <div class="p-7 text-center"><p class="text-2xl font-black text-gray-950">Real-time</p><p class="mt-1 text-xs font-bold uppercase tracking-widest text-gray-500">Tracking</p></div>
                <div class="p-7 text-center"><p class="text-2xl font-black text-gray-950">Performance</p><p class="mt-1 text-xs font-bold uppercase tracking-widest text-gray-500">Marketing</p></div>
            </div>
        </section>

        <section id="businesses" class="mx-auto max-w-7xl px-5 py-24 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <div><span class="text-sm font-black uppercase tracking-[.2em] text-violet-600">For businesses</span><h2 class="mt-4 text-4xl font-black tracking-tight text-gray-950 sm:text-5xl">Build a sales network without building a massive sales team.</h2><p class="mt-6 text-lg leading-8 text-gray-600">Create your own affiliate program and turn creators, marketers, loyal customers and communities into revenue-generating partners.</p><a href="{{ route('register') }}" class="mt-8 inline-flex font-bold text-violet-700">Launch your program <span class="ml-2">→</span></a></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm"><div class="text-2xl">⚡</div><h3 class="mt-5 font-black">Launch fast</h3><p class="mt-2 text-sm leading-6 text-gray-500">Create programs, add products and define your commission structure.</p></div>
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm"><div class="text-2xl">👥</div><h3 class="mt-5 font-black">Recruit partners</h3><p class="mt-2 text-sm leading-6 text-gray-500">Build an affiliate network around the people who can reach your buyers.</p></div>
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm"><div class="text-2xl">📈</div><h3 class="mt-5 font-black">Track sales</h3><p class="mt-2 text-sm leading-6 text-gray-500">Know which partners and referrals are driving revenue.</p></div>
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm"><div class="text-2xl">💸</div><h3 class="mt-5 font-black">Reward results</h3><p class="mt-2 text-sm leading-6 text-gray-500">Manage commissions and payouts based on actual performance.</p></div>
                </div>
            </div>
        </section>

        <section id="affiliates" class="bg-gray-950 text-white">
            <div class="mx-auto grid max-w-7xl gap-14 px-5 py-24 lg:grid-cols-[.9fr_1.1fr] lg:px-8">
                <div><span class="text-sm font-black uppercase tracking-[.2em] text-violet-300">For affiliates</span><h2 class="mt-4 text-4xl font-black tracking-tight sm:text-5xl">Your audience is an asset. Put it to work.</h2><p class="mt-6 text-lg leading-8 text-gray-400">Discover products worth promoting, get unique referral links, share them anywhere and earn when your referrals convert.</p><a href="{{ route('partner.apply') }}" class="mt-8 inline-flex rounded-2xl bg-white px-6 py-4 font-black text-gray-950 transition hover:bg-violet-100">Explore affiliate opportunities →</a></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-7"><span class="text-3xl">🔗</span><h3 class="mt-6 text-xl font-black">Your unique links</h3><p class="mt-2 leading-7 text-gray-400">Share trackable links and know where your conversions come from.</p></div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-7"><span class="text-3xl">📊</span><h3 class="mt-6 text-xl font-black">Know your numbers</h3><p class="mt-2 leading-7 text-gray-400">Monitor referrals, sales and commissions from your dashboard.</p></div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-7"><span class="text-3xl">💰</span><h3 class="mt-6 text-xl font-black">Earn commissions</h3><p class="mt-2 leading-7 text-gray-400">Get rewarded according to the commission rules of each program.</p></div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-7"><span class="text-3xl">🚀</span><h3 class="mt-6 text-xl font-black">Grow with products</h3><p class="mt-2 leading-7 text-gray-400">Choose programs that fit your audience, niche and influence.</p></div>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="mx-auto max-w-7xl px-5 py-24 lg:px-8">
            <div class="mx-auto max-w-2xl text-center"><span class="text-sm font-black uppercase tracking-[.2em] text-violet-600">How it works</span><h2 class="mt-4 text-4xl font-black tracking-tight text-gray-950 sm:text-5xl">A simple engine for performance-driven growth.</h2></div>
            <div class="mt-16 grid gap-6 md:grid-cols-5">
                @foreach ([['01','Create','A business creates an affiliate program and defines its products and commission rules.'],['02','Join','Affiliates discover programs and join the ones that fit their audience.'],['03','Promote','Partners receive unique referral links and share products with their networks.'],['04','Sell','Customers purchase through affiliate referrals and the platform attributes the sale.'],['05','Reward','Commissions are recorded and affiliates can request payouts.']] as $step)
                    <div class="relative rounded-3xl border border-gray-200 bg-white p-6 shadow-sm"><span class="text-xs font-black text-violet-600">{{ $step[0] }}</span><h3 class="mt-4 text-lg font-black">{{ $step[1] }}</h3><p class="mt-2 text-sm leading-6 text-gray-500">{{ $step[2] }}</p></div>
                @endforeach
            </div>
        </section>

        <section id="features" class="bg-violet-50/60">
            <div class="mx-auto max-w-7xl px-5 py-24 lg:px-8">
                <div class="max-w-2xl"><span class="text-sm font-black uppercase tracking-[.2em] text-violet-600">Everything in one place</span><h2 class="mt-4 text-4xl font-black tracking-tight text-gray-950 sm:text-5xl">The infrastructure behind your affiliate channel.</h2></div>
                <div class="mt-12 grid gap-5 md:grid-cols-3">
                    @foreach ([['Affiliate programs','Launch and manage multiple programs from one platform.'],['Product management','Choose the products affiliates can promote and keep offers organized.'],['Commission engine','Set percentage or fixed commissions and flexible program rules.'],['Referral tracking','Attribute referrals and purchases to the right affiliate.'],['Affiliate management','Approve, manage and monitor the people selling your products.'],['Payout management','Move commissions through approval, payable and payout workflows.']] as $feature)
                        <div class="rounded-3xl border border-violet-100 bg-white p-7"><div class="mb-5 grid h-11 w-11 place-items-center rounded-xl bg-violet-100 font-black text-violet-700">✓</div><h3 class="text-xl font-black text-gray-950">{{ $feature[0] }}</h3><p class="mt-2 leading-7 text-gray-500">{{ $feature[1] }}</p></div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="programs" class="mx-auto max-w-7xl px-5 py-24 lg:px-8">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end"><div><span class="text-sm font-black uppercase tracking-[.2em] text-violet-600">Live marketplace</span><h2 class="mt-4 text-4xl font-black tracking-tight text-gray-950">Programs ready for affiliates.</h2><p class="mt-3 text-gray-600">Businesses publish programs. Affiliates choose the opportunities that fit them.</p></div><a href="{{ route('partner.apply') }}" class="font-bold text-violet-700">Become an affiliate →</a></div>
            @if(isset($programs) && $programs->count())
                <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($programs as $program)
                        @php $rule = $program->commissionRules->first(); @endphp
                        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="flex items-start justify-between gap-4"><div class="grid h-12 w-12 place-items-center rounded-2xl bg-violet-100 font-black text-violet-700">{{ strtoupper(substr($program->name,0,1)) }}</div><span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Active</span></div>
                            <h3 class="mt-6 text-xl font-black">{{ $program->name }}</h3><p class="mt-2 line-clamp-2 text-sm leading-6 text-gray-500">{{ $program->description ?: 'Promote products from this business and earn commission on qualifying sales.' }}</p>
                            <div class="mt-6 flex items-end justify-between border-t border-gray-100 pt-5"><div><p class="text-xs font-bold uppercase tracking-wider text-gray-400">Commission</p><p class="mt-1 text-2xl font-black text-gray-950">{{ $rule ? rtrim(rtrim(number_format($rule->value, 2), '0'), '.') . '%' : 'Flexible' }}</p></div><a href="{{ route('partner.apply') }}" class="rounded-xl bg-gray-950 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-700">Join program</a></div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-10 rounded-3xl border border-dashed border-gray-300 bg-white p-12 text-center"><p class="text-lg font-black text-gray-900">Programs are coming soon.</p><p class="mt-2 text-gray-500">Businesses will publish their affiliate opportunities here.</p></div>
            @endif
        </section>

        <section class="px-5 pb-24 lg:px-8">
            <div class="mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-violet-700 to-fuchsia-700 px-7 py-16 text-center text-white shadow-2xl shadow-violet-700/20 sm:px-12">
                <p class="text-sm font-black uppercase tracking-[.2em] text-violet-200">Ready to grow?</p><h2 class="mx-auto mt-4 max-w-3xl text-4xl font-black tracking-tight sm:text-5xl">Build your affiliate channel. Or start earning from one.</h2><p class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-violet-100">LAMI AI connects businesses that want more sales with people who know how to generate them.</p><div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row"><a href="{{ route('register') }}" class="rounded-2xl bg-white px-7 py-4 font-black text-violet-700 hover:bg-violet-50">I'm a Business →</a><a href="{{ route('partner.apply') }}" class="rounded-2xl border border-white/30 bg-white/10 px-7 py-4 font-black text-white hover:bg-white/20">I'm an Affiliate →</a></div>
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:grid-cols-2 lg:grid-cols-4 lg:px-8">
            <div class="sm:col-span-2"><div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-xl bg-gray-950 font-black text-white">L</span><span class="text-xl font-black">LAMI <span class="text-violet-600">AI</span></span></div><p class="mt-5 max-w-md leading-7 text-gray-500">Business marketing infrastructure for performance-driven growth. Businesses create programs. Affiliates create sales.</p></div>
            <div><h3 class="font-black text-gray-950">Platform</h3><div class="mt-4 space-y-3 text-sm font-semibold text-gray-500"><a class="block hover:text-violet-600" href="#features">Features</a><a class="block hover:text-violet-600" href="#programs">Programs</a><a class="block hover:text-violet-600" href="#how-it-works">How it works</a></div></div>
            <div><h3 class="font-black text-gray-950">Get started</h3><div class="mt-4 space-y-3 text-sm font-semibold text-gray-500"><a class="block hover:text-violet-600" href="{{ route('register') }}">Create a program</a><a class="block hover:text-violet-600" href="{{ route('partner.apply') }}">Become an affiliate</a><a class="block hover:text-violet-600" href="{{ route('login') }}">Log in</a></div></div>
        </div>
        <div class="border-t border-gray-100"><div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-6 text-sm text-gray-400 sm:flex-row sm:justify-between lg:px-8"><span>© {{ date('Y') }} LAMI AI Business Marketing.</span><span>Built for businesses. Powered by partnerships.</span></div></div>
    </footer>
</body>
</html>
