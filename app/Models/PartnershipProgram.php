<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnershipProgram extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'slug', 'description', 'status', 'starts_at', 'ends_at',
        'attribution_window_days', 'minimum_payout', 'settings',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'program_products', 'program_id', 'product_id');
    }

    public function partners() { return $this->hasMany(ProgramPartner::class, 'program_id'); }
    public function commissionRules() { return $this->hasMany(CommissionRule::class, 'program_id'); }
    public function orders() { return $this->hasMany(Order::class, 'program_id'); }
    public function commissions() { return $this->hasMany(Commission::class, 'program_id'); }
    public function payouts() { return $this->hasMany(Payout::class, 'program_id'); }
    public function clicks() { return $this->hasMany(Click::class, 'program_id'); }
}
