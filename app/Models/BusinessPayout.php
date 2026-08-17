<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessPayout extends Model
{
    protected $fillable = [
        'business_id',
        'amount',
        'currency',
        'method',
        'status',
        'reference',
        'notes',
        'requested_at',
        'approved_at',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(User::class, 'business_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'business_payout_id');
    }
}
