<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the LAMI AI Partner Network</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display:none !important; }
        .mesh { background-image: radial-gradient(circle at 20% 20%, rgba(99,102,241,.16) 0, transparent 28%), radial-gradient(circle at 85% 10%, rgba(16,185,129,.13) 0, transparent 24%), radial-gradient(circle at 70% 80%, rgba(139,92,246,.12) 0, transparent 30%); }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-900 antialiased">
    <div class="min-h-screen mesh">
        <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg font-black text-slate-950 shadow-lg shadow-indigo-500/10">L</div>
                    <div>
                        <div class="text-lg font-extrabold tracking-tight text-white">LAMI AI</div>
                        <div class="text-[10px] font-semibold uppercase tracking-[.2em] text-slate-400">Partner Network</div>
                    </div>
                </a>
                <a href="{{ route('login') }}" class="rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:border-white/20 hover:bg-white/5">Already a partner? <span class="text-white">Sign in</span></a>
            </div>
        </header>

        <main class="mx-auto grid max-w-7xl gap-10 px-5 py-10 sm:px-8 lg:grid-cols-[.9fr_1.1fr] lg:items-start lg:gap-16 lg:py-16">
            <section class="pt-2 lg:sticky lg:top-10">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    PARTNER OPPORTUNITY
                </div>
                <h1 class="max-w-xl text-4xl font-black leading-[1.05] tracking-tight text-white sm:text-5xl lg:text-6xl">Turn your audience into <span class="text-indigo-300">income.</span></h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-slate-300">Join businesses on LAMI AI, promote products you believe in, and earn commissions when your referrals make successful purchases.</p>

                <div class="mt-9 grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="flex gap-4 rounded-2xl border border-white/10 bg-white/[.04] p-4 backdrop-blur">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-500/15 text-indigo-300">01</div>
                        <div><p class="font-bold text-white">Find products</p><p class="mt-1 text-sm text-slate-400">Discover programs that fit your audience.</p></div>
                    </div>
                    <div class="flex gap-4 rounded-2xl border border-white/10 bg-white/[.04] p-4 backdrop-blur">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-300">02</div>
                        <div><p class="font-bold text-white">Share &amp; sell</p><p class="mt-1 text-sm text-slate-400">Get your referral link and start promoting.</p></div>
                    </div>
                    <div class="flex gap-4 rounded-2xl border border-white/10 bg-white/[.04] p-4 backdrop-blur">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/15 text-violet-300">03</div>
                        <div><p class="font-bold text-white">Earn commissions</p><p class="mt-1 text-sm text-slate-400">Track sales and commissions from your dashboard.</p></div>
                    </div>
                </div>

                <div class="mt-8 flex items-center gap-3 text-sm text-slate-400">
                    <div class="flex -space-x-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-slate-950 bg-indigo-200 text-[10px] font-bold text-indigo-900">AI</span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-slate-950 bg-emerald-200 text-[10px] font-bold text-emerald-900">+</span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-slate-950 bg-violet-200 text-[10px] font-bold text-violet-900">$</span>
                    </div>
                    <span>One platform connecting businesses, partners and customers.</span>
                </div>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-2xl shadow-black/20 sm:p-8 lg:p-10">
                <div class="mb-8">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-[.16em] text-indigo-600">Get started</span>
                        <span class="text-xs font-medium text-slate-400">Takes about 2 minutes</span>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Create your partner account</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Complete your details below to apply for an affiliate partnership.</p>
                </div>

                @if($recruiterError)
                    <div class="mb-6 flex gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <span class="font-bold">!</span><p><strong>Referral issue:</strong> {{ $recruiterError }}</p>
                    </div>
                @elseif($recruiterPartner)
                    <div class="mb-6 rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm leading-6 text-indigo-800">
                        <div class="font-bold">You were invited by {{ $recruiterPartner->user->name }}</div>
                        <div class="mt-1">Once you are approved, {{ $recruiterPartner->user->name }} may earn a recruiter commission when you generate sales.</div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <p class="mb-2 font-bold">Please check the following:</p>
                        <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('partner.apply.store') }}" class="space-y-5">
                    @csrf
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="mb-2 block text-sm font-bold text-slate-700">Full name</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="e.g. Olamide Agunkejoye" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                        </div>
                        <div>
                            <label for="email" class="mb-2 block text-sm font-bold text-slate-700">Email address</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                        </div>
                        <div>
                            <label for="phone" class="mb-2 block text-sm font-bold text-slate-700">Phone / WhatsApp</label>
                            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="+234 800 000 0000" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-sm font-bold text-slate-700">Create password</label>
                            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Choose a secure password" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                        </div>
                        <div>
                            <label for="program_id" class="mb-2 block text-sm font-bold text-slate-700">Choose a program</label>
                            <select id="program_id" name="program_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                                <option value="">Select an affiliate program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" @selected(old('program_id') == $program->id)>{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if($recruiterCode)
                        <input type="hidden" name="recruiter_code" value="{{ $recruiterCode }}">
                    @endif

                    <div class="rounded-2xl bg-slate-50 p-4 text-xs leading-5 text-slate-500">
                        By applying, you agree to participate according to the selected program's terms and commission rules. Your application may require approval before you can promote products.
                    </div>

                    <button type="submit" class="group flex w-full items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-4 text-sm font-bold text-white shadow-lg shadow-slate-950/10 transition hover:-translate-y-0.5 hover:bg-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-500/20">
                        Apply to become a partner
                        <span class="transition group-hover:translate-x-1">→</span>
                    </button>
                </form>

                <p class="mt-6 text-center text-xs text-slate-400">Already have an account? <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Sign in here</a></p>
            </section>
        </main>
    </div>
</body>
</html>