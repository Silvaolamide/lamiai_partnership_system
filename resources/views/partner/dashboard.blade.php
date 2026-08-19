                $rules = $program->commissionRules->where('status', true)->where('event', 'sale');
                $level1Rule = $rules->where('level', 1)->sortByDesc('priority')->first();
                $level2Rule = $rules->where('level', 2)->sortByDesc('priority')->first();
                $storefrontUrl = route('partner.storefront', ['partnerCode' => $partner->partner_code]);
            @endphp
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-6 sm:p-7">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div><p class="text-xs font-black uppercase tracking-[0.18em] text-violet-600">Partnership Program</p><h2 class="mt-1 text-2xl font-black">{{ $program->name }}</h2><p class="mt-2 text-sm text-slate-500">Partner code: <span class="font-mono font-bold">{{ $partner->partner_code }}</span></p></div>
                        <a href="{{ $storefrontUrl }}" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-black text-white">Open public storefront →</a>
                    </div>
                </div>
                <div class="grid gap-6 p-6 sm:p-7 lg:grid-cols-[.8fr_1.2fr]">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Sales</p><p class="mt-1 text-2xl font-black">₦{{ number_format($programStat['paid_sales_amount'],2) }}</p></div>
                        <div class="rounded-2xl bg-emerald-50 p-4"><p class="text-xs text-emerald-700">Commission</p><p class="mt-1 text-2xl font-black text-emerald-700">₦{{ number_format($programStat['total_commissions'],2) }}</p></div>
                        <div class="rounded-2xl bg-violet-50 p-4"><p class="text-xs text-violet-700">Recruited</p><p class="mt-1 text-2xl font-black text-violet-950">{{ $programStat['recruited_partners_count'] }}</p></div>
                        <div class="rounded-2xl bg-blue-50 p-4"><p class="text-xs text-blue-700">Your rate</p><p class="mt-1 text-2xl font-black text-blue-950">{{ $level1Rule ? ($level1Rule->commission_type === 'percentage' ? number_format((float)$level1Rule->value,2).'%' : '₦'.number_format((float)$level1Rule->value,2)) : '—' }}</p></div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between"><h3 class="text-lg font-black">Products you can promote</h3><a href="{{ $storefrontUrl }}#products" class="text-sm font-bold text-violet-600">See all on storefront →</a></div>
                        @if($program->products->isNotEmpty())
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach($program->products->take(6) as $product)
                                    <a href="{{ route('product.show', ['slug' => $product->slug, 'ref' => $partner->partner_code]) }}" class="group rounded-2xl border border-slate-200 p-4 hover:border-violet-300 hover:shadow-sm"><div class="flex items-center justify-between gap-3"><div><p class="font-bold group-hover:text-violet-700">{{ $product->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $product->currency ?? 'NGN' }} {{ number_format((float)$product->price,2) }}</p></div><span class="text-violet-600">→</span></div></a>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-4 rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">No active products are currently attached to this program.</p>
                        @endif
                    </div>
                </div>
            </section>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center"><h2 class="text-2xl font-black">You haven't joined a program yet.</h2><p class="mt-2 text-slate-600">Browse available programs and choose the ones you want to promote.</p><a href="{{ route('partner.marketplace.index') }}" class="mt-6 inline-flex rounded-xl bg-violet-600 px-5 py-3 font-bold text-white">Browse programs →</a></div>
        @endforelse
    </section>
</div>
</body>
</html>