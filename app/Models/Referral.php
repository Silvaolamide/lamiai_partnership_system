<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    //
    protected $fillable = [
        'program_id',
        'parent_partner_id',
        'child_partner_id',
        'status',
    ];
    public function program()
    {
        return $this->belongsTo(PartnershipProgram::class);
    }

    public function parentPartner()
    {
        return $this->belongsTo(
            ProgramPartner::class,
            'parent_partner_id'
        );
    }

    public function childPartner()
    {
        return $this->belongsTo(
            ProgramPartner::class,
            'child_partner_id'
        );
    }
}
