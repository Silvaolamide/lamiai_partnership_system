<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles, HasFactory, Notifiable;

    public function programPartners()
    {
        return $this->hasMany(ProgramPartner::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    protected $fillable = [
        'name', 'email', 'password', 'business_name', 'business_website',
        'business_industry', 'business_phone', 'business_super_admin_approved_at',
        'business_rejected_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'business_super_admin_approved_at' => 'datetime',
            'business_rejected_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail());
    }
}
