<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\SocialAccount;
use App\Models\SocialFollowCampaign;
use App\Models\SocialFollowParticipant;
use App\Models\SocialFollowVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Str;

class SocialFollowController extends Controller
{
    private array $platforms = ['youtube', 'tiktok', 'instagram', 'facebook'];

    private function businessForUser($user): Business
    {
        return Business::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'slug' => Str::slug($user->name) . '-' . Str::lower(Str::random(6)),
                'status' => 'active',
            ]
        );
    }

    public function index(Request $request)
    {
        $business = $this->businessForUser($request->user());
        $campaigns = $business->socialFollowCampaigns()->latest()->get();
        $accounts = $business->socialAccounts()->get()->keyBy('platform');

        return view('business.social-follow.index', compact('business', 'campaigns', 'accounts'));
    }

    public function create(Request $request)
    {
        $business = $this->businessForUser($request->user());
        $accounts = $business->socialAccounts()->get()->keyBy('platform');
        return view('business.social-follow.create', ['business' => $business, 'accounts' => $accounts, 'platforms' => $this->platforms]);
    }

    public function store(Request $request)
    {
        $business = $this->businessForUser($request->user());
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'minimum_score' => ['required', 'integer', 'min:1'],
            'resource_type' => ['required', 'in:external_link,download'],
            'resource_title' => ['required', 'string', 'max:180'],
            'resource_url' => ['required', 'url', 'max:2048'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['in:youtube,tiktok,instagram,facebook'],
            'handles' => ['array'],
            'profile_urls' => ['array'],
        ]);

        $selected = collect($data['platforms']);
        if ($data['minimum_score'] > $selected->count()) {
            return back()->withErrors(['minimum_score' => 'Minimum score cannot exceed the number of enabled platforms.'])->withInput();
        }

        foreach ($selected as $platform) {
            $url = $data['profile_urls'][$platform] ?? null;
            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
                return back()->withErrors(["profile_urls.$platform" => 'Enter a valid profile URL.'])->withInput();
            }
            SocialAccount::updateOrCreate(
                ['business_id' => $business->id, 'platform' => $platform],
                ['handle' => $data['handles'][$platform] ?? null, 'profile_url' => $this->socialActionUrl($platform, $url), 'is_enabled' => true]
            );
        }

        $campaign = $business->socialFollowCampaigns()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(5)),
            'headline' => $data['headline'] ?? null,
            'description' => $data['description'] ?? null,
            'minimum_score' => $data['minimum_score'],
            'resource_type' => $data['resource_type'],
            'resource_title' => $data['resource_title'],
            'resource_url' => $data['resource_url'],
            'is_active' => true,
        ]);

        foreach ($selected as $index => $platform) {
            $account = $business->socialAccounts()->where('platform', $platform)->first();
            $campaign->socialAccounts()->attach($account->id, ['points' => 1, 'sort_order' => $index]);
        }

        return redirect()->route('business.social-follow.index')->with('success', 'Social Follow campaign created.');
    }

    public function edit(Request $request, SocialFollowCampaign $campaign)
    {
        $business = $this->businessForUser($request->user());
        abort_unless($campaign->business_id === $business->id, 403);
        $campaign->load('socialAccounts');
        return view('business.social-follow.edit', ['business' => $business, 'campaign' => $campaign, 'platforms' => $this->platforms]);
    }

    public function update(Request $request, SocialFollowCampaign $campaign)
    {
        $business = $this->businessForUser($request->user());
        abort_unless($campaign->business_id === $business->id, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'minimum_score' => ['required', 'integer', 'min:1'],
            'resource_type' => ['required', 'in:external_link,download'],
            'resource_title' => ['required', 'string', 'max:180'],
            'resource_url' => ['required', 'url', 'max:2048'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['in:youtube,tiktok,instagram,facebook'],
            'handles' => ['array'],
            'profile_urls' => ['array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['minimum_score'] > count($data['platforms'])) {
            return back()->withErrors(['minimum_score' => 'Minimum score cannot exceed the number of enabled platforms.'])->withInput();
        }

        $campaign->update([
            'name' => $data['name'], 'headline' => $data['headline'] ?? null, 'description' => $data['description'] ?? null,
            'minimum_score' => $data['minimum_score'], 'resource_type' => $data['resource_type'],
            'resource_title' => $data['resource_title'], 'resource_url' => $data['resource_url'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $sync = [];
        foreach ($data['platforms'] as $index => $platform) {
            $url = $data['profile_urls'][$platform] ?? null;
            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
                return back()->withErrors(["profile_urls.$platform" => 'Enter a valid profile URL.'])->withInput();
            }
            $account = SocialAccount::updateOrCreate(
                ['business_id' => $business->id, 'platform' => $platform],
                ['handle' => $data['handles'][$platform] ?? null, 'profile_url' => $this->socialActionUrl($platform, $url), 'is_enabled' => true]
            );
            $sync[$account->id] = ['points' => 1, 'sort_order' => $index];
        }
        $campaign->socialAccounts()->sync($sync);

        return redirect()->route('business.social-follow.index')->with('success', 'Campaign updated.');
    }

    public function show(string $businessSlug, string $campaignSlug)
    {
        $business = Business::where('slug', $businessSlug)->where('status', 'active')->firstOrFail();
        $campaign = $business->socialFollowCampaigns()->with('socialAccounts')->where('slug', $campaignSlug)->where('is_active', true)->firstOrFail();
        $token = session('social_follow.' . $campaign->id);
        $participant = $token ? SocialFollowParticipant::where('session_token', $token)->where('campaign_id', $campaign->id)->first() : null;

        return view('social-follow.show', compact('business', 'campaign', 'participant'));
    }

    public function start(Request $request, string $businessSlug, string $campaignSlug)
    {
        $business = Business::where('slug', $businessSlug)->where('status', 'active')->firstOrFail();
        $campaign = $business->socialFollowCampaigns()->where('slug', $campaignSlug)->where('is_active', true)->firstOrFail();
        $participant = SocialFollowParticipant::create(['campaign_id' => $campaign->id, 'session_token' => Str::random(64)]);
        session(['social_follow.' . $campaign->id => $participant->session_token]);
        return redirect()->route('social-follow.show', [$business->slug, $campaign->slug]);
    }

    public function verify(Request $request, string $businessSlug, string $campaignSlug, SocialAccount $account)
    {
        $business = Business::where('slug', $businessSlug)->where('status', 'active')->firstOrFail();
        $campaign = $business->socialFollowCampaigns()->with('socialAccounts')->where('slug', $campaignSlug)->where('is_active', true)->firstOrFail();
        abort_unless($campaign->socialAccounts->contains('id', $account->id), 404);
        $token = session('social_follow.' . $campaign->id);
        abort_unless($token, 419);
        $participant = SocialFollowParticipant::where('campaign_id', $campaign->id)->where('session_token', $token)->firstOrFail();

        SocialFollowVerification::updateOrCreate(
            ['participant_id' => $participant->id, 'social_account_id' => $account->id],
            ['status' => 'claimed', 'verification_method' => 'user_confirmation', 'verified_at' => now(), 'metadata' => ['note' => 'User confirmed the follow after visiting the platform.']]
        );

        $score = $campaign->socialAccounts->filter(fn ($a) => $participant->verifications()->where('social_account_id', $a->id)->whereIn('status', ['verified', 'claimed'])->exists())->sum(fn ($a) => (int) $a->pivot->points);
        $participant->update(['score' => $score, 'status' => $score >= $campaign->minimum_score ? 'completed' : 'in_progress', 'completed_at' => $score >= $campaign->minimum_score ? now() : null]);

        return back()->with('verified_platform', $account->platform);
    }

    public function unlock(Request $request, string $businessSlug, string $campaignSlug)
    {
        $business = Business::where('slug', $businessSlug)->where('status', 'active')->firstOrFail();
        $campaign = $business->socialFollowCampaigns()->with('socialAccounts')->where('slug', $campaignSlug)->where('is_active', true)->firstOrFail();
        $token = session('social_follow.' . $campaign->id);
        abort_unless($token, 419);
        $participant = SocialFollowParticipant::where('campaign_id', $campaign->id)->where('session_token', $token)->firstOrFail();
        abort_unless($participant->score >= $campaign->minimum_score, 403);
        return view('social-follow.unlock', compact('business', 'campaign', 'participant'));
    }

    private function socialActionUrl(string $platform, string $url): string
    {
        if ($platform === 'youtube' && !str_contains($url, 'sub_confirmation=')) {
            return $url . (str_contains($url, '?') ? '&' : '?') . 'sub_confirmation=1';
        }
        return $url;
    }
}
