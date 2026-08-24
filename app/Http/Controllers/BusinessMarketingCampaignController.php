<?php

namespace App\Http\Controllers;

use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BusinessMarketingCampaignController extends Controller
{
    private function own(Request $request, MarketingCampaign $campaign): MarketingCampaign
    {
        abort_unless((int) $campaign->owner_id === (int) $request->user()->id, 403);
        return $campaign;
    }

    public function index(Request $request): View
    {
        $campaigns = MarketingCampaign::where('owner_id', $request->user()->id)
            ->withCount('leads')->latest()->paginate(12);
        return view('business.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        return view('business.campaigns.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'headline' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'redirect_url' => ['required', 'url:http,https', 'max:2048'],
            'status' => ['required', 'in:draft,active,paused'],
        ]);
        $base = Str::slug($data['name']);
        $slug = $base ?: Str::random(8);
        $i = 1;
        while (MarketingCampaign::where('slug', $slug)->exists()) $slug = $base.'-'.(++$i);
        $data['slug'] = $slug;
        $data['owner_id'] = $request->user()->id;
        MarketingCampaign::create($data);
        return redirect()->route('business.campaigns.index')->with('success', 'Marketing campaign created successfully.');
    }

    public function edit(Request $request, MarketingCampaign $campaign): View
    {
        return view('business.campaigns.edit', ['campaign' => $this->own($request, $campaign)]);
    }

    public function update(Request $request, MarketingCampaign $campaign): RedirectResponse
    {
        $campaign = $this->own($request, $campaign);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'headline' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'redirect_url' => ['required', 'url:http,https', 'max:2048'],
            'status' => ['required', 'in:draft,active,paused'],
        ]);
        $campaign->update($data);
        return redirect()->route('business.campaigns.index')->with('success', 'Campaign updated successfully.');
    }

    public function toggle(Request $request, MarketingCampaign $campaign): RedirectResponse
    {
        $campaign = $this->own($request, $campaign);
        $campaign->update(['status' => $campaign->status === 'active' ? 'paused' : 'active']);
        return back()->with('success', 'Campaign status updated.');
    }

    public function leads(Request $request, MarketingCampaign $campaign): View
    {
        $campaign = $this->own($request, $campaign);
        $leads = $campaign->leads()->latest()->paginate(25);
        return view('business.campaigns.leads', compact('campaign', 'leads'));
    }

    public function show(MarketingCampaign $campaign): View
    {
        abort_unless($campaign->status === 'active', 404);
        return view('marketing-campaign.form', compact('campaign'));
    }

    public function submit(Request $request, MarketingCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->status === 'active', 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'whatsapp_number' => ['required', 'string', 'min:7', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'has_sold_online' => ['required', 'boolean'],
            'what_sold' => ['nullable', 'string', 'max:1000'],
            'sales_result' => ['nullable', 'in:very_good,good,not_good'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'max:0'],
        ]);
        unset($data['website']);
        $data['campaign_id'] = $campaign->id;
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->userAgent();
        $data['landing_page'] = $request->headers->get('referer');
        $data['has_sold_online'] = (bool) $data['has_sold_online'];
        MarketingCampaignLead::create($data);
        return redirect()->away($campaign->redirect_url);
    }
}
