<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    //
    protected $fillable = [
        'program_id',
        'product_id',
        'event',
        'level',
        'commission_type',
        'value',
        'maximum_amount',
        'status',
        'priority',
    ];
    public function program()
    {
        return $this->belongsTo(PartnershipProgram::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'rule_id');
    }
}
