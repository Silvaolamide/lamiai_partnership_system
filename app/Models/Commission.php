<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    //
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
