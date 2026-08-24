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

    private function defaultQuestions(): array
    {
        return [
            ['id' => 'has_sold_online', 'label' => 'Have you ever sold anything online?', 'type' => 'single_choice', 'required' => true, 'options' => ['Yes', 'No']],
            ['id' => 'what_sold', 'label' => 'What did you sell?', 'type' => 'textarea', 'required' => false, 'options' => []],
            ['id' => 'sales_result', 'label' => 'How was the sales?', 'type' => 'single_choice', 'required' => false, 'options' => ['Very good', 'Good', 'Not good']],
        ];
    }

    private function normalizeQuestions(Request $request): array
    {
        $questions = $request->input('questions', []);
        $allowed = ['single_choice', 'text', 'textarea', 'email', 'phone'];
        $out = [];
        foreach ($questions as $q) {
            if (!is_array($q)) continue;
            $label = trim((string) ($q['label'] ?? ''));
            $type = (string) ($q['type'] ?? 'text');
            if ($label === '' || !in_array($type, $allowed, true)) continue;
            $id = preg_replace('/[^a-z0-9_]+/', '_', strtolower((string) ($q['id'] ?? Str::slug($label, '_'))));
            $id = trim($id, '_') ?: Str::random(8);
            if (in_array($id, ['name', 'whatsapp_number', 'email'], true)) continue;
            $options = array_values(array_filter(array_map('trim', (array) ($q['options'] ?? []))));
            if ($type === 'single_choice' && count($options) < 2) continue;
            $out[] = ['id' => $id, 'label' => $label, 'type' => $type, 'required' => !empty($q['required']), 'options' => $options];
        }
        return array_slice($out, 0, 12);
    }

    public function index(Request $request): View
    {
        $campaigns = MarketingCampaign::where('owner_id', $request->user()->id)->withCount('leads')->latest()->paginate(12);
        return view('business.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        return view('business.campaigns.create', ['questions' => $this->defaultQuestions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'headline' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'], 'redirect_url' => ['required', 'url:http,https', 'max:2048'],
            'status' => ['required', 'in:draft,active,paused'],
        ]);
        $data['questions'] = $this->normalizeQuestions($request);
        $base = Str::slug($data['name']); $slug = $base ?: Str::random(8); $i = 1;
        while (MarketingCampaign::where('slug', $slug)->exists()) $slug = $base.'-'.(++$i);
        $data['slug'] = $slug; $data['owner_id'] = $request->user()->id;
        MarketingCampaign::create($data);
        return redirect()->route('business.campaigns.index')->with('success', 'Marketing campaign created successfully.');
    }

    public function edit(Request $request, MarketingCampaign $campaign): View
    {
        $campaign = $this->own($request, $campaign);
        return view('business.campaigns.edit', ['campaign' => $campaign, 'questions' => $campaign->configuredQuestions()]);
    }

    public function update(Request $request, MarketingCampaign $campaign): RedirectResponse
    {
        $campaign = $this->own($request, $campaign);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'headline' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'], 'redirect_url' => ['required', 'url:http,https', 'max:2048'],
            'status' => ['required', 'in:draft,active,paused'],
        ]);
        $data['questions'] = $this->normalizeQuestions($request);
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
        $rules = ['name' => ['required', 'string', 'min:2', 'max:120'], 'whatsapp_number' => ['required', 'string', 'min:7', 'max:50'], 'email' => ['required', 'email', 'max:255'], 'utm_source' => ['nullable', 'string', 'max:255'], 'utm_medium' => ['nullable', 'string', 'max:255'], 'utm_campaign' => ['nullable', 'string', 'max:255'], 'utm_content' => ['nullable', 'string', 'max:255'], 'utm_term' => ['nullable', 'string', 'max:255'], 'website' => ['nullable', 'max:0']];
        foreach ($campaign->configuredQuestions() as $q) {
            $key = 'q_'.$q['id'];
            if ($q['type'] === 'email') $rules[$key] = $q['required'] ? ['required','email','max:255'] : ['nullable','email','max:255'];
            elseif ($q['type'] === 'phone') $rules[$key] = $q['required'] ? ['required','string','max:50'] : ['nullable','string','max:50'];
            elseif ($q['type'] === 'single_choice') $rules[$key] = $q['required'] ? ['required','string','in:'.implode(',', $q['options'])] : ['nullable','string','in:'.implode(',', $q['options'])];
            else $rules[$key] = $q['required'] ? ['required','string','max:2000'] : ['nullable','string','max:2000'];
        }
        $data = $request->validate($rules);
        $responses = [];
        foreach ($campaign->configuredQuestions() as $q) if (array_key_exists('q_'.$q['id'], $data)) $responses[$q['id']] = $data['q_'.$q['id']];
        $lead = ['campaign_id' => $campaign->id, 'name' => $data['name'], 'whatsapp_number' => $data['whatsapp_number'], 'email' => $data['email'], 'utm_source' => $data['utm_source'] ?? null, 'utm_medium' => $data['utm_medium'] ?? null, 'utm_campaign' => $data['utm_campaign'] ?? null, 'utm_content' => $data['utm_content'] ?? null, 'utm_term' => $data['utm_term'] ?? null, 'landing_page' => $request->headers->get('referer'), 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'responses' => $responses];
        $lead['has_sold_online'] = isset($responses['has_sold_online']) ? $responses['has_sold_online'] === 'Yes' : false;
        $lead['what_sold'] = $responses['what_sold'] ?? null;
        $lead['sales_result'] = isset($responses['sales_result']) ? ['Very good' => 'very_good', 'Good' => 'good', 'Not good' => 'not_good'][$responses['sales_result']] ?? null : null;
        MarketingCampaignLead::create($lead);

        return redirect()->route('marketing.campaign.success', $campaign);
    }

    public function success(MarketingCampaign $campaign): View
    {
        return view('marketing-campaign.success', compact('campaign'));
    }
}
