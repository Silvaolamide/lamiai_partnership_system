@include('partner.referral-showcase')

@php
    $productPrograms = $subscribedPrograms
        ->filter(fn ($programPartner) => $programPartner->program)
        ->flatMap(function ($programPartner) {
            $program = $programPartner->program;

            return $program->products->map(function ($product) use ($program) {
                return [
                    'product_id' => $product->id,
                    'program_name' => $program->name,
                    'program_description' => $program->description,
                    'attribution_window_days' => $program->attribution_window_days,
                    'minimum_payout' => $program->minimum_payout,
                    'direct_rule' => $program->commissionRules->firstWhere('level', 1),
                    'recruiter_rule' => $program->commissionRules->firstWhere('level', 2),
                ];
            });
        })
        ->groupBy('product_id');
@endphp

<script>
window.addEventListener('DOMContentLoaded', function () {
    const products = @json($products->map(function ($product) use ($productPrograms) {
        $offers = $productPrograms->get($product->id, collect())->map(function ($offer) {
            $direct = $offer['direct_rule'];
            $recruiter = $offer['recruiter_rule'];

            $formatRule = function ($rule) {
                if (!$rule) return null;
                return $rule['commission_type'] === 'percentage'
                    ? rtrim(rtrim(number_format((float) $rule['value'], 2), '0'), '.') . '%'
                    : '₦' . number_format((float) $rule['value'], 2);
            };

            return [
                'program_name' => $offer['program_name'],
                'direct_commission' => $formatRule($direct),
                'recruiter_commission' => $formatRule($recruiter),
                'attribution_window_days' => $offer['attribution_window_days'],
                'minimum_payout' => number_format((float) $offer['minimum_payout'], 2),
            ];
        })->values();

        return [
            'name' => $product->name,
            'image' => $product->featured_image ? Storage::url($product->featured_image) : null,
            'offers' => $offers,
        ];
    })->values());

    const cards = document.querySelectorAll('#products .grid > article');

    cards.forEach(function (card, index) {
        const product = products[index];
        if (!product) return;

        const cover = card.firstElementChild;
        if (cover && product.image) {
            cover.className = 'relative h-56 w-full overflow-hidden bg-slate-100';
            cover.innerHTML = '';

            const image = document.createElement('img');
            image.src = product.image;
            image.alt = product.name;
            image.className = 'h-full w-full object-cover transition duration-500 group-hover:scale-105';
            image.loading = 'lazy';
            cover.appendChild(image);
        }

        if (!product.offers || !product.offers.length) return;

        const body = card.querySelector('.flex.flex-1.flex-col');
        if (!body) return;

        const details = document.createElement('div');
        details.className = 'mt-5 space-y-3';

        const heading = document.createElement('p');
        heading.className = 'text-xs font-black uppercase tracking-wider text-emerald-600';
        heading.textContent = 'Affiliate earning opportunity';
        details.appendChild(heading);

        product.offers.forEach(function (offer) {
            const panel = document.createElement('div');
            panel.className = 'rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4';

            const title = document.createElement('p');
            title.className = 'font-black text-slate-900';
            title.textContent = offer.program_name;
            panel.appendChild(title);

            const metrics = document.createElement('div');
            metrics.className = 'mt-3 grid grid-cols-2 gap-2 text-xs';

            if (offer.direct_commission) {
                const direct = document.createElement('div');
                direct.className = 'rounded-xl bg-white p-3';
                direct.innerHTML = '<span class="block font-bold uppercase tracking-wide text-slate-400">Direct sale</span><strong class="mt-1 block text-base text-emerald-700"></strong>';
                direct.querySelector('strong').textContent = offer.direct_commission;
                metrics.appendChild(direct);
            }

            if (offer.recruiter_commission) {
                const recruiter = document.createElement('div');
                recruiter.className = 'rounded-xl bg-white p-3';
                recruiter.innerHTML = '<span class="block font-bold uppercase tracking-wide text-slate-400">Recruiter bonus</span><strong class="mt-1 block text-base text-violet-700"></strong>';
                recruiter.querySelector('strong').textContent = offer.recruiter_commission;
                metrics.appendChild(recruiter);
            }

            panel.appendChild(metrics);

            const meta = document.createElement('p');
            meta.className = 'mt-3 text-[11px] font-semibold text-slate-500';
            meta.textContent = offer.attribution_window_days + ' day attribution · Minimum payout ₦' + offer.minimum_payout;
            panel.appendChild(meta);

            details.appendChild(panel);
        });

        const priceRow = body.querySelector('.mt-auto.flex.items-end');
        if (priceRow) {
            body.insertBefore(details, priceRow);
        } else {
            body.appendChild(details);
        }
    });
});
</script>
