<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialFollowCampaign extends Model
{
    protected $fillable = ['user_id', 'name', 'slug', 'headline', 'description', 'minimum_score', 'resource_type', 'resource_title', 'resource_url', 'cover_image', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function socialAccounts(): BelongsToMany { return $this->belongsToMany(SocialAccount::class, 'social_follow_campaign_accounts', 'campaign_id', 'social_account_id')->withPivot(['points', 'sort_order'])->withTimestamps(); }
    public function participants(): HasMany { return $this->hasMany(SocialFollowParticipant::class, 'campaign_id'); }
}
