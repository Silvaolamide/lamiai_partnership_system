<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\SocialFollowCampaign;
use App\Models\SocialFollowParticipant;
use App\Models\SocialFollowVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SocialFollowController extends Controller
{
    private array $platforms = ['youtube', 'tiktok', 'instagram', 'facebook'];

    private function authorizeManager(Request $request): void
    {
        abort_unless($request->user()->hasRole('program_manager'), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeManager($request);
        $campaigns = $request->user()->socialFollowCampaigns()->with('socialAccounts')->latest()->get();
        $accounts = $request->user()->socialAccounts()->get()->keyBy('platform');
        return view('business.social-follow.index', compact('campaigns', 'accounts'));
    }

    public function create(Request $request)
    {
        $this->authorizeManager($request);
        $accounts = $request->user()->socialAccounts()->get()->keyBy('platform');
        return view('business.social-follow.create', ['accounts' => $accounts, 'platforms' => $this->platforms]);
    }

    public function store(Request $request)
    {
        $this->authorizeManager($request);
        $data = $this->validateCampaign($request);
        $campaign = $this->saveCampaign($request->user(), $data);
        return redirect()->route('business.social-follow.index')->with('success', 'Social Follow campaign created.');
    }

    public function edit(Request $request, SocialFollowCampaign $campaign)
    {
        $this->authorizeManager($request);
        abort_unless($campaign->user_id === $request->user()->id, 403);
        $campaign->load('socialAccounts');
        return view('business.social-follow.edit', ['campaign' => $campaign, 'platforms' => $this->platforms]);
    }

    public function update(Request $request, SocialFollowCampaign $campaign)
    {
        $this->authorizeManager($request);
        abort_unless($campaign->user_id === $request->user()->id, 403);
        $data = $this->validateCampaign($request, true);
        $this->saveCampaign($request->user(), $data, $campaign);
        return redirect()->route('business.social-follow.index')->with('success', 'Campaign updated.');
    }

    public function show(string $userSlug, string $campaignSlug)
    {
        $user = \App\Models\User::where('id', $this->decodeUserSlug($userSlug))->firstOrFail();
        $campaign = $user->socialFollowCampaigns()->with('socialAccounts')->where('slug', $campaignSlug)->where('is_active', true)->firstOrFail();
        $token = session('social_follow.' . $campaign->id);
        $participant = $token ? SocialFollowParticipant::where('session_token', $token)->where('campaign_id', $campaign->id)->first() : null;
        return view('social-follow.show', compact('user', 'campaign', 'participant'));
    }

    public function start(Request $request, string $userSlug, string $campaignSlug)
    {
        $user = \App\Models\User::where('id', $this->decodeUserSlug($userSlug))->firstOrFail();
        $campaign = $user->socialFollowCampaigns()->where('slug', $campaignSlug)->where('is_active', true)->firstOrFail();
        $participant = SocialFollowParticipant::create(['campaign_id' => $campaign->id, 'session_token' => Str::random(64)]);
        session(['social_follow.' . $campaign->id => $participant->session_token]);
        return redirect()->route('social-follow.show', [$this->userSlug($user), $campaign->slug]);
    }

    public function verify(Request $request, string $userSlug, string $campaignSlug, SocialAccount $account)
    {
        $user = \App\Models\User::where('id', $this->decodeUserSlug($userSlug))->firstOrFail();
        $campaign = $user->socialFollowCampaigns()->with('socialAccounts')->where('slug', $campaignSlug)->where('is_active', true)->firstOrFail();
        abort_unless($account->user_id === $user->id && $campaign->socialAccounts->contains('id', $account->id), 404);
        $token = session('social_follow.' . $campaign->id);
        abort_unless($token, 419);
        $participant = SocialFollowParticipant::where('campaign_id', $campaign->id)->where('session_token', $token)->firstOrFail();
        SocialFollowVerification::updateOrCreate(['participant_id' => $participant->id, 'social_account_id' => $account->id], ['status' => 'claimed', 'verification_method' => 'user_confirmation', 'verified_at' => now()]);
        $verified = $participant->verifications()->whereIn('status', ['verified', 'claimed'])->pluck('social_account_id');
        $score = $campaign->socialAccounts->whereIn('id', $verified)->sum(fn ($a) => (int) $a->pivot->points);
        $participant->update(['score' => $score, 'status' => $score >= $campaign->minimum_score ? 'completed' : 'in_progress', 'completed_at' => $score >= $campaign->minimum_score ? now() : null]);
        return back()->with('verified_platform', $account->platform);
    }

    public function unlock(Request $request, string $userSlug, string $campaignSlug)
    {
        $user = \App\Models\User::where('id', $this->decodeUserSlug($userSlug))->firstOrFail();
        $campaign = $user->socialFollowCampaigns()->where('slug', $campaignSlug)->where('is_active', true)->firstOrFail();
        $token = session('social_follow.' . $campaign->id);
        abort_unless($token, 419);
        $participant = SocialFollowParticipant::where('campaign_id', $campaign->id)->where('session_token', $token)->firstOrFail();
        abort_unless($participant->score >= $campaign->minimum_score, 403);
        return view('social-follow.unlock', compact('user', 'campaign', 'participant'));
    }

    private function validateCampaign(Request $request, bool $update = false): array
    {
        $data = $request->validate(['name'=>'required|string|max:120','headline'=>'nullable|string|max:180','description'=>'nullable|string|max:2000','minimum_score'=>'required|integer|min:1','resource_type'=>'required|in:external_link,download','resource_title'=>'required|string|max:180','resource_url'=>'required|url|max:2048','platforms'=>'required|array|min:1','platforms.*'=>'in:youtube,tiktok,instagram,facebook','handles'=>'nullable|array','profile_urls'=>'required|array','is_active'=>'nullable|boolean']);
        if ($data['minimum_score'] > count($data['platforms'])) return back()->withErrors(['minimum_score'=>'Minimum score cannot exceed the number of enabled platforms.'])->withInput()->throwResponse();
        foreach ($data['platforms'] as $platform) if (!filter_var($data['profile_urls'][$platform] ?? '', FILTER_VALIDATE_URL)) return back()->withErrors(["profile_urls.$platform"=>'Enter a valid profile URL.'])->withInput()->throwResponse();
        return $data;
    }

    private function saveCampaign($user, array $data, ?SocialFollowCampaign $campaign = null): SocialFollowCampaign
    {
        $campaign ??= new SocialFollowCampaign();
        if (!$campaign->exists) { $campaign->user_id = $user->id; $campaign->slug = Str::slug($data['name']).'-'.Str::lower(Str::random(5)); }
        $campaign->fill(['name'=>$data['name'],'headline'=>$data['headline']??null,'description'=>$data['description']??null,'minimum_score'=>$data['minimum_score'],'resource_type'=>$data['resource_type'],'resource_title'=>$data['resource_title'],'resource_url'=>$data['resource_url'],'is_active'=>$data['is_active'] ?? true]);
        $campaign->save();
        $sync=[];
        foreach ($data['platforms'] as $i=>$platform) { $account=SocialAccount::updateOrCreate(['user_id'=>$user->id,'platform'=>$platform],['handle'=>$data['handles'][$platform]??null,'profile_url'=>$this->socialActionUrl($platform,$data['profile_urls'][$platform]),'is_enabled'=>true]); $sync[$account->id]=['points'=>1,'sort_order'=>$i]; }
        $campaign->socialAccounts()->sync($sync);
        return $campaign;
    }

    private function socialActionUrl(string $platform, string $url): string { return $platform === 'youtube' && !str_contains($url,'sub_confirmation=') ? $url.(str_contains($url,'?')?'&':'?').'sub_confirmation=1' : $url; }
    private function userSlug($user): string { return base64_encode((string)$user->id); }
    private function decodeUserSlug(string $slug): int { return (int) base64_decode($slug, true); }
}
