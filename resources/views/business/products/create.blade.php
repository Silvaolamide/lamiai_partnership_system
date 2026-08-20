@extends('business.portal')
@section('title','Add Product') @section('heading','Add Product')
@section('content')
<form method="POST" action="{{ route('business.products.store') }}" enctype="multipart/form-data" class="max-w-4xl bg-white border rounded-2xl p-6 shadow-soft space-y-6">
    @csrf
    <div class="grid md:grid-cols-2 gap-5">
        <label class="md:col-span-2"><span class="text-sm font-bold">Product name</span><input name="name" value="{{ old('name') }}" required class="mt-2 w-full rounded-xl border-slate-200"></label>
        <label class="md:col-span-2"><span class="text-sm font-bold">Description</span><textarea name="description" rows="4" class="mt-2 w-full rounded-xl border-slate-200">{{ old('description') }}</textarea></label>
        <label><span class="text-sm font-bold">Price</span><input type="number" min="0" step="0.01" name="price" value="{{ old('price') }}" required class="mt-2 w-full rounded-xl border-slate-200"></label>
        <label><span class="text-sm font-bold">Currency</span><input name="currency" value="{{ old('currency','NGN') }}" required class="mt-2 w-full rounded-xl border-slate-200"></label>
        <label><span class="text-sm font-bold">SKU</span><input name="sku" value="{{ old('sku') }}" class="mt-2 w-full rounded-xl border-slate-200"></label>
        <label><span class="text-sm font-bold">Status</span><select name="status" class="mt-2 w-full rounded-xl border-slate-200"><option value="active">Active</option><option value="draft">Draft</option><option value="inactive">Inactive</option></select></label>
    </div>

    <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5 space-y-4">
        <div><h3 class="font-black text-lg">Product media</h3><p class="mt-1 text-sm text-slate-600">Add a strong featured image for the storefront and supporting images for the product gallery.</p></div>
        <label class="block rounded-2xl border-2 border-dashed border-violet-300 bg-white p-5 cursor-pointer"><span class="block font-black text-violet-900">Featured image</span><span class="block text-xs text-slate-500 mt-1">JPG, PNG or WebP • maximum 5MB</span><input id="featured_image" type="file" name="featured_image" accept="image/jpeg,image/png,image/webp" class="mt-3 w-full" onchange="previewProductImage(event)"><img id="featured_preview" class="hidden mt-4 h-48 w-full rounded-xl object-cover" alt="Featured image preview"></label>
        <label class="block rounded-2xl border-2 border-dashed border-slate-300 bg-white p-5 cursor-pointer"><span class="block font-black">Additional product media</span><span class="block text-xs text-slate-500 mt-1">Upload up to 10 additional images.</span><input type="file" name="media[]" multiple accept="image/jpeg,image/png,image/webp" class="mt-3 w-full"></label>
    </div>

    <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5"><div><h3 class="font-black text-lg">Product landing page</h3><p class="mt-1 text-sm text-slate-600">Choose the sales experience customers should see before they purchase.</p></div><div class="mt-4 grid gap-3 md:grid-cols-2">@foreach($landingPages as $key => $landingPage)<label class="cursor-pointer rounded-2xl border border-slate-200 bg-white p-4 hover:border-violet-400"><span class="flex items-start gap-3"><input type="radio" name="landing_page" value="{{ $key }}" @checked(old('landing_page','classic')===$key) class="mt-1"><span><span class="block font-black">{{ $landingPage['name'] }}</span><span class="mt-1 block text-sm text-slate-500">{{ $landingPage['description'] }}</span></span></span></label>@endforeach</div></div>

    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5"><div><h3 class="font-black text-lg">What does the customer receive?</h3><p class="mt-1 text-sm text-slate-600">After payment, the buyer will see this access button in My Purchases. You can point it to an ebook, video, course, download or external resource.</p></div><div class="mt-4 grid gap-5 md:grid-cols-2"><label><span class="text-sm font-bold">Delivery type</span><select name="delivery_type" class="mt-2 w-full rounded-xl border-slate-200"><option value="">Not configured yet</option><option value="link">External link</option><option value="video">Video</option><option value="ebook">Ebook / document</option><option value="download">Download</option><option value="course">Online course</option></select></label><label><span class="text-sm font-bold">Button label</span><input name="delivery_label" value="{{ old('delivery_label') }}" placeholder="e.g. Watch the Masterclass" class="mt-2 w-full rounded-xl border-slate-200"></label><label class="md:col-span-2"><span class="text-sm font-bold">Access URL</span><input type="url" name="delivery_url" value="{{ old('delivery_url') }}" placeholder="https://..." class="mt-2 w-full rounded-xl border-slate-200"></label></div></div>
    <div class="flex justify-end gap-3"><a href="{{ route('business.products.index') }}" class="rounded-xl border px-5 py-3 font-black">Cancel</a><button class="brand rounded-xl px-6 py-3 text-white font-black">Create product</button></div>
</form>
<script>function previewProductImage(e){const f=e.target.files[0],p=document.getElementById('featured_preview');if(!f){p.classList.add('hidden');return;}p.src=URL.createObjectURL(f);p.classList.remove('hidden');}</script>
@endsection
