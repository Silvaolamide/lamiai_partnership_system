@props(['count' => 0, 'role' => 'business'])

@if(auth()->check() && auth()->user()->hasRole('partner') && auth()->user()->hasRole('customer'))
    <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-violet-200 bg-violet-50 p-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">You have two modes</p>
            <p class="mt-1 text-sm font-semibold text-violet-950">Switch between buying products and growing your partner business without signing out.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('marketplace.products') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-black text-blue-700 shadow-sm ring-1 ring-blue-200 hover:bg-blue-50">🛍️ Product Marketplace</a>
            @if($role === 'partner')
                <a href="{{ route('customer.dashboard') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-black text-violet-700 shadow-sm ring-1 ring-violet-200 hover:bg-violet-100">🛍️ Switch to Customer Mode →</a>
            @elseif($role === 'customer')
                <a href="{{ route('partner.dashboard') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-black text-white shadow-sm hover:bg-violet-700">🤝 Switch to Partner/Affiliate Mode →</a>
            @endif
        </div>
    </div>
@endif

@if(auth()->check() && auth()->user()->hasRole('partner'))
    <style>
        /* Partner navigation is intentionally self-contained here because this component
           is present on every partner dashboard render. The desktop sidebar and mobile
           drawer replace the old horizontal navigation without changing dashboard content. */
        body:has(.partner-sidebar) { padding-left: 17rem; }
        body:has(.partner-sidebar) > .mx-auto { max-width: none; margin-left: 0; margin-right: 0; }
        body:has(.partner-sidebar) nav[aria-label="Partner navigation"] { display: none; }
        .partner-sidebar { width: 16rem; }
        .partner-sidebar-overlay { display: none; }
        @media (max-width: 767px) {
            body:has(.partner-sidebar) { padding-left: 0; }
            body:has(.partner-sidebar) > .mx-auto { width: 100%; }
            .partner-sidebar { transform: translateX(-105%); transition: transform .25s ease; }
            .partner-sidebar.is-open { transform: translateX(0); }
            .partner-sidebar-overlay { display: block; opacity: 0; pointer-events: none; transition: opacity .2s ease; }
            .partner-sidebar-overlay.is-open { opacity: 1; pointer-events: auto; }
            .partner-mobile-header { display: flex !important; }
        }
        @media (min-width: 768px) {
            .partner-mobile-header { display: none !important; }
        }
    </style>

    <div class="partner-mobile-header sticky top-0 z-50 hidden items-center justify-between border-b border-slate-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur" style="margin: -1rem -1rem 1rem -1rem;">
        <div class="flex items-center gap-3">
            <button type="button" onclick="togglePartnerSidebar()" class="grid h-10 w-10 place-items-center rounded-xl bg-slate-950 text-xl font-black text-white" aria-label="Open partner navigation">☰</button>
            <div><p class="text-xs font-black uppercase tracking-widest text-violet-600">AIPM</p><p class="text-sm font-black text-slate-950">Partner Hub</p></div>
        </div>
        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">Partner Mode</span>
    </div>

    <div class="partner-sidebar-overlay fixed inset-0 z-[60] bg-slate-950/50" onclick="togglePartnerSidebar()" aria-hidden="true"></div>

    <aside class="partner-sidebar fixed inset-y-0 left-0 z-[70] flex flex-col border-r border-slate-800 bg-slate-950 text-white shadow-2xl" aria-label="Partner sidebar navigation">
        <div class="flex h-20 items-center justify-between border-b border-white/10 px-5">
            <a href="{{ route('partner.dashboard') }}" class="flex items-center gap-3" onclick="closePartnerSidebar()">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-violet-500 text-lg font-black shadow-lg shadow-violet-500/25">A</span>
                <div><p class="text-lg font-black tracking-tight">AIPM</p><p class="text-[10px] font-bold uppercase tracking-[.18em] text-slate-400">Partner Hub</p></div>
            </a>
            <button type="button" class="md:hidden text-slate-400 hover:text-white" onclick="togglePartnerSidebar()" aria-label="Close partner navigation">✕</button>
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-5">
            <p class="px-3 pb-2 text-[10px] font-black uppercase tracking-[.2em] text-slate-500">Main</p>
            <nav class="space-y-1" aria-label="Partner sidebar links">
                <a href="{{ route('partner.dashboard') }}" onclick="closePartnerSidebar()" class="flex items-center gap-3 rounded-xl bg-violet-600 px-3 py-3 text-sm font-black shadow-lg shadow-violet-900/20"><span class="w-6 text-center">⌂</span> Dashboard</a>
                <a href="{{ route('partner.marketplace.index') }}" onclick="closePartnerSidebar()" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold text-slate-300 transition hover:bg-white/10 hover:text-white"><span class="w-6 text-center">◈</span> Find Programs</a>
                <a href="{{ route('network.index') }}" onclick="closePartnerSidebar()" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold text-slate-300 transition hover:bg-white/10 hover:text-white"><span class="w-6 text-center">♧</span> My Network</a>
                <a href="{{ route('partner.payouts.index') }}" onclick="closePartnerSidebar()" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold text-slate-300 transition hover:bg-white/10 hover:text-white"><span class="w-6 text-center">₦</span> Earnings &amp; Payouts</a>
                @if($programStats->isNotEmpty())
                    @php $sidebarPartner = $programStats->first()['partner']; $sidebarStoreUrl = route('partner.storefront', ['partnerCode' => $sidebarPartner->partner_code]); @endphp
                    <a href="{{ $sidebarStoreUrl }}" target="_blank" rel="noopener noreferrer" onclick="closePartnerSidebar()" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold text-slate-300 transition hover:bg-white/10 hover:text-white"><span class="w-6 text-center">↗</span> My Store <span class="ml-auto text-xs text-slate-500">New tab</span></a>
                @endif
            </nav>

            <p class="mt-7 px-3 pb-2 text-[10px] font-black uppercase tracking-[.2em] text-slate-500">Quick actions</p>
            <div class="space-y-2">
                <a href="{{ route('partner.marketplace.index') }}" onclick="closePartnerSidebar()" class="block rounded-xl border border-white/10 bg-white/5 p-3 transition hover:bg-white/10"><p class="text-xs font-black text-violet-300">01 · PROMOTE</p><p class="mt-1 text-sm font-bold">Choose a program</p></a>
                <a href="{{ route('network.index') }}" onclick="closePartnerSidebar()" class="block rounded-xl border border-white/10 bg-white/5 p-3 transition hover:bg-white/10"><p class="text-xs font-black text-emerald-300">02 · RECRUIT</p><p class="mt-1 text-sm font-bold">Build your network</p></a>
                <a href="{{ route('partner.payouts.index') }}" onclick="closePartnerSidebar()" class="block rounded-xl border border-white/10 bg-white/5 p-3 transition hover:bg-white/10"><p class="text-xs font-black text-amber-300">03 · EARN</p><p class="mt-1 text-sm font-bold">Track your money</p></a>
            </div>
        </div>

        <div class="border-t border-white/10 p-3">
            <a href="{{ route('home') }}" onclick="closePartnerSidebar()" class="mb-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-slate-400 hover:bg-white/10 hover:text-white"><span class="w-6 text-center">◉</span> Visit AIPM</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl bg-rose-500/10 px-3 py-2.5 text-sm font-black text-rose-300 hover:bg-rose-500/20"><span class="w-6 text-center">⇥</span> Log out</button>
            </form>
        </div>
    </aside>

    <script>
        function togglePartnerSidebar() {
            document.querySelector('.partner-sidebar')?.classList.toggle('is-open');
            document.querySelector('.partner-sidebar-overlay')?.classList.toggle('is-open');
        }
        function closePartnerSidebar() {
            document.querySelector('.partner-sidebar')?.classList.remove('is-open');
            document.querySelector('.partner-sidebar-overlay')?.classList.remove('is-open');
        }
    </script>
