@extends('layouts.admin')

@section('content')
<div class="min-h-[calc(100vh-78px)] bg-[#f6f7fb] py-6 sm:py-8">
    <div class="mx-auto max-w-[1600px] space-y-6 px-4 sm:px-6 lg:px-8">
        <section class="relative overflow-hidden rounded-[28px] bg-slate-950 px-6 py-7 text-white shadow-2xl shadow-slate-900/10 sm:px-8">
            <div class="absolute -right-24 -top-28 h-72 w-72 rounded-full bg-fuchsia-600/20 blur-3xl"></div>
            <div class="relative flex flex-col justify-between gap-5 md:flex-row md:items-end">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-violet-300"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>Catalog management</div>
                    <h1 class="text-2xl font-black tracking-tight sm:text-3xl">Products</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Manage the products available across your partnership ecosystem.</p>
                </div>
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-black text-slate-950 shadow-lg transition hover:-translate-y-0.5 hover:bg-violet-50"><span class="text-lg leading-none">+</span> Create Product</a>
            </div>
        </section>

        @if(session('success'))
            <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800 shadow-sm"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-emerald-100">✓</span><span>{{ session('success') }}</span></div>
        @endif

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($products as $product)
                <article class="group overflow-hidden rounded-[22px] border border-slate-200/80 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:border-violet-200 hover:shadow-xl hover:shadow-slate-900/5">
                    <div class="h-1.5 bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-500"></div>
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="grid h-12 w-12 place-items-center rounded-2xl bg-indigo-50 text-indigo-600"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4.5 7.5 7.5 4 7.5-4M12 12v9"/></svg></div>
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $product->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $product->status }}</span>
                        </div>
                        <h2 class="mt-5 text-lg font-black tracking-tight text-slate-950">{{ $product->name }}</h2>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">₦{{ number_format($product->price, 2) }}</p>
                        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-5">
                            <div><p class="text-[9px] font-black uppercase tracking-wider text-slate-400">SKU</p><p class="mt-1 text-xs font-bold text-slate-700">{{ $product->sku ?: 'Not assigned' }}</p></div>
                            <a href="{{ route('admin.products.edit', $product) }}" class="rounded-xl bg-slate-50 px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-violet-50 hover:text-violet-700">Edit →</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="md:col-span-2 xl:col-span-3 rounded-[22px] border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm"><h2 class="text-lg font-black text-slate-900">No products</h2><p class="mt-1 text-sm text-slate-500">Create your first product to make it available to partnership programs.</p><a href="{{ route('admin.products.create') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white">Create Product</a></div>
            @endforelse
        </div>
    </div>
</div>
@endsection
