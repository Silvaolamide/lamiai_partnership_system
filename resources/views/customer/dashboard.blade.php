<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Purchases - LAMI AI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
            <div>
                <p class="text-sm font-bold tracking-wide text-blue-600">LAMI AI</p>
                <h1 class="text-xl font-extrabold">My Purchases</h1>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <span class="hidden text-slate-500 sm:inline">{{ auth()->user()->name }}</span>
                @if(auth()->user()->hasRole('partner'))
                    <a href="{{ route('partner.dashboard') }}" class="font-semibold text-blue-600 hover:text-blue-700">Partner Dashboard</a>
                @elseif(auth()->user()->hasRole('super_admin'))
                    <a href="{{ route('admin') }}" class="font-semibold text-blue-600 hover:text-blue-700">Admin</a>
                @elseif(auth()->user()->hasRole('program_manager'))
                    <a href="{{ route('business.dashboard') }}" class="font-semibold text-blue-600 hover:text-blue-700">Business</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="font-semibold text-slate-700 hover:text-blue-600">Log out</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</div>
        @endif

        <div class="mb-10 rounded-3xl bg-slate-950 p-7 text-white shadow-xl sm:p-10">
            <p class="text-sm font-bold uppercase tracking-widest text-blue-300">Your learning library</p>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">Everything you've purchased, ready when you are.</h2>
            <p class="mt-3 max-w-2xl text-slate-300">Access your ebooks, videos, courses, downloads and other product resources from one secure place.</p>
        </div>

        <section>
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-bold">My Products</h3>
                    <p class="mt-1 text-sm text-slate-500">Paid products available to you.</p>
                </div>
                <a href="{{ route('home') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold hover:border-blue-300">Browse more</a>
            </div>

            @if($products->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                    <h4 class="text-lg font-bold">No purchases yet</h4>
                    <p class="mt-2 text-sm text-slate-500">Your products will appear here after a successful purchase.</p>
                    <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Browse products</a>
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($products as $entry)
                        @php($product = $entry['product'])
                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-44 w-full object-cover">
                            @else
                                <div class="flex h-44 items-center justify-center bg-slate-100 text-sm font-semibold text-slate-400">LAMI AI</div>
                            @endif
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-3">
                                    <h4 class="text-lg font-bold">{{ $product->name }}</h4>
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-black uppercase tracking-wide text-emerald-700">Purchased</span>
                                </div>
                                @if($product->description)
                                    <p class="mt-2 line-clamp-3 text-sm text-slate-500">{{ $product->description }}</p>
                                @endif

                                <div class="mt-5 rounded-xl bg-slate-50 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Your access</p>
                                    @if($entry['has_access'])
                                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $entry['delivery_label'] ?: 'Your product is ready.' }}</p>
                                        <a href="{{ route('customer.product-access', ['item' => $entry['item']->id]) }}" class="mt-3 inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white hover:bg-blue-700">{{ $entry['delivery_label'] ?: 'Access product' }} →</a>
                                    @else
                                        <p class="mt-1 text-sm text-amber-700">Access is being prepared by the product owner.</p>
                                    @endif
                                </div>

                                <a href="{{ route('product.show', ['slug' => $product->slug]) }}" class="mt-4 inline-flex text-sm font-bold text-slate-600 hover:text-blue-600">View product information</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mt-12">
            <h3 class="text-2xl font-bold">Recent Orders</h3>
            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Order</th>
                                <th class="px-6 py-4">Product</th>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Amount</th>
                                <th class="px-6 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($orders as $order)
                                <tr>
                                    <td class="px-6 py-4 font-mono font-semibold">{{ $order->order_number }}</td>
                                    <td class="px-6 py-4">{{ $order->items->first()?->product?->name ?? 'Product' }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ optional($order->paid_at)->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 font-semibold">{{ $order->currency }} {{ number_format($order->total, 2) }}</td>
                                    <td class="px-6 py-4"><span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Paid</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No paid orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
