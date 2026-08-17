<style>
    .network-tree.vertical .network-node-children { display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem; }
    .network-tree.horizontal .network-node-children { display: flex; flex-direction: row; align-items: flex-start; gap: 2rem; margin-top: 0; margin-left: 3rem; padding-left: 2rem; border-left: 2px dashed rgb(237 233 254); }
    .network-tree.horizontal .network-node { display: flex; align-items: flex-start; gap: 2rem; }
    .network-tree.horizontal .network-node > .network-node-content { flex: 0 0 360px; }
    .network-tree.horizontal .network-node-children > .network-node { position: relative; }
    .network-tree.horizontal .network-node-children > .network-node::before { content: ''; position: absolute; left: -2rem; top: 2rem; width: 2rem; height: 2px; background: rgb(221 214 254); }
    .network-tree.horizontal .network-node-children .network-node-children { margin-left: 0; }
    .network-tree.horizontal .network-node-children .network-node-children > .network-node { display: block; }
    .network-tree.horizontal .network-node-children .network-node-children > .network-node::before { display: none; }
    @media (max-width: 900px) {
        .network-tree.horizontal .network-node-children { flex-direction: column; }
        .network-tree.horizontal .network-node { display: block; }
    }
</style>

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
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"><p class="text-sm font-medium text-gray-500">Partners in view</p><p class="mt-2 text-3xl font-black text-gray-900">{{ number_format($totalPartners) }}</p></div>
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"><p class="text-sm font-medium text-gray-500">Recruiters</p><p class="mt-2 text-3xl font-black text-gray-900">{{ number_format($totalRecruiters) }}</p></div>
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"><p class="text-sm font-medium text-gray-500">Programs represented</p><p class="mt-2 text-3xl font-black text-gray-900">{{ number_format($trees->count()) }}</p></div>
            </section>

            @if(auth()->user()->hasRole('program_manager') && !$selectedPartner)
                <section class="rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50 to-violet-50 p-5 sm:p-6">
                    <p class="text-sm font-black text-indigo-900">Explore any partner's team</p>
                    <p class="mt-1 max-w-3xl text-sm leading-6 text-indigo-800">Your business can see every affiliate in its programs. Click <strong>View team</strong> on any partner below to make that partner the root of the tree and see everyone they have recruited.</p>
                </section>
            @elseif($selectedPartner)
                <section class="flex flex-col gap-4 rounded-2xl border border-violet-100 bg-violet-50 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Viewing partner team</p><h3 class="mt-1 text-lg font-black text-gray-900">{{ $selectedPartner->user?->name ?? 'Unknown partner' }}</h3><p class="mt-1 text-sm text-gray-600">This partner is now the root of the recruitment tree.</p></div>
                    <a href="{{ route('network.index') }}" class="inline-flex w-fit rounded-xl bg-white px-4 py-2.5 text-sm font-black text-gray-700 shadow-sm ring-1 ring-inset ring-gray-200 hover:bg-gray-50">← Show all partners</a>
                </section>
            @endif

            <section class="rounded-2xl border border-violet-100 bg-gradient-to-r from-violet-50 to-indigo-50 p-5 sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div><p class="text-sm font-black text-violet-900">Network view</p><p class="mt-1 max-w-3xl text-sm leading-6 text-violet-800">Switch between a vertical hierarchy and a horizontal organization-style tree. Your preference is remembered on this device.</p></div>
                    <div class="inline-flex w-fit rounded-xl bg-white p-1 shadow-sm ring-1 ring-inset ring-violet-100" role="group" aria-label="Network tree layout">
                        <button type="button" @click="layout = 'vertical'" :class="layout === 'vertical' ? 'bg-violet-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'" class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-xs font-black transition">Vertical</button>
                        <button type="button" @click="layout = 'horizontal'" :class="layout === 'horizontal' ? 'bg-violet-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'" class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-xs font-black transition">Horizontal</button>
                    </div>
                </div>
            </section>

            @forelse($trees as $tree)
                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-gray-100 bg-gray-50 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Affiliate program</p><h3 class="mt-1 text-xl font-black text-gray-900">{{ $tree['program']->name }}</h3><p class="mt-1 text-sm text-gray-500">{{ $tree['program']->owner?->business_name ?: ($tree['program']->owner?->name ?? 'Platform') }} · {{ $tree['partners_count'] }} partner{{ $tree['partners_count'] === 1 ? '' : 's' }}</p></div>
                        @if($tree['program']->status)<span class="w-fit rounded-full bg-white px-3 py-1.5 text-xs font-black uppercase tracking-wide text-gray-600 ring-1 ring-inset ring-gray-200">{{ $tree['program']->status }}</span>@endif
                    </div>
                    <div class="overflow-auto p-5 sm:p-8">
                        <div class="network-tree" :class="layout" style="min-width: 760px;">
                            @foreach($tree['roots'] as $root)
                                @include('network.node', ['node' => $root, 'children' => $tree['children'], 'depth' => 0])
                            @endforeach
                        </div>
                    </div>
                </section>
            @empty
                <section class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center shadow-sm">
                    <h3 class="text-xl font-black text-gray-900">No recruitment network yet</h3>
                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-gray-500">Partners will appear here as they join programs and recruit other partners.</p>
                </section>
            @endforelse
        </div>
    </div>
</x-app-layout>