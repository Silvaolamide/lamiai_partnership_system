<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'slug', 'description', 'sku', 'price', 'currency', 'status', 'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'metadata' => 'json',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function partnershipPrograms()
    {
        return $this->belongsToMany(PartnershipProgram::class, 'program_products', 'product_id', 'program_id');
    }
}
