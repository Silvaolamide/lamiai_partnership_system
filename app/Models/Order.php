<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function program()
    {
        return $this->belongsTo(PartnershipProgram::class);
    }

    public function partner()
    {
        return $this->belongsTo(
            ProgramPartner::class,
            'partner_id'
        );
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }
}
