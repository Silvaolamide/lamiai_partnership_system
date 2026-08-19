<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSubmission extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'bank_name',
        'transaction_reference',
        'transfer_date',
        'proof_path',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_date' => 'date',
        'verified_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
