<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    //
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
