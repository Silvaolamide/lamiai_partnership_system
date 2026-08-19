@props(['count' => 0, 'role' => 'business'])

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
