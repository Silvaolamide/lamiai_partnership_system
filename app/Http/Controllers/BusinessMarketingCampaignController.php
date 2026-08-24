<?php

namespace App\Http\Controllers;

use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignLead;
use Illuminate\Database\QueryException;
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

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function normalizeWhatsapp(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', trim($phone));
        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        }
        // Normalize Nigerian local numbers so 0703..., 703..., 234703... and +234703...
        // are treated as the same WhatsApp number. Other international numbers are kept in E.164-like form.
        if (str_starts_with($phone, '0') && strlen($phone) === 11) {
            $phone = '+234' . substr($phone, 1);
        } elseif (str_starts_with($phone, '234') && !str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
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

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'whatsapp_number' => ['required', 'string', 'min:7', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'], 'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'], 'utm_content' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'], 'website' => ['nullable', 'max:0'],
        ];

        foreach ($campaign->configuredQuestions() as $q) {
            $key = 'q_'.$q['id'];
            if ($q['type'] === 'email') $rules[$key] = $q['required'] ? ['required','email','max:255'] : ['nullable','email','max:255'];
            elseif ($q['type'] === 'phone') $rules[$key] = $q['required'] ? ['required','string','max:50'] : ['nullable','string','max:50'];
            elseif ($q['type'] === 'single_choice') $rules[$key] = $q['required'] ? ['required','string','in:'.implode(',', $q['options'])] : ['nullable','string','in:'.implode(',', $q['options'])];
            else $rules[$key] = $q['required'] ? ['required','string','max:2000'] : ['nullable','string','max:2000'];
        }

        $data = $request->validate($rules);
        $normalizedEmail = $this->normalizeEmail($data['email']);
        $normalizedWhatsapp = $this->normalizeWhatsapp($data['whatsapp_number']);

        // Check existing records as well as the database unique indexes. This catches older
        // leads created before normalization was introduced (e.g. 0703... vs +234703...).
        $duplicate = $campaign->leads()
            ->where(function ($query) use ($normalizedEmail, $normalizedWhatsapp) {
                $query->where('normalized_email', $normalizedEmail)
                    ->orWhere('normalized_whatsapp', $normalizedWhatsapp);
            })->exists();

        if (!$duplicate) {
            $duplicate = $campaign->leads()->get(['email', 'whatsapp_number'])->contains(function ($lead) use ($normalizedEmail, $normalizedWhatsapp) {
                return $this->normalizeEmail((string) $lead->email) === $normalizedEmail
                    || $this->normalizeWhatsapp((string) $lead->whatsapp_number) === $normalizedWhatsapp;
            });
        }

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'duplicate' => 'You have already submitted this application. We already have your details for this campaign.'
            ]);
        }

        $responses = [];
        foreach ($campaign->configuredQuestions() as $q) {
            if (array_key_exists('q_'.$q['id'], $data)) {
                $responses[$q['id']] = $data['q_'.$q['id']];
            }
        }

        $lead = [
            'campaign_id' => $campaign->id,
            'name' => $data['name'],
            'whatsapp_number' => $data['whatsapp_number'],
            'email' => $data['email'],
            'normalized_email' => $normalizedEmail,
            'normalized_whatsapp' => $normalizedWhatsapp,
            'utm_source' => $data['utm_source'] ?? null, 'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null, 'utm_content' => $data['utm_content'] ?? null,
            'utm_term' => $data['utm_term'] ?? null, 'landing_page' => $request->headers->get('referer'),
            'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'responses' => $responses,
            'has_sold_online' => isset($responses['has_sold_online']) ? $responses['has_sold_online'] === 'Yes' : false,
            'what_sold' => $responses['what_sold'] ?? null,
            'sales_result' => isset($responses['sales_result']) ? ['Very good' => 'very_good', 'Good' => 'good', 'Not good' => 'not_good'][$responses['sales_result']] ?? null : null,
        ];

        try {
            MarketingCampaignLead::create($lead);
        } catch (QueryException $e) {
            // A concurrent double-click/request can race the application-level check;
            // the unique campaign/email and campaign/WhatsApp indexes make this safe.
            if ((int) $e->getCode() === 23000) {
                return back()->withInput()->withErrors([
                    'duplicate' => 'You have already submitted this application. We already have your details for this campaign.'
                ]);
            }
            throw $e;
        }

        session()->flash('campaign_submission_'.$campaign->id, true);
        return redirect()->route('marketing.campaign.success', $campaign);
    }

    public function success(Request $request, MarketingCampaign $campaign): View|RedirectResponse
    {
        $sessionKey = 'campaign_submission_'.$campaign->id;
        abort_unless($campaign->status === 'active', 404);

        if (!$request->session()->pull($sessionKey, false)) {
            return redirect()->route('marketing.campaign.show', $campaign);
        }

        return view('marketing-campaign.success', compact('campaign'));
    }
}
