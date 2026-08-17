<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

<div class="max-w-6xl mx-auto px-6 py-10">

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Products</h1>

        <a
            href="{{ route('admin.products.create') }}"
            class="bg-black text-white px-5 py-3 rounded-lg"
        >
            Create Product
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @forelse($products as $product)

        <div class="border rounded-lg p-6 mb-4">

            <div class="flex justify-between">

                <div>
                    <h2 class="text-xl font-semibold">
                        {{ $product->name }}
                    </h2>

                    <p class="mt-2">
                        ₦{{ number_format($product->price, 2) }}
                    </p>

                    <p class="text-gray-500">
                        {{ $product->status }}
                    </p>
                </div>

                <a
                    href="{{ route('admin.products.edit', $product) }}"
                    class="text-blue-600"
                >
                    Edit
                </a>

            </div>

        </div>

    @empty

        <p>No products found.</p>

    @endforelse

</div>

</body>
</html>