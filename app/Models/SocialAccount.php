<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    protected $fillable = ['user_id', 'platform', 'handle', 'profile_url', 'is_enabled'];
    protected $casts = ['is_enabled' => 'boolean'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function followUrl(): string
    {
        $url = trim($this->profile_url ?? '');
        if ($url === '') return '#';
        if ($this->platform === 'youtube') {
            $separator = str_contains($url, '?') ? '&' : '?';
            return $url . $separator . 'sub_confirmation=1';
        }
        return $url;
    }
}
