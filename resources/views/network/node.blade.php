@php
    $nodeChildren = $children->get((string) $node->id, collect());
    $statusClasses = match ($node->status) {
        'active', 'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'rejected', 'inactive', 'suspended' => 'bg-red-50 text-red-700 ring-red-200',
        default => 'bg-gray-50 text-gray-600 ring-gray-200',
    };
    $canDrillDown = auth()->user()->hasAnyRole(['super_admin', 'program_manager']);
@endphp

<div class="relative {{ $depth > 0 ? 'ml-10 border-l-2 border-dashed border-violet-100 pl-8' : '' }}">
    @if($depth > 0)
        <span class="absolute -left-8 top-7 h-px w-8 bg-violet-200"></span>
    @endif

    <div class="group relative flex max-w-2xl items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-md">
        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 text-sm font-black text-white shadow-sm">
            {{ strtoupper(substr($node->user?->name ?? 'P', 0, 1)) }}
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <p class="truncate font-black text-gray-900">{{ $node->user?->name ?? 'Unknown partner' }}</p>
                <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase tracking-wide ring-1 ring-inset {{ $statusClasses }}">{{ $node->status }}</span>
                @if($depth === 0)
                    <span class="rounded-full bg-violet-600 px-2 py-1 text-[10px] font-black uppercase tracking-wide text-white">{{ auth()->user()->hasRole('partner') ? 'You' : 'Root' }}</span>
                @endif
            </div>
            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                <span>Code: <b class="font-mono text-gray-700">{{ $node->partner_code }}</b></span>
                @if($node->user?->email)<span>{{ $node->user->email }}</span>@endif
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-3">
            <div class="hidden text-right sm:block">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Level</p>
                <p class="mt-1 text-lg font-black text-gray-900">{{ $depth + 1 }}</p>
            </div>

            @if($canDrillDown)
                <a href="{{ route('network.index', ['partner' => $node->id]) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-violet-50 px-3 py-2 text-xs font-black text-violet-700 ring-1 ring-inset ring-violet-100 transition hover:bg-violet-100" title="View this partner's recruitment team">
                    <span>View team</span>
                    <span aria-hidden="true">→</span>
                </a>
            @endif
        </div>
    </div>

    @if($nodeChildren->isNotEmpty())
        <div class="mt-4 space-y-4">
            @foreach($nodeChildren as $child)
                @include('network.node', ['node' => $child, 'children' => $children, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
