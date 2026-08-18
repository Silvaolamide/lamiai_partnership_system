<x-app-layout>
<x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.22em] text-violet-600">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Platform command center
            </div>
            <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Super Admin Dashboard</h2>
            <p class="mt-1 text-sm text-slate-500">A clear view of growth, money and operational activity.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('network.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-200 hover:text-violet-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Network
            </a>
            <a href="{{ route('admin.analytics.businesses') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-slate-900/15 transition hover:-translate-y-0.5 hover:bg-violet-700">
                All Businesses
                <span>→</span>
            </a>
        </div>
    </div>
</x-slot>

<div class="min-h-screen bg-[#f6f7fb] py-6 sm:py-8">
    <div class="mx-auto max-w-[1600px] space-y-6 px-4 sm:px-6 lg:px-8">

        {{-- Hero --}}
        <section class="relative overflow-hidden rounded-[28px] bg-slate-950 px-6 py-7 text-white shadow-2xl shadow-slate-900/10 sm:px-8 sm:py-8">
            <div class="absolute -right-24 -top-32 h-80 w-80 rounded-full bg-violet-600/30 blur-3xl"></div>
            <div class="absolute -bottom-32 right-1/3 h-72 w-72 rounded-full bg-fuchsia-500/15 blur-3xl"></div>
            <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-300">Executive overview</p>
                    <h1 class="mt-2 max-w-2xl text-3xl font-black tracking-tight sm:text-4xl">See the whole platform at a glance.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">Monitor growth, partner activity, cash movement and the areas that need your attention — without digging through screens.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4 backdrop-blur">
                    <div class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Today</div>
                    <div class="mt-1 text-lg font-black text-white">{{ now()->format('D, d M Y') }}</div>
                    <div class="mt-1 text-xs text-emerald-300">● Platform operational</div>
                </div>
            </div>
        </section>

        {{-- Filters --}}
        <form method="GET" class="rounded-[22px] border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="mb-3 flex items-center gap-2">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-violet-50 text-violet-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h18M6 12h12M10 20h4"/></svg>
                </span>
                <div><p class="text-sm font-black text-slate-900">Refine your view</p><p class="text-xs text-slate-400">Change the reporting scope without leaving the dashboard.</p></div>
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-5">
                <div><label class="text-[10px] font-black uppercase tracking-wider text-slate-400">From</label><input type="date" name="from" value="{{ request('from') }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:border-violet-500 focus:ring-violet-500"></div>
                <div><label class="text-[10px] font-black uppercase tracking-wider text-slate-400">To</label><input type="date" name="to" value="{{ request('to') }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:border-violet-500 focus:ring-violet-500"></div>
                <div><label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Business</label><select name="business_id" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:border-violet-500 focus:ring-violet-500"><option value="">All businesses</option>@foreach($businessOptions as $option)<option value="{{ $option->id }}" @selected($businessId===$option->id)>{{ $option->business_name ?: $option->name }}</option>@endforeach</select></div>
                <div><label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Program</label><select name="program_id" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:border-violet-500 focus:ring-violet-500"><option value="">All programs</option>@foreach($programOptions as $option)<option value="{{ $option->id }}" @selected($programId===$option->id)>{{ $option->name }}</option>@endforeach</select></div>
                <div class="flex items-end gap-2"><button class="flex-1 rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white transition hover:bg-violet-700">Apply filters</button><a href="{{ route('admin') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Reset</a></div>
            </div>
        </form>

        {{-- KPI cards --}}
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-8">
            @foreach([
                ['Businesses',$stats['businesses'],'violet'],['Partners',$stats['partners'],'blue'],['Customers',$stats['customers'],'cyan'],['Programs',$stats['programs'],'indigo'],['Products',$stats['products'],'pink'],['Orders',$stats['paid_orders'],'amber'],['Gross Sales','₦'.number_format($stats['sales'],0),'emerald'],['Operational Net','₦'.number_format($stats['net_platform_revenue'],0),'fuchsia']
            ] as $card)
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                    <div class="absolute right-0 top-0 h-16 w-16 rounded-bl-full bg-{{ $card[2] }}-50"></div>
                    <div class="relative"><div class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $card[0] }}</div><div class="mt-2 truncate text-xl font-black tracking-tight text-slate-950">{{ $card[1] }}</div><div class="mt-3 h-1 w-8 rounded-full bg-{{ $card[2] }}-500 transition-all group-hover:w-14"></div></div>
                </div>
            @endforeach
        </div>

        {{-- Attention strip --}}
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
            @foreach($pendingActions as $action)
                <a href="{{ route($action['route']) }}" class="group flex items-center justify-between rounded-2xl border border-amber-100 bg-gradient-to-r from-amber-50 to-white p-4 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div><div class="text-sm font-black text-slate-900">{{ $action['label'] }}</div><div class="mt-1 text-xs text-slate-500">Needs your attention <span class="text-amber-600">→</span></div></div>
                    <span class="grid h-10 min-w-10 place-items-center rounded-xl bg-white px-3 text-sm font-black text-amber-700 shadow-sm ring-1 ring-amber-100">{{ $action['count'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Chart + money --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <section class="rounded-[24px] border border-slate-200/80 bg-white p-5 shadow-sm xl:col-span-2">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.18em] text-violet-600"><span class="h-2 w-2 rounded-full bg-violet-500"></span> Performance trend</div><h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Sales & commissions</h3><p class="mt-1 text-xs text-slate-400">Daily movement across the selected period.</p></div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-500">{{ count($series) }} days</span>
                </div>
                <div class="mt-4 rounded-2xl bg-slate-50 p-2"><canvas id="performanceChart" height="105" class="w-full"></canvas></div>
            </section>
            <section class="overflow-hidden rounded-[24px] bg-slate-950 text-white shadow-xl shadow-slate-900/10">
                <div class="border-b border-white/10 p-5"><div class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-300">Money control</div><h3 class="mt-1 text-xl font-black">Where the money is</h3></div>
                <div class="divide-y divide-white/10">
                    <div class="flex justify-between p-5"><span class="text-sm text-slate-400">Gross sales</span><b>₦{{ number_format($stats['sales'],2) }}</b></div>
                    <div class="flex justify-between p-5"><span class="text-sm text-slate-400">Commissions</span><b>₦{{ number_format($stats['commissions'],2) }}</b></div>
                    <div class="flex justify-between p-5"><span class="text-sm text-slate-400">Pending payouts</span><b>₦{{ number_format($stats['pending_payouts'],2) }}</b></div>
                    <div class="flex items-end justify-between bg-emerald-500/10 p-5"><div><div class="text-xs font-bold uppercase tracking-wider text-emerald-300">Operational net</div><div class="mt-1 text-2xl font-black">₦{{ number_format($stats['net_platform_revenue'],2) }}</div></div><span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-400/15 text-emerald-300">↗</span></div>
                </div>
            </section>
        </div>

        {{-- Businesses --}}
        <section class="overflow-hidden rounded-[24px] border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:p-6"><div><div class="text-[10px] font-black uppercase tracking-[0.18em] text-violet-600">Business command view</div><h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Businesses at a glance</h3></div><a href="{{ route('admin.analytics.businesses') }}" class="rounded-xl bg-violet-50 px-4 py-2 text-sm font-black text-violet-700 transition hover:bg-violet-100">Open all →</a></div>
            <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50/80 text-[10px] font-black uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3 text-left">Business</th><th class="px-5 py-3">Programs</th><th class="px-5 py-3">Partners</th><th class="px-5 py-3">Orders</th><th class="px-5 py-3">Sales</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($businesses as $business)<tr class="group transition hover:bg-violet-50/30"><td class="px-5 py-4"><div class="flex items-center gap-3"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-xs font-black text-white">{{ strtoupper(substr($business->business_name ?: $business->name,0,1)) }}</span><div><b class="text-slate-900">{{ $business->business_name ?: $business->name }}</b><div class="text-xs text-slate-400">{{ $business->email }}</div></div></div></td><td class="px-5 py-4 text-center font-bold text-slate-600">{{ $business->dashboard_metrics['programs'] }}</td><td class="px-5 py-4 text-center font-bold text-slate-600">{{ $business->dashboard_metrics['partners'] }}</td><td class="px-5 py-4 text-center font-bold text-slate-600">{{ $business->dashboard_metrics['orders'] }}</td><td class="px-5 py-4 font-black text-slate-900">₦{{ number_format($business->dashboard_metrics['sales'],0) }}</td><td class="px-5 py-4 text-right"><a class="font-black text-violet-600 opacity-70 transition group-hover:opacity-100" href="{{ route('admin.analytics.business',$business) }}">View →</a></td></tr>@empty<tr><td colspan="6" class="p-10 text-center text-slate-400">No businesses.</td></tr>@endforelse</tbody></table></div>
        </section>

        {{-- Activity + partners --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-5">
            <section class="overflow-hidden rounded-[24px] border border-slate-200/80 bg-white shadow-sm xl:col-span-3">
                <div class="flex items-center justify-between border-b border-slate-100 p-5"><div><div class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-600">Live sales stream</div><h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Recent customer activity</h3></div><span id="liveStatus" class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-600">Connecting…</span></div>
                <div id="salesFeed" class="divide-y divide-slate-100">@forelse($recentOrders as $order)<a href="{{ route('admin.orders.show',$order) }}" class="group flex justify-between gap-4 p-4 transition hover:bg-slate-50"><div class="flex min-w-0 items-center gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">↗</span><div class="min-w-0"><b class="block truncate text-sm text-slate-900">{{ $order->customer?->name ?? $order->customer_name ?? 'Guest customer' }}</b><div class="truncate text-xs text-slate-400">{{ $order->order_number }} · {{ $order->program?->name ?? '—' }} · {{ $order->partner?->user?->name ?? 'Direct' }}</div></div></div><div class="shrink-0 text-right"><b class="text-sm text-slate-900">{{ $order->currency }} {{ number_format($order->total,2) }}</b><div class="text-[10px] text-slate-400">{{ $order->created_at->diffForHumans() }}</div></div></a>@empty<div class="p-10 text-center text-slate-400">No activity.</div>@endforelse</div>
            </section>
            <section class="overflow-hidden rounded-[24px] border border-slate-200/80 bg-white shadow-sm xl:col-span-2">
                <div class="border-b border-slate-100 p-5"><div class="text-[10px] font-black uppercase tracking-[0.18em] text-fuchsia-600">Partner performance</div><h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Top earners</h3></div>
                <div class="divide-y divide-slate-100">@forelse($topPartners as $partner)<a href="{{ route('admin.analytics.show','partners') }}" class="group flex items-center justify-between p-4 transition hover:bg-slate-50"><div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-full bg-gradient-to-br from-fuchsia-500 to-violet-600 text-xs font-black text-white">{{ strtoupper(substr($partner->user?->name ?? 'U',0,1)) }}</span><span><b class="block text-sm text-slate-900">{{ $partner->user?->name ?? 'Unknown' }}</b><small class="block text-xs text-slate-400">{{ $partner->program?->name ?? '—' }}</small></span></div><strong class="text-sm text-slate-900">₦{{ number_format($partner->earnings ?? 0,2) }}</strong></a>@empty<div class="p-10 text-center text-slate-400">No partners.</div>@endforelse</div>
            </section>
        </div>

        {{-- Programs --}}
        <section class="overflow-hidden rounded-[24px] border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:p-6"><div><div class="text-[10px] font-black uppercase tracking-[0.18em] text-indigo-600">Program performance</div><h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Which programs are moving?</h3></div><a href="{{ route('admin.programs.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-violet-200 hover:text-violet-700">Manage →</a></div>
            <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50/80 text-[10px] font-black uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3 text-left">Program</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Partners</th><th class="px-5 py-3">Products</th><th class="px-5 py-3">Orders</th><th class="px-5 py-3 text-right">Sales</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($programs as $program)<tr class="transition hover:bg-indigo-50/30"><td class="px-5 py-4 font-black text-slate-900">{{ $program->name }}</td><td class="px-5 py-4 text-center"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase text-slate-600">{{ ucfirst($program->status) }}</span></td><td class="px-5 py-4 text-center font-bold text-slate-600">{{ $program->partners_count }}</td><td class="px-5 py-4 text-center font-bold text-slate-600">{{ $program->products_count }}</td><td class="px-5 py-4 text-center font-bold text-slate-600">{{ $program->orders_count }}</td><td class="px-5 py-4 text-right font-black text-slate-900">₦{{ number_format($program->sales ?? 0,2) }}</td></tr>@empty<tr><td colspan="6" class="p-10 text-center text-slate-400">No programs.</td></tr>@endforelse</tbody></table></div>
        </section>
    </div>
</div>

<script>
const series=@json($series),c=document.getElementById('performanceChart'),x=c.getContext('2d');
function draw(){const d=devicePixelRatio||1,w=c.clientWidth||900,h=240;c.width=w*d;c.height=h*d;x.setTransform(d,0,0,d,0,0);const m=Math.max(...series.map(v=>v.sales),1),l=42,r=16,t=16,b=30;x.clearRect(0,0,w,h);x.strokeStyle='#e2e8f0';x.lineWidth=1;for(let i=0;i<4;i++){let y=t+(h-t-b)*i/3;x.beginPath();x.moveTo(l,y);x.lineTo(w-r,y);x.stroke()}if(!series.length)return;x.strokeStyle='#7c3aed';x.lineWidth=3;x.lineCap='round';x.lineJoin='round';x.beginPath();series.forEach((p,i)=>{let px=l+(w-l-r)*i/Math.max(series.length-1,1),py=t+(h-t-b)*(1-p.sales/m);i?x.lineTo(px,py):x.moveTo(px,py)});x.stroke();x.fillStyle='#7c3aed';series.forEach((p,i)=>{if(i===series.length-1){let px=l+(w-l-r)*i/Math.max(series.length-1,1),py=t+(h-t-b)*(1-p.sales/m);x.beginPath();x.arc(px,py,5,0,Math.PI*2);x.fill()}})}draw();addEventListener('resize',draw);
const feed=document.getElementById('salesFeed'),status=document.getElementById('liveStatus');function safe(s){return String(s??'').replace(/[&<>'"]/g,z=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[z]))}function connect(){const s=new EventSource('{{ route('admin.realtime.sales') }}');s.addEventListener('sales',e=>{const rows=JSON.parse(e.data);if(!rows.length)return;feed.innerHTML=rows.map(o=>`<a href="/admin/orders/${o.id}" class="group flex justify-between gap-4 p-4 transition hover:bg-slate-50"><div class="flex min-w-0 items-center gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">↗</span><div class="min-w-0"><b class="block truncate text-sm text-slate-900">${safe(o.customer)}</b><div class="truncate text-xs text-slate-400">${safe(o.order_number)} · ${safe(o.program||'—')} · ${safe(o.partner||'Direct')}</div></div></div><div class="shrink-0 text-right"><b class="text-sm text-slate-900">${safe(o.currency||'NGN')} ${Number(o.total).toLocaleString(undefined,{minimumFractionDigits:2})}</b><div class="text-[10px] text-slate-400">${new Date(o.created_at).toLocaleString()}</div></div></a>`).join('');status.textContent='Live';status.className='rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-600'});s.onerror=()=>{status.textContent='Reconnecting…';status.className='rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-amber-600';s.close();setTimeout(connect,10000)}}connect();
</script>
</x-app-layout>