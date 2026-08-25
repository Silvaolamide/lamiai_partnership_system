<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialFollowParticipant extends Model
{
    protected $fillable = [
        'campaign_id', 'session_token', 'score', 'status', 'completed_at',
    ];

    protected $casts = ['completed_at' => 'datetime'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SocialFollowCampaign::class, 'campaign_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(SocialFollowVerification::class, 'participant_id');
    }
}
