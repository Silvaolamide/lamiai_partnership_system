<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    protected $fillable = [
        'program_id',
        'partner_id',
        'campaign_id',
        'referral_code',
        'ip_hash',
        'user_agent',
        'landing_url',
    ];

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