@endif

@if($count > 0)
    @php
        $isAdmin = $role === 'admin';
        $isPartner = $role === 'partner';
        $title = $isAdmin
            ? 'PAYMENT CONFIRMATION REQUIRED'
            : ($isPartner ? 'YOUR REFERRAL HAS A PENDING PAYMENT' : 'PAYMENT CONFIRMATION IN PROGRESS');
        $description = $isAdmin
            ? "{$count} payment submission" . ($count === 1 ? '' : 's') . " require" . ($count === 1 ? 's' : '') . " your review before the order can become a confirmed sale."
            : ($isPartner
                ? "{$count} order" . ($count === 1 ? '' : 's') . " from your referrals have payment proof waiting for admin confirmation. Commission is only finalized after confirmation."
                : "{$count} order" . ($count === 1 ? '' : 's') . " from your business have payment proof waiting for admin confirmation. These are not yet completed sales.");
        $url = $isAdmin ? route('admin.payments.index') : ($isPartner ? route('partner.dashboard').'#programs' : route('business.sales.index'));
        $button = $isAdmin ? 'Review payments' : ($isPartner ? 'View referral activity' : 'View sales');
    @endphp

    <section class="relative mb-6 overflow-hidden rounded-3xl border border-amber-300 bg-gradient-to-r from-amber-50 via-white to-orange-50 p-5 shadow-md ring-2 ring-amber-100 sm:p-6">
        <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-amber-300/30 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-amber-500 text-xl font-black text-white shadow-lg shadow-amber-500/25">!</div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">Action required</p>
                        <span class="rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-white">{{ $count }} pending</span>
                    </div>
                    <h2 class="mt-1 text-lg font-black text-slate-950 sm:text-xl">{{ $title }}</h2>
                    <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">{{ $description }}</p>
                </div>
            </div>
            <a href="{{ $url }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-slate-950 px-5 py-3 text-sm font-black text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-amber-600">{{ $button }} <span class="ml-2">→</span></a>
        </div>
    </section>
@endif
