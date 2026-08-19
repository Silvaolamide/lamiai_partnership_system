<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} | Learn. Create. Monetize.</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .hero-bg { background: radial-gradient(circle at 15% 10%, rgba(124,58,237,.24), transparent 32%), radial-gradient(circle at 90% 20%, rgba(37,99,235,.20), transparent 30%), linear-gradient(135deg,#09090f 0%,#111827 55%,#0f172a 100%); }
        .gradient-cta { background: linear-gradient(135deg,#7c3aed 0%,#2563eb 100%); }
        .gradient-gold { background: linear-gradient(135deg,#f59e0b 0%,#f97316 100%); }
        .shadow-glow { box-shadow: 0 24px 70px rgba(37,99,235,.20); }
        .lift { transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
        .lift:hover { transform: translateY(-5px); box-shadow: 0 20px 45px rgba(15,23,42,.12); }
        .sticky-buy { box-shadow: 0 -10px 30px rgba(15,23,42,.10); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">

@if($referralError)
    <div class="bg-red-600 px-4 py-3 text-center text-sm font-semibold text-white">Referral notice: {{ $referralError }}</div>
@elseif($referralProcessed && $referringPartner)
    <div class="bg-emerald-600 px-4 py-3 text-center text-sm font-semibold text-white">✓ You were referred by {{ $referringPartner->user->name }}. Their referral is active for this purchase.</div>
@endif

<!-- HERO -->
<header class="hero-bg relative overflow-hidden text-white">
    <div class="absolute inset-0 opacity-20" style="background-image:linear-gradient(rgba(255,255,255,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.08) 1px,transparent 1px);background-size:44px 44px"></div>
    <div class="relative mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-24 lg:px-10">
        <div class="grid items-center gap-12 lg:grid-cols-[1.15fr_.85fr]">
            <div>
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-bold backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span> Practical AI Training • Built for Creators & Entrepreneurs
                </div>
                <p class="mb-4 text-sm font-black uppercase tracking-[.22em] text-violet-300">AI for Naija</p>
                <h1 class="max-w-4xl text-4xl font-black leading-[1.02] tracking-tight sm:text-6xl lg:text-7xl">Turn your ideas into <span class="text-violet-300">professional AI-powered videos.</span></h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300 sm:text-xl">Learn a practical workflow for using AI to create compelling stories, scenes and videos — without needing a traditional production team.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="#buy" class="gradient-cta inline-flex items-center justify-center rounded-2xl px-7 py-4 text-base font-black shadow-glow transition hover:scale-[1.02]">YES — I WANT TO LEARN AI FILMMAKING <span class="ml-2">→</span></a>
                    <a href="#inside" class="inline-flex items-center justify-center rounded-2xl border border-white/20 bg-white/5 px-7 py-4 text-base font-bold text-white backdrop-blur hover:bg-white/10">See what's inside</a>
                </div>
                <div class="mt-7 flex flex-wrap gap-x-6 gap-y-3 text-sm text-slate-300">
                    <span>✓ Practical lessons</span><span>✓ Learn by doing</span><span>✓ One-time investment</span><span>✓ Secure checkout</span>
                </div>
            </div>
            <div id="buy" class="rounded-3xl border border-white/10 bg-white p-7 text-slate-900 shadow-2xl sm:p-9">
                <div class="mb-5 flex items-center justify-between"><span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-black uppercase tracking-wider text-violet-700">Complete Course</span><span class="text-sm font-bold text-emerald-600">Limited-time offer</span></div>
                <h2 class="text-2xl font-black sm:text-3xl">{{ $product->name }}</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $product->description ?: 'A practical AI filmmaking experience designed to help you create, publish and monetize better video content.' }}</p>
                <div class="my-7 border-y border-slate-200 py-6">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">One-time investment</p>
                    <div class="mt-1 flex items-end gap-2"><span class="text-5xl font-black tracking-tight">₦{{ number_format($product->price, 0) }}</span><span class="pb-2 text-sm text-slate-500">NGN</span></div>
                    <p class="mt-2 text-sm font-semibold text-slate-500">Pay once. Focus on learning.</p>
                </div>
                <form action="{{ route('checkout.create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="gradient-cta w-full rounded-2xl px-6 py-5 text-lg font-black text-white shadow-lg transition hover:scale-[1.01] hover:shadow-xl">GET ACCESS NOW →</button>
                </form>
                <p class="mt-4 text-center text-xs font-semibold text-slate-500">🔒 Secure checkout • Your referral attribution is preserved</p>
                @if($referralProcessed && $referringPartner)
                    <div class="mt-5 rounded-2xl bg-emerald-50 p-4 text-center text-sm text-emerald-800"><strong>{{ $referringPartner->user->name }}</strong> referred you to this course.</div>
                @endif
            </div>
        </div>
    </div>
</header>

<!-- PROBLEM -->
<section class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:px-10 lg:py-24">
    <div class="max-w-3xl"><p class="text-sm font-black uppercase tracking-[.2em] text-violet-600">The opportunity</p><h2 class="mt-3 text-3xl font-black tracking-tight sm:text-5xl">AI is changing content creation. Are you ready to use it?</h2><p class="mt-5 text-lg leading-8 text-slate-600">You don't need another course filled with theory. You need a clear path from idea to finished video — and the confidence to actually use the tools.</p></div>
    <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
        <div class="lift rounded-3xl border border-slate-200 bg-white p-6"><div class="text-2xl">💡</div><h3 class="mt-4 font-black">Ideas without execution</h3><p class="mt-2 text-sm leading-6 text-slate-600">Turn concepts in your head into visual stories people can see.</p></div>
        <div class="lift rounded-3xl border border-slate-200 bg-white p-6"><div class="text-2xl">💸</div><h3 class="mt-4 font-black">Production is expensive</h3><p class="mt-2 text-sm leading-6 text-slate-600">Explore AI workflows that can reduce the barrier to producing content.</p></div>
        <div class="lift rounded-3xl border border-slate-200 bg-white p-6"><div class="text-2xl">⏱️</div><h3 class="mt-4 font-black">Too much time</h3><p class="mt-2 text-sm leading-6 text-slate-600">Build a repeatable process instead of figuring everything out from scratch.</p></div>
        <div class="lift rounded-3xl border border-slate-200 bg-white p-6"><div class="text-2xl">🚀</div><h3 class="mt-4 font-black">A new skill economy</h3><p class="mt-2 text-sm leading-6 text-slate-600">Learn a modern skill you can apply to your own brand or offer to clients.</p></div>
    </div>
</section>

<!-- TRANSFORMATION -->
<section class="bg-slate-950 px-5 py-16 text-white sm:px-8 lg:py-24">
    <div class="mx-auto max-w-7xl">
        <div class="mx-auto max-w-3xl text-center"><p class="text-sm font-black uppercase tracking-[.2em] text-violet-300">The transformation</p><h2 class="mt-3 text-3xl font-black sm:text-5xl">From a simple idea to a complete AI video workflow.</h2><p class="mt-5 text-lg leading-8 text-slate-400">Learn the process, not just a collection of tools.</p></div>
        <div class="mt-12 grid gap-4 md:grid-cols-5">
            @foreach([['01','IDEA','Start with your story'],['02','CREATE','Build characters & scenes'],['03','GENERATE','Turn prompts into video'],['04','ENHANCE','Voice, sound & editing'],['05','PUBLISH','Create content people want']] as $step)
                <div class="relative rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur"><span class="text-xs font-black text-violet-300">{{ $step[0] }}</span><h3 class="mt-5 text-lg font-black">{{ $step[1] }}</h3><p class="mt-2 text-sm leading-6 text-slate-400">{{ $step[2] }}</p></div>
            @endforeach
        </div>
    </div>
</section>

<!-- INSIDE -->
<section id="inside" class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:px-10 lg:py-24">
    <div class="grid gap-12 lg:grid-cols-[.8fr_1.2fr] lg:items-start">
        <div class="lg:sticky lg:top-8"><p class="text-sm font-black uppercase tracking-[.2em] text-violet-600">Inside the course</p><h2 class="mt-3 text-3xl font-black sm:text-5xl">Learn. Create. Apply.</h2><p class="mt-5 text-lg leading-8 text-slate-600">A practical learning path built around what you actually want to do with AI filmmaking.</p><a href="#buy" class="gradient-cta mt-7 inline-flex rounded-2xl px-6 py-4 font-black text-white">GET STARTED →</a></div>
        <div class="space-y-4">
            @foreach([['01','AI FILMMAKING FUNDAMENTALS','Understand the AI-powered video landscape and build the right workflow.'],['02','STORY & SCRIPT','Turn an idea into a compelling story and usable production prompts.'],['03','CHARACTERS & SCENES','Create visual concepts, characters and environments that support your story.'],['04','AI VIDEO GENERATION','Learn how to move from prompts and images to dynamic video scenes.'],['05','VOICE, MUSIC & SOUND','Bring your scenes together with voice, sound and finishing techniques.'],['06','CREATE & MONETIZE','Apply your new skill to content creation, personal branding and client opportunities.']] as $module)
                <div class="lift flex gap-5 rounded-3xl border border-slate-200 bg-white p-6 sm:p-7"><div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-sm font-black text-violet-700">{{ $module[0] }}</div><div><h3 class="text-lg font-black">{{ $module[1] }}</h3><p class="mt-2 leading-7 text-slate-600">{{ $module[2] }}</p></div></div>
            @endforeach
        </div>
    </div>
</section>

<!-- OUTCOMES -->
<section class="bg-white px-5 py-16 sm:px-8 lg:py-24">
    <div class="mx-auto max-w-7xl">
        <div class="max-w-3xl"><p class="text-sm font-black uppercase tracking-[.2em] text-blue-600">What you can do</p><h2 class="mt-3 text-3xl font-black sm:text-5xl">Don't just learn AI. Put it to work.</h2></div>
        <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach(['Create social media videos faster','Turn ideas into cinematic stories','Build an AI-powered content workflow','Create videos for your own brand','Explore AI video services for clients','Develop a modern, marketable creative skill'] as $benefit)
                <div class="rounded-3xl bg-slate-50 p-6"><div class="flex gap-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-black text-emerald-700">✓</span><p class="font-bold leading-6">{{ $benefit }}</p></div></div>
            @endforeach
        </div>
    </div>
</section>

<!-- WHO -->
<section class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:px-10 lg:py-24">
    <div class="rounded-[2rem] bg-gradient-to-br from-violet-50 via-white to-blue-50 p-8 sm:p-12 lg:p-16">
        <div class="max-w-3xl"><p class="text-sm font-black uppercase tracking-[.2em] text-violet-600">Built for you</p><h2 class="mt-3 text-3xl font-black sm:text-5xl">You don't need to be a filmmaker.</h2><p class="mt-5 text-lg leading-8 text-slate-600">If you have a story, a business, a phone or computer and the willingness to learn, you can start exploring what AI makes possible.</p></div>
        <div class="mt-10 flex flex-wrap gap-3">@foreach(['Content creators','Entrepreneurs','Social media managers','Students','Aspiring filmmakers','Digital marketers','Business owners','Creative professionals'] as $audience)<span class="rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-bold shadow-sm">{{ $audience }}</span>@endforeach</div>
    </div>
</section>

<!-- OFFER -->
<section class="px-5 pb-24 sm:px-8 lg:px-10">
    <div class="mx-auto max-w-5xl overflow-hidden rounded-[2rem] bg-slate-950 text-white shadow-2xl">
        <div class="p-8 sm:p-12 lg:p-16">
            <div class="text-center"><p class="text-sm font-black uppercase tracking-[.2em] text-violet-300">Your investment</p><h2 class="mt-3 text-3xl font-black sm:text-5xl">Everything you need to start creating with AI.</h2></div>
            <div class="mx-auto mt-10 grid max-w-3xl gap-3 sm:grid-cols-2">
                @foreach(['Complete course experience','Practical demonstrations','Resources & learning assets','Project-based learning','Community/support access','Certificate of completion'] as $item)<div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-4"><span class="text-emerald-400">✓</span><span class="font-semibold text-slate-200">{{ $item }}</span></div>@endforeach
            </div>
            <div class="mx-auto mt-10 max-w-xl rounded-3xl bg-white p-7 text-center text-slate-900 sm:p-9">
                <p class="text-xs font-black uppercase tracking-wider text-slate-500">One-time payment</p><p class="mt-2 text-5xl font-black">₦{{ number_format($product->price, 0) }}</p><p class="mt-2 text-sm text-slate-500">Start learning without a recurring subscription.</p>
                <form action="{{ route('checkout.create') }}" method="POST" class="mt-6">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><button type="submit" class="gradient-cta w-full rounded-2xl px-6 py-5 text-lg font-black text-white">YES — GIVE ME ACCESS →</button></form>
                <p class="mt-4 text-xs font-semibold text-slate-500">🔒 Secure checkout</p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ / OBJECTIONS -->
<section class="mx-auto max-w-5xl px-5 pb-28 sm:px-8">
    <div class="text-center"><p class="text-sm font-black uppercase tracking-[.2em] text-blue-600">Questions?</p><h2 class="mt-3 text-3xl font-black sm:text-5xl">Before you decide...</h2></div>
    <div class="mt-10 space-y-4">
        @foreach([['“I know nothing about AI.”','That is exactly why a structured, practical workflow matters. The experience is designed to take you through the process rather than assume you already know everything.'],['“I am not a professional filmmaker.”','You do not need to be. The goal is to help you use modern AI tools to create compelling video content.'],['“Will this help me make money?”','The course teaches practical creative workflows and monetization opportunities. Results depend on how you apply the skill, your market and your consistency.'],['“Do I need expensive equipment?”','The focus is on AI-powered creation, so you can begin without building a traditional film production setup.']] as $faq)
            <details class="group rounded-3xl border border-slate-200 bg-white p-6"><summary class="cursor-pointer list-none pr-8 text-lg font-black">{{ $faq[0] }} <span class="float-right text-violet-600">+</span></summary><p class="mt-4 max-w-3xl leading-7 text-slate-600">{{ $faq[1] }}</p></details>
        @endforeach
    </div>
</section>

<!-- FINAL CTA -->
<section class="hero-bg px-5 py-16 text-center text-white sm:px-8 lg:py-24">
    <div class="mx-auto max-w-4xl"><p class="text-sm font-black uppercase tracking-[.2em] text-violet-300">Your next video can start here</p><h2 class="mt-4 text-4xl font-black tracking-tight sm:text-6xl">Stop watching AI transform video creation. Learn to create with it.</h2><p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-300">Learn the workflow. Create something real. Put your new skill to work.</p><a href="#buy" class="gradient-cta mt-8 inline-flex rounded-2xl px-8 py-5 text-lg font-black text-white shadow-glow transition hover:scale-[1.02]">GET AI FOR NAIJA NOW →</a></div>
</section>

<!-- MOBILE STICKY CTA -->
<div class="sticky-buy fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur md:hidden">
    <div class="flex items-center gap-3"><div class="min-w-0 flex-1"><p class="text-xs font-bold text-slate-500">{{ $product->name }}</p><p class="text-lg font-black">₦{{ number_format($product->price, 0) }}</p></div><form action="{{ route('checkout.create') }}" method="POST" class="shrink-0">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><button type="submit" class="gradient-cta rounded-xl px-5 py-3 text-sm font-black text-white">GET ACCESS →</button></form></div>
</div>

</body>
</html>
