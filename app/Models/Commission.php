<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'program_id',
        'order_id',
        'partner_id',
        'source_partner_id',
        'rule_id',
        'level',
        'commission_type',
        'rate',
        'base_amount',
        'commission_amount',
        'status',
        'available_at',
        'paid_at',
        'reversed_at',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'base_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'available_at' => 'datetime',
        'paid_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function program()
    {
        return $this->belongsTo(PartnershipProgram::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function partner()
    {
        return $this->belongsTo(
            ProgramPartner::class,
            'partner_id'
        );
    }

    public function sourcePartner()
    {
        return $this->belongsTo(
            ProgramPartner::class,
            'source_partner_id'
        );
    }

    public function rule()
    {
        return $this->belongsTo(
            CommissionRule::class,
            'rule_id'
        );
    }
}

