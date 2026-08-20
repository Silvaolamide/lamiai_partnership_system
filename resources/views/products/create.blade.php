<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50">
<div class="max-w-4xl mx-auto px-6 py-10">
    <h1 class="text-3xl font-bold mb-8">Create Product</h1>

    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded mb-6"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div class="bg-white border rounded-2xl p-6 space-y-5">
            <h2 class="text-xl font-black">Product details</h2>
            <div class="grid md:grid-cols-2 gap-5">
                <label class="md:col-span-2"><span class="font-bold">Product Name</span><input type="text" name="name" value="{{ old('name') }}" class="mt-2 w-full border rounded-lg p-3" required></label>
                <label class="md:col-span-2"><span class="font-bold">Description</span><textarea name="description" class="mt-2 w-full border rounded-lg p-3" rows="4">{{ old('description') }}</textarea></label>
                <label><span class="font-bold">SKU</span><input type="text" name="sku" value="{{ old('sku') }}" class="mt-2 w-full border rounded-lg p-3"></label>
                <label><span class="font-bold">Status</span><select name="status" class="mt-2 w-full border rounded-lg p-3"><option value="draft">Draft</option><option value="active">Active</option><option value="inactive">Inactive</option></select></label>
                <label><span class="font-bold">Price</span><input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" class="mt-2 w-full border rounded-lg p-3" required></label>
                <label><span class="font-bold">Currency</span><input type="text" name="currency" value="{{ old('currency', 'NGN') }}" class="mt-2 w-full border rounded-lg p-3" required></label>
                <label class="md:col-span-2"><span class="font-bold">Slug</span><input type="text" name="slug" value="{{ old('slug') }}" class="mt-2 w-full border rounded-lg p-3"><span class="text-xs text-slate-500">Leave blank to generate it from the product name.</span></label>
            </div>
        </div>

        <div class="bg-white border rounded-2xl p-6 space-y-5">
            <div><h2 class="text-xl font-black">Product media</h2><p class="mt-1 text-sm text-slate-500">Use a strong product image for the storefront, then add supporting images to the gallery.</p></div>
            <label class="block border-2 border-dashed border-violet-300 bg-violet-50 rounded-2xl p-6 cursor-pointer">
                <span class="block font-black text-violet-900">Featured image</span>
                <span class="block text-sm text-violet-700 mt-1">This is the main image customers and partners should see. JPG, PNG or WebP, max 5MB.</span>
                <input id="featured_image" type="file" name="featured_image" accept="image/jpeg,image/png,image/webp" class="mt-4 w-full" onchange="previewFeatured(event)">
                <img id="featured_preview" class="hidden mt-4 h-48 w-full object-cover rounded-xl" alt="Featured image preview">
            </label>
            <label class="block border-2 border-dashed border-slate-300 rounded-2xl p-6 cursor-pointer">
                <span class="block font-black">Additional media / gallery</span>
                <span class="block text-sm text-slate-500 mt-1">Upload up to 10 additional product images. These help customers understand what they are buying.</span>
                <input type="file" name="media[]" multiple accept="image/jpeg,image/png,image/webp" class="mt-4 w-full">
            </label>
        </div>

        <button type="submit" class="bg-black text-white px-6 py-3 rounded-lg font-bold">Create Product</button>
    </form>
</div>
<script>
function previewFeatured(event) { const file = event.target.files[0]; const preview = document.getElementById('featured_preview'); if (!file) { preview.classList.add('hidden'); return; } preview.src = URL.createObjectURL(file); preview.classList.remove('hidden'); }
</script>
</body>
</html>
