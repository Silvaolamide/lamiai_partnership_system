<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramPartner extends Model
{
    protected $fillable = [
        'program_id',
        'user_id',
        'partner_code',
        'status',
        'approval_context',
        'parent_partner_id',
        'joined_at',
        'approved_at',
    ];

    public function program()
    {
        return $this->belongsTo(PartnershipProgram::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parentPartner()
    {
        return $this->belongsTo(ProgramPartner::class, 'parent_partner_id');
    }

    public function childPartners()
    {
        return $this->hasMany(ProgramPartner::class, 'parent_partner_id');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'parent_partner_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'partner_id');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'partner_id');
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class, 'partner_id');
    }

    public function clicks()
    {
        return $this->hasMany(Click::class, 'partner_id');
    }
}
