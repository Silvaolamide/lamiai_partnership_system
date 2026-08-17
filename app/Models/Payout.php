<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'partner_id',
        'program_id',
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

    public function partner()
    {
        return $this->belongsTo(
            ProgramPartner::class,
            'partner_id'
        );
    }

    public function program()
    {
        return $this->belongsTo(PartnershipProgram::class);
    }
}

