<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">LAMI AI Partners</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight sm:text-4xl">Partner Dashboard</h1>
            <p class="mt-2 text-slate-600">Welcome back, {{ auth()->user()->name }}.</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold shadow-sm hover:bg-slate-50">My Profile</a>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Sales Value</p>
            <p class="mt-2 text-3xl font-bold">₦{{ number_format($totalSalesAmount, 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $totalSales }} completed order{{ $totalSales === 1 ? '' : 's' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Available Commission</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">₦{{ number_format($totalPending, 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Eligible for payout</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Paid Commission</p>
            <p class="mt-2 text-3xl font-bold">₦{{ number_format($totalPaid, 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Lifetime paid earnings</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Partners Recruited</p>
            <p class="mt-2 text-3xl font-bold">{{ $totalRecruited }}</p>
            <p class="mt-1 text-xs text-slate-500">Your active downline</p>
        </div>
    </section>

    <div class="mt-8 space-y-6">
        @forelse($programStats as $programStat)
            @php
                $program = $programStat['program'];
                $partner = $programStat['partner'];
                $rules = $programStat['program']->commissionRules->where('status', true)->where('event', 'sale');
                $level1Rule = $rules->where('level', 1)->sortByDesc('priority')->first();
                $level2Rule = $rules->where('level', 2)->sortByDesc('priority')->first();
            @endphp

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-6 text-white sm:px-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-slate-300">Partnership Program</p>
                            <h2 class="mt-1 text-2xl font-bold">{{ $program->name }}</h2>
                            <p class="mt-2 text-sm text-slate-300">Partner code: <span class="font-mono font-semibold text-white">{{ $partner->partner_code }}</span></p>
                        </div>
                        <span class="w-fit rounded-full bg-emerald-500/20 px-3 py-1 text-sm font-semibold text-emerald-200">Active Partner</span>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Available</p>
                            <p class="mt-1 text-2xl font-bold">₦{{ number_format($programStat['stats']['pending'], 2) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Paid</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-600">₦{{ number_format($programStat['stats']['paid'], 2) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Sales</p>
                            <p class="mt-1 text-2xl font-bold">{{ $programStat['paid_orders'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Recruited</p>
                            <p class="mt-1 text-2xl font-bold">{{ $programStat['recruited_partners_count'] }}</p>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-6 lg:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h3 class="font-bold">Promote Products</h3>
                                    <p class="mt-1 text-sm text-slate-500">Use your personal referral link.</p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-4">
                                @forelse($program->products as $product)
                                    <div class="rounded-xl bg-slate-50 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-semibold">{{ $product->name }}</p>
                                                <p class="mt-1 text-sm text-slate-500">₦{{ number_format($product->price, 2) }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex gap-2">
                                            <input id="product_link_{{ $partner->id }}_{{ $product->id }}" readonly value="{{ route('product.show', ['slug' => $product->slug]) }}?ref={{ $partner->partner_code }}" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600">
                                            <button type="button" onclick="copyText('product_link_{{ $partner->id }}_{{ $product->id }}', this)" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Copy</button>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">No products are currently attached to this program.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h3 class="font-bold">Recruit Partners</h3>
                            <p class="mt-1 text-sm text-slate-500">Invite other marketers. Their qualifying sales can generate your higher-level commission.</p>
                            <div class="mt-5 rounded-xl bg-slate-50 p-4">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recruitment link</label>
                                <div class="mt-2 flex gap-2">
                                    <input id="recruit_link_{{ $partner->id }}" readonly value="{{ route('partner.apply') }}?recruiter_code={{ $partner->partner_code }}" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600">
                                    <button type="button" onclick="copyText('recruit_link_{{ $partner->id }}', this)" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Copy</button>
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-blue-50 p-4">
                                    <p class="text-xs font-medium text-blue-700">Direct earnings</p>
                                    <p class="mt-1 text-xl font-bold text-blue-900">₦{{ number_format($programStat['direct_commission'], 2) }}</p>
                                    @if($level1Rule)
                                        <p class="mt-1 text-xs text-blue-700">{{ $level1Rule->commission_type === 'percentage' ? number_format($level1Rule->value, 2).'%' : '₦'.number_format($level1Rule->value, 2) }}</p>
                                    @endif
                                </div>
                                <div class="rounded-xl bg-violet-50 p-4">
                                    <p class="text-xs font-medium text-violet-700">Recruiter earnings</p>
                                    <p class="mt-1 text-xl font-bold text-violet-900">₦{{ number_format($programStat['recruiter_commission'], 2) }}</p>
                                    @if($level2Rule)
                                        <p class="mt-1 text-xs text-violet-700">{{ $level2Rule->commission_type === 'percentage' ? number_format($level2Rule->value, 2).'%' : '₦'.number_format($level2Rule->value, 2) }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @empty
            <section class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                <h2 class="text-2xl font-bold">No active partnership yet</h2>
                <p class="mx-auto mt-2 max-w-lg text-slate-600">Apply to an available partnership program to get your unique referral links and start earning.</p>
                <a href="{{ route('partner.apply') }}" class="mt-6 inline-flex rounded-xl bg-slate-900 px-6 py-3 font-semibold text-white hover:bg-slate-700">Browse Programs</a>
            </section>
        @endforelse
    </div>
</div>

<script>
function copyText(id, button) {
    const input = document.getElementById(id);
    navigator.clipboard.writeText(input.value).then(() => {
        const original = button.textContent;
        button.textContent = 'Copied!';
        button.classList.add('bg-emerald-600');
        setTimeout(() => {
            button.textContent = original;
            button.classList.remove('bg-emerald-600');
        }, 1800);
    });
}
</script>
</body>
</html>
