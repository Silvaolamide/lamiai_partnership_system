@extends('layouts.admin')

@section('content')
<div class="min-h-[calc(100vh-78px)] bg-[#f6f7fb] py-6 sm:py-8">
    <div class="mx-auto max-w-[1600px] space-y-6 px-4 sm:px-6 lg:px-8">
        <section class="relative overflow-hidden rounded-[28px] bg-slate-950 px-6 py-7 text-white shadow-2xl shadow-slate-900/10 sm:px-8">
            <div class="absolute -right-24 -top-28 h-72 w-72 rounded-full bg-violet-600/25 blur-3xl"></div>
            <div class="relative flex flex-col justify-between gap-5 md:flex-row md:items-end">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-violet-300"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>Partnership management</div>
                    <h1 class="text-2xl font-black tracking-tight sm:text-3xl">Partnership Programs</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Create and configure the programs that power your partner ecosystem.</p>
                </div>
                <a href="{{ route('admin.programs.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-black text-slate-950 shadow-lg transition hover:-translate-y-0.5 hover:bg-violet-50"><span class="text-lg leading-none">+</span> Create Program</a>
            </div>
        </section>

        @if(session('success'))
            <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800 shadow-sm"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-emerald-100">✓</span><span>{{ session('success') }}</span></div>
        @endif

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($programs as $program)
                <article class="group overflow-hidden rounded-[22px] border border-slate-200/80 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:border-violet-200 hover:shadow-xl hover:shadow-slate-900/5">
                    <div class="h-1.5 bg-gradient-to-r from-violet-600 via-fuchsia-500 to-indigo-500"></div>
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="grid h-12 w-12 place-items-center rounded-2xl bg-violet-50 text-violet-600"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg></div>
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $program->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $program->status }}</span>
                        </div>
                        <h2 class="mt-5 text-lg font-black tracking-tight text-slate-950">{{ $program->name }}</h2>
                        <p class="mt-2 min-h-[48px] text-sm leading-6 text-slate-500">{{ $program->description ?: 'No description has been added to this program yet.' }}</p>
                        <div class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-5">
                            <div><p class="text-[9px] font-black uppercase tracking-wider text-slate-400">Attribution</p><p class="mt-1 text-sm font-black text-slate-800">{{ $program->attribution_window_days }} days</p></div>
                            <div><p class="text-[9px] font-black uppercase tracking-wider text-slate-400">Min. payout</p><p class="mt-1 text-sm font-black text-slate-800">₦{{ number_format($program->minimum_payout, 2) }}</p></div>
                        </div>
                        <a href="{{ route('admin.programs.edit', $program) }}" class="mt-5 flex w-full items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 transition group-hover:bg-violet-50 group-hover:text-violet-700">Configure program <span>→</span></a>
                    </div>
                </article>
            @empty
                <div class="md:col-span-2 xl:col-span-3 rounded-[22px] border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm"><h2 class="text-lg font-black text-slate-900">No partnership programs</h2><p class="mt-1 text-sm text-slate-500">Create your first program to start building your partner ecosystem.</p><a href="{{ route('admin.programs.create') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white">Create Program</a></div>
            @endforelse
        </div>
    </div>
</div>
@endsection
