<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'slug', 'description', 'featured_image', 'media', 'sku', 'price', 'currency', 'status', 'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'metadata' => 'json',
        'media' => 'json',
    ];

    protected static function booted(): void
    {
        static::created(function (Product $product) {
            $product->syncBusinessUploadedMedia();
        });

        static::updated(function (Product $product) {
            $product->syncBusinessUploadedMedia();
        });
    }

    private function syncBusinessUploadedMedia(): void
    {
        if (!function_exists('request') || !request()->routeIs('business.products.*')) {
            return;
        }

        $request = request();
        $changes = [];

        if ($request->hasFile('featured_image')) {
            if ($this->featured_image) {
                Storage::disk('public')->delete($this->featured_image);
            }
            $changes['featured_image'] = $request->file('featured_image')->store('products/featured', 'public');
        }

        $currentMedia = is_array($this->media) ? $this->media : [];
        $removeMedia = collect($request->input('remove_media', []))->filter()->values();
        foreach ($removeMedia as $path) {
            if (in_array($path, $currentMedia, true)) {
                Storage::disk('public')->delete($path);
            }
        }
        $currentMedia = array_values(array_diff($currentMedia, $removeMedia->all()));

        $newMedia = collect($request->file('media', []))
            ->map(fn ($file) => $file->store('products/media', 'public'))
            ->all();

        $changes['media'] = array_values(array_merge($currentMedia, $newMedia));

        if ($changes) {
            $this->forceFill($changes)->saveQuietly();
        }
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function partnershipPrograms()
    {
        return $this->belongsToMany(PartnershipProgram::class, 'program_products', 'product_id', 'program_id');
    }
}
