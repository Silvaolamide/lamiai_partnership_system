<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasRoles;
    use HasFactory, Notifiable;

    public function programPartners() { return $this->hasMany(ProgramPartner::class); }
    public function socialAccounts(): HasMany { return $this->hasMany(SocialAccount::class); }
    public function socialFollowCampaigns(): HasMany { return $this->hasMany(SocialFollowCampaign::class); }

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at' => 'datetime', 'password' => 'hashed']; }
}
