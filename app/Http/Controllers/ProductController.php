<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->get();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products,sku'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['featured_image'] = $request->hasFile('featured_image')
            ? $request->file('featured_image')->store('products/featured', 'public')
            : null;
        $validated['media'] = collect($request->file('media', []))
            ->map(fn ($file) => $file->store('products/media', 'public'))
            ->values()->all();

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug,' . $product->id],
            'description' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_media' => ['nullable', 'array'],
            'remove_media.*' => ['string'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products,sku,' . $product->id],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $currentMedia = is_array($product->media) ? $product->media : [];
        $removeMedia = collect($request->input('remove_media', []))->filter()->values();
        foreach ($removeMedia as $path) {
            if (in_array($path, $currentMedia, true)) Storage::disk('public')->delete($path);
        }
        $currentMedia = array_values(array_diff($currentMedia, $removeMedia->all()));
        $newMedia = collect($request->file('media', []))
            ->map(fn ($file) => $file->store('products/media', 'public'))->all();

        if ($request->hasFile('featured_image')) {
            if ($product->featured_image) Storage::disk('public')->delete($product->featured_image);
            $validated['featured_image'] = $request->file('featured_image')->store('products/featured', 'public');
        } else {
            $validated['featured_image'] = $product->featured_image;
        }

        $validated['media'] = array_values(array_merge($currentMedia, $newMedia));
        unset($validated['remove_media']);
        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }
}
