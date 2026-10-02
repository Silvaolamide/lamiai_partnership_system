<?php

namespace Tests\Feature;

use App\Models\MarketingCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingCampaignQuestionPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_saved_question_configuration_can_be_empty(): void
    {
        $campaign = MarketingCampaign::create([
            'owner_id' => User::factory()->create()->id,
            'name' => 'Empty Questions Campaign',
            'slug' => 'empty-questions-campaign',
            'headline' => 'Test',
            'redirect_url' => 'https://example.com',
            'status' => 'draft',
            'questions' => [],
        ]);

        $this->assertSame([], $campaign->fresh()->configuredQuestions());
    }

    public function test_saved_questions_are_returned_exactly_as_configured(): void
    {
        $questions = [
            [
                'id' => 'industry',
                'label' => 'What industry are you in?',
                'type' => 'text',
                'required' => true,
                'options' => [],
            ],
            [
                'id' => 'budget',
                'label' => 'What is your monthly budget?',
                'type' => 'single_choice',
                'required' => false,
                'options' => ['Under ₦50,000', '₦50,000 - ₦100,000', 'Over ₦100,000'],
            ],
        ];

        $campaign = MarketingCampaign::create([
            'owner_id' => User::factory()->create()->id,
            'name' => 'Configured Questions Campaign',
            'slug' => 'configured-questions-campaign',
            'headline' => 'Test',
            'redirect_url' => 'https://example.com',
            'status' => 'draft',
            'questions' => $questions,
        ]);

        $this->assertSame($questions, $campaign->fresh()->configuredQuestions());
    }

    public function test_edit_update_can_remove_all_questions_and_persist_that_change(): void
    {
        $user = User::factory()->create();
        $campaign = MarketingCampaign::create([
            'owner_id' => $user->id,
            'name' => 'Editable Campaign',
            'slug' => 'editable-campaign',
            'headline' => 'Test',
            'redirect_url' => 'https://example.com',
            'status' => 'draft',
            'questions' => [
                ['id' => 'old_question', 'label' => 'Old question', 'type' => 'text', 'required' => true, 'options' => []],
            ],
        ]);

        $response = $this->actingAs($user)->put(route('business.campaigns.update', $campaign), [
            'name' => $campaign->name,
            'headline' => $campaign->headline,
            'description' => $campaign->description,
            'redirect_url' => $campaign->redirect_url,
            'status' => $campaign->status,
            'questions' => [],
        ]);

        $response->assertRedirect(route('business.campaigns.index'));
        $this->assertSame([], $campaign->fresh()->questions);
        $this->assertSame([], $campaign->fresh()->configuredQuestions());
    }
}
