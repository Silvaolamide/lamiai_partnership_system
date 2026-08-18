<div class="mb-5 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="flex flex-wrap items-center gap-1 p-2">
        <a href="{{ route('admin') }}" class="rounded-xl px-3 py-2 text-xs font-black {{ request()->routeIs('admin') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Overview</a>
        <a href="{{ route('admin.analytics.businesses') }}" class="rounded-xl px-3 py-2 text-xs font-black {{ request()->routeIs('admin.analytics.business*') ? 'bg-violet-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Businesses & intelligence</a>
        <a href="{{ route('admin.businesses.index') }}" class="rounded-xl px-3 py-2 text-xs font-black {{ request()->routeIs('admin.businesses.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">Businesses</a>
        <a href="{{ route('admin.partners.index') }}" class="rounded-xl px-3 py-2 text-xs font-black {{ request()->routeIs('admin.partners.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">Partners</a>
        <a href="{{ route('admin.orders.index') }}" class="rounded-xl px-3 py-2 text-xs font-black {{ request()->routeIs('admin.orders.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">Orders</a>
        <a href="{{ route('admin.commissions.index') }}" class="rounded-xl px-3 py-2 text-xs font-black {{ request()->routeIs('admin.commissions.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">Commissions</a>
        <a href="{{ route('admin.payouts.index') }}" class="rounded-xl px-3 py-2 text-xs font-black {{ request()->routeIs('admin.payouts.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">Partner payouts</a>
        <a href="{{ route('admin.business-payouts.index') }}" class="rounded-xl px-3 py-2 text-xs font-black {{ request()->routeIs('admin.business-payouts.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">Business payouts</a>
        <a href="{{ route('network.index') }}" class="rounded-xl px-3 py-2 text-xs font-black text-slate-600 hover:bg-slate-50">Network</a>
        <a href="{{ route('admin.settings') }}" class="ml-auto rounded-xl px-3 py-2 text-xs font-black text-slate-600 hover:bg-slate-50">Settings</a>
    </div>
</div>
