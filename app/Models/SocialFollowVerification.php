<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialFollowVerification extends Model
{
    protected $fillable = [
        'participant_id', 'social_account_id', 'status', 'verification_method',
        'verified_at', 'metadata',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(SocialFollowParticipant::class, 'participant_id');
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }
}
