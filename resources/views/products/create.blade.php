<!DOCTYPE html>
<html>
<head>
    <title>Create Product</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

<div class="max-w-3xl mx-auto px-6 py-10">

    <h1 class="text-3xl font-bold mb-8">
        Create Product
    </h1>

    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.products.store') }}"
        method="POST"
        class="space-y-6"
    >

        @csrf

        <div>
            <label>Product Name</label>

            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="w-full border rounded-lg p-3"
                required
            >
        </div>

        <div>
            <label>Slug</label>

            <input
                type="text"
                name="slug"
                value="{{ old('slug') }}"
                class="w-full border rounded-lg p-3"
            >
        </div>

        <div>
            <label>Description</label>

            <textarea
                name="description"
                class="w-full border rounded-lg p-3"
                rows="4"
            >{{ old('description') }}</textarea>
        </div>

        <div>
            <label>SKU</label>

            <input
                type="text"
                name="sku"
                value="{{ old('sku') }}"
                class="w-full border rounded-lg p-3"
            >
        </div>

        <div>
            <label>Price</label>

            <input
                type="number"
                name="price"
                value="{{ old('price') }}"
                step="0.01"
                min="0"
                class="w-full border rounded-lg p-3"
                required
            >
        </div>

        <div>
            <label>Currency</label>

            <input
                type="text"
                name="currency"
                value="{{ old('currency', 'NGN') }}"
                class="w-full border rounded-lg p-3"
                required
            >
        </div>

        <div>
            <label>Status</label>

            <select
                name="status"
                class="w-full border rounded-lg p-3"
            >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <button
            type="submit"
            class="bg-black text-white px-6 py-3 rounded-lg"
        >
            Create Product
        </button>

    </form>

</div>

</body>
</html>