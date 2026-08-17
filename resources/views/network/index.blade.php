<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-violet-600">Network intelligence</p>
                <h2 class="mt-1 text-2xl font-black text-gray-900">{{ $title }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex w-fit items-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50">← Back to dashboard</a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ layout: localStorage.getItem('networkTreeLayout') || 'vertical' }" x-init="$watch('layout', value => localStorage.setItem('networkTreeLayout', value))">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Partners in view</p>
                    <p class="mt-2 text-3xl font-black text-gray-900">{{ number_format($totalPartners) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Recruiters</p>
                    <p class="mt-2 text-3xl font-black text-gray-900">{{ number_format($totalRecruiters) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Programs represented</p>
                    <p class="mt-2 text-3xl font-black text-gray-900">{{ number_format($trees->count()) }}</p>
                </div>
            </section>

            @if(auth()->user()->hasRole('program_manager') && !$selectedPartner)
                <section class="rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50 to-violet-50 p-5 sm:p-6">
                    <div>
                        <p class="text-sm font-black text-indigo-900">Explore any partner's team</p>
                        <p class="mt-1 max-w-3xl text-sm leading-6 text-indigo-800">Your business can see every affiliate in its programs. Click <strong>View team</strong> on any partner below to make that partner the root of the tree and see everyone they have recruited.</p>
                    </div>
                </section>
            @elseif($selectedPartner)
                <section class="flex flex-col gap-4 rounded-2xl border border-violet-100 bg-violet-50 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div>
                        <p class="text-xs font-black uppercase tracking-widest text-violet-600">Viewing partner team</p>
                        <h3 class="mt-1 text-lg font-black text-gray-900">{{ $selectedPartner->user?->name ?? 'Unknown partner' }}</h3>
                        <p class="mt-1 text-sm text-gray-600">This partner is now the root of the recruitment tree.</p>
                    </div>
                    <a href="{{ route('network.index') }}" class="inline-flex w-fit rounded-xl bg-white px-4 py-2.5 text-sm font-black text-gray-700 shadow-sm ring-1 ring-inset ring-gray-200 hover:bg-gray-50">← Show all partners</a>
                </section>
            @endif

            <section class="rounded-2xl border border-violet-100 bg-gradient-to-r from-violet-50 to-indigo-50 p-5 sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-black text-violet-900">Network view</p>
                        <p class="mt-1 max-w-3xl text-sm leading-6 text-violet-800">Switch between a compact vertical hierarchy and a horizontal organization-style tree. Your choice is remembered on this device.</p>
                    </div>
                    <div class="inline-flex w-fit rounded-xl bg-white p-1 shadow-sm ring-1 ring-inset ring-violet-100" role="group" aria-label="Network tree layout">
                        <button type="button" @click="layout = 'vertical'" :class="layout === 'vertical' ? 'bg-violet-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'" class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-xs font-black transition">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M7 9l5-4 5 4M7 19h10"/><circle cx="12" cy="4" r="2"/><circle cx="7" cy="20" r="2"/><circle cx="17" cy="20" r="2"/></svg>
                            Vertical
                        </button>
                        <button type="button" @click="layout = 'horizontal'" :class="layout === 'horizontal' ? 'bg-violet-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'" class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-xs font-black transition">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M9 7l-4 5 4 5M19 7v10"/><circle cx="4" cy="12" r="2"/><circle cx="20" cy="7" r="2"/><circle cx="20" cy="17" r="2"/></svg>
                            Horizontal
                        </button>
                    </div>
                </div>
            </section>

            @forelse($trees as $tree)
                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-gray-100 bg-gray-50 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-violet-600">Affiliate program</p>
                            <h3 class="mt-1 text-xl font-black text-gray-900">{{ $tree['program']->name }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $tree['program']->owner?->business_name ?: ($tree['program']->owner?->name ?? 'Platform') }} · {{ $tree['partners_count'] }} partner{{ $tree['partners_count'] === 1 ? '' : 's' }}</p>
                        </div>
                        @if($tree['program']->status)
                            <span class="w-fit rounded-full bg-white px-3 py-1.5 text-xs font-black uppercase tracking-wide text-gray-600 ring-1 ring-inset ring-gray-200">{{ $tree['program']->status }}</span>
                        @endif
                    </div>

                    <div class="overflow-x-auto p-5 sm:p-8">
                        <div class="min-w-[760px]" :class="layout === 'horizontal' ? 'min-h-[320px]' : ''">
                            @foreach($tree['roots'] as $root)
                                @include('network.node', ['node' => $root, 'children' => $tree['children'], 'depth' => 0])
                            @endforeach
                        </div>
                    </div>
                </section>
            @empty
                <section class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center shadow-sm">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-violet-50 text-violet-600">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="7" r="3"/><circle cx="5" cy="17" r="3"/><circle cx="19" cy="17" r="3"/><path d="M10 9.5 6.8 14.5M14 9.5l3.2 5M8 17h8"/></svg>
                    </div>
                    <h3 class="mt-5 text-xl font-black text-gray-900">No recruitment network yet</h3>
                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-gray-500">Partners will appear here as they join programs and recruit other partners. Once the first recruit joins, the network will automatically form a tree.</p>
                    @if(auth()->user()->hasRole('partner'))
                        <a href="{{ route('partner.dashboard') }}" class="mt-6 inline-flex rounded-xl bg-violet-600 px-5 py-3 text-sm font-black text-white hover:bg-violet-700">Back to partner dashboard</a>
                    @elseif(auth()->user()->hasRole('program_manager'))
                        <a href="{{ route('business.affiliates.index') }}" class="mt-6 inline-flex rounded-xl bg-violet-600 px-5 py-3 text-sm font-black text-white hover:bg-violet-700">View affiliates</a>
                    @endif
                </section>
            @endforelse
        </div>
    </div>
</x-app-layout>