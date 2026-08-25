<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SocialAccount extends Model
{
    protected $fillable = ['user_id', 'platform', 'handle', 'profile_url', 'is_enabled'];
    protected $casts = ['is_enabled' => 'boolean'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(SocialFollowCampaign::class, 'social_follow_campaign_accounts', 'social_account_id', 'campaign_id')
            ->withPivot(['points', 'sort_order'])->withTimestamps();
    }
}
