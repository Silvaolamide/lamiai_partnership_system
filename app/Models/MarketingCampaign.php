<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingCampaign extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'slug', 'headline', 'description', 'redirect_url', 'status', 'questions',
    ];

    protected $casts = ['questions' => 'array'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(MarketingCampaignLead::class, 'campaign_id');
    }

    public function configuredQuestions(): array
    {
        // A null value means the campaign has never been configured and should
        // therefore use the original defaults. An empty array is a valid saved
        // configuration and must remain empty when the owner removes all questions.
        if (is_array($this->questions)) {
            return $this->questions;
        }

        return [
            ['id' => 'has_sold_online', 'label' => 'Have you ever sold anything online?', 'type' => 'single_choice', 'required' => true, 'options' => ['Yes', 'No']],
            ['id' => 'what_sold', 'label' => 'What did you sell?', 'type' => 'textarea', 'required' => false, 'options' => []],
            ['id' => 'sales_result', 'label' => 'How was the sales?', 'type' => 'single_choice', 'required' => false, 'options' => ['Very good', 'Good', 'Not good']],
        ];
    }
}
