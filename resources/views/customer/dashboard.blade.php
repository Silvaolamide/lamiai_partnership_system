<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - LAMI AI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
            <div>
                <p class="text-sm font-bold tracking-wide text-blue-600">LAMI AI</p>
                <h1 class="text-xl font-extrabold">Customer Dashboard</h1>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <span class="hidden text-slate-500 sm:inline">{{ auth()->user()->name }}</span>
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

        <div class="mb-10">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Welcome back</p>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight">Hello, {{ auth()->user()->name }} 👋</h2>
            <p class="mt-2 text-slate-600">Your purchased products and order history are all in one place.</p>
        </div>

        <section>
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-bold">My Products</h3>
                    <p class="mt-1 text-sm text-slate-500">Products you have successfully purchased.</p>
                </div>
            </div>

            @if($products->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                    <h4 class="text-lg font-bold">No purchases yet</h4>
                    <p class="mt-2 text-sm text-slate-500">Your products will appear here after a successful purchase.</p>
                    <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Browse products</a>
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($products as $product)
                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-44 w-full object-cover">
                            @else
                                <div class="flex h-44 items-center justify-center bg-slate-100 text-sm font-semibold text-slate-400">LAMI AI</div>
                            @endif
                            <div class="p-6">
                                <h4 class="text-lg font-bold">{{ $product->name }}</h4>
                                @if($product->description)
                                    <p class="mt-2 line-clamp-3 text-sm text-slate-500">{{ $product->description }}</p>
                                @endif
                                <div class="mt-5 flex items-center justify-between gap-4">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Purchased</span>
                                    <a href="{{ route('product.show', ['slug' => $product->slug]) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">View product</a>
                                </div>
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
