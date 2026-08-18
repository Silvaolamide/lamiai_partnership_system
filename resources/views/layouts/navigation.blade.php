@php
    $isAdmin = Auth::check() && Auth::user()->hasRole('super_admin');
    $adminSections = [
        'Overview' => [['route' => 'admin', 'label' => 'Command Center', 'icon' => 'home']],
        'Manage' => [
            ['route' => 'admin.businesses.index', 'label' => 'Businesses', 'icon' => 'building'],
            ['route' => 'admin.partners.index', 'label' => 'Partners', 'icon' => 'users'],
            ['route' => 'admin.programs.index', 'label' => 'Programs', 'icon' => 'layers'],
            ['route' => 'admin.products.index', 'label' => 'Products', 'icon' => 'box'],
        ],
        'Money & Activity' => [
            ['route' => 'admin.orders.index', 'label' => 'Orders', 'icon' => 'shopping'],
            ['route' => 'admin.commissions.index', 'label' => 'Commissions', 'icon' => 'wallet'],
            ['route' => 'admin.payouts.index', 'label' => 'Partner Payouts', 'icon' => 'arrow'],
            ['route' => 'admin.business-payouts.index', 'label' => 'Business Payouts', 'icon' => 'bank'],
        ],
        'Insights' => [
            ['route' => 'admin.analytics.businesses', 'label' => 'Analytics', 'icon' => 'chart'],
            ['route' => 'network.index', 'label' => 'Network', 'icon' => 'network'],
        ],
    ];
@endphp

