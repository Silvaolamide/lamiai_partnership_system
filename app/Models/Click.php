<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    //
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
}