<nav class="relative z-50 {{ $isAdmin ? 'h-0' : '' }}">
    @if($isAdmin)
        <div class="bg-[#f6f7fb]">
            <aside :class="adminSidebarCollapsed ? 'w-[82px]' : 'w-[264px]'" class="fixed inset-y-0 left-0 hidden border-r border-slate-200/80 bg-white transition-all duration-300 lg:flex lg:flex-col">
                <div class="flex h-[78px] items-center border-b border-slate-100 px-5">
                    <a href="{{ route('admin') }}" class="flex min-w-0 items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-[13px] bg-slate-950 text-[10px] font-black tracking-tight text-white shadow-lg shadow-violet-900/10">AIPM</span>
                        <span x-show="!adminSidebarCollapsed" x-transition class="truncate text-sm font-black tracking-tight text-slate-950">AI Powered <span class="text-violet-600">Marketing</span></span>
                    </a>
                </div>
                <div class="flex-1 overflow-y-auto px-3 py-5">
                    @foreach($adminSections as $section => $items)
                        <div class="mb-6">
                            <div x-show="!adminSidebarCollapsed" class="mb-2 px-3 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">{{ $section }}</div>
                            <div class="space-y-1">
                                @foreach($items as $item)
                                    @php($active = request()->routeIs($item['route']) || ($item['route']==='admin.analytics.businesses' && request()->routeIs('admin.analytics.*')))
                                    <a href="{{ route($item['route']) }}" class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all {{ $active ? 'bg-violet-50 text-violet-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-950' }}" title="{{ $item['label'] }}">
                                        @if($active)<span class="absolute left-0 h-6 w-1 rounded-r-full bg-violet-600"></span>@endif
                                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg {{ $active ? 'bg-white text-violet-600 shadow-sm' : 'bg-slate-50 text-slate-400 group-hover:text-slate-700' }}">@include('layouts.icons', ['icon' => $item['icon']])</span>
                                        <span x-show="!adminSidebarCollapsed" x-transition class="truncate">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 p-3">
                    <button @click="adminSidebarCollapsed=!adminSidebarCollapsed" class="mb-2 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-400 transition hover:bg-slate-50 hover:text-slate-700">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-50"><svg class="h-4 w-4 transition-transform" :class="adminSidebarCollapsed ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg></span>
                        <span x-show="!adminSidebarCollapsed">Collapse sidebar</span>
                    </button>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 transition hover:bg-violet-50">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 text-xs font-black text-white">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span>
                        <span x-show="!adminSidebarCollapsed" class="min-w-0"><b class="block truncate text-xs text-slate-900">{{ Auth::user()->name }}</b><small class="block truncate text-[10px] text-slate-400">Super Administrator</small></span>
                    </a>
                </div>
            </aside>

            <div x-show="mobileNavOpen" x-transition.opacity class="fixed inset-0 z-[60] bg-slate-950/40 backdrop-blur-sm lg:hidden" @click="mobileNavOpen=false"></div>
            <aside x-show="mobileNavOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" class="fixed inset-y-0 left-0 z-[70] flex w-[290px] flex-col bg-white shadow-2xl lg:hidden">
                <div class="flex h-[78px] items-center justify-between border-b border-slate-100 px-5">
                    <a href="{{ route('admin') }}" class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-[13px] bg-slate-950 text-[10px] font-black text-white">AIPM</span><span class="text-sm font-black text-slate-950">AI Powered <span class="text-violet-600">Marketing</span></span></a>
                    <button @click="mobileNavOpen=false" class="grid h-9 w-9 place-items-center rounded-xl bg-slate-50 text-slate-500"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
                </div>
                <div class="flex-1 overflow-y-auto px-3 py-5">
                    @foreach($adminSections as $section => $items)
                        <div class="mb-6"><div class="mb-2 px-3 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">{{ $section }}</div><div class="space-y-1">
                            @foreach($items as $item)
                                <a @click="mobileNavOpen=false" href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold {{ request()->routeIs($item['route']) ? 'bg-violet-50 text-violet-700' : 'text-slate-500 hover:bg-slate-50' }}"><span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-50 text-slate-500">@include('layouts.icons', ['icon' => $item['icon']])</span>{{ $item['label'] }}</a>
                            @endforeach
                        </div></div>
                    @endforeach
                </div>
            </aside>

            <header :class="adminSidebarCollapsed ? 'lg:left-[82px]' : 'lg:left-[264px]'" class="fixed left-0 right-0 top-0 z-40 h-[78px] border-b border-slate-200/80 bg-white/90 backdrop-blur-xl transition-all duration-300">
                <div class="flex h-full items-center justify-between px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button @click="mobileNavOpen=true" class="grid h-10 w-10 place-items-center rounded-xl bg-slate-950 text-white lg:hidden"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
                        <div class="hidden sm:block"><p class="text-[9px] font-black uppercase tracking-[0.2em] text-violet-600">Admin workspace</p><p class="text-sm font-black text-slate-900">{{ request()->routeIs('admin') ? 'Command Center' : Str::headline(request()->route()->getName()) }}</p></div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <a href="{{ route('home') }}" target="_blank" class="hidden items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-500 transition hover:border-violet-200 hover:text-violet-700 sm:flex"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3h7v7M10 14 21 3M21 14v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h6"/></svg>View site</a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-xl p-1.5 pr-2 transition hover:bg-slate-50"><span class="grid h-9 w-9 place-items-center rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 text-xs font-black text-white">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span><span class="hidden text-left md:block"><b class="block text-xs font-black text-slate-900">{{ Auth::user()->name }}</b><small class="block text-[10px] text-slate-400">Super Admin</small></span></a>
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">@csrf<button class="rounded-xl p-2.5 text-slate-400 transition hover:bg-red-50 hover:text-red-500" title="Log out"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M21 4v16a2 2 0 0 1-2 2h-7"/></svg></button></form>
                    </div>
                </div>
            </header>
        </div>
    @else
        <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
            <div class="mx-auto flex h-[70px] max-w-[1600px] items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-[13px] bg-slate-950 text-[10px] font-black text-white">AIPM</span><span class="hidden text-sm font-black text-slate-950 sm:inline">AI Powered <span class="text-violet-600">Marketing</span></span></a>
                <div class="flex items-center gap-2"><a href="{{ route('profile.edit') }}" class="rounded-xl px-3 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50">{{ Auth::user()->name }}</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-xl px-3 py-2 text-sm font-bold text-slate-400 hover:bg-red-50 hover:text-red-500">Log out</button></form></div>
            </div>
        </header>
    @endif
</nav>
