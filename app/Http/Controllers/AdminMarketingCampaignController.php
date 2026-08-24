<?php

namespace App\Http\Controllers;

use App\Models\MarketingCampaign;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminMarketingCampaignController extends Controller
{
    public function index(Request $request)
    {
        $campaigns = MarketingCampaign::with('owner')
            ->withCount('leads')
            ->latest()
            ->paginate(20);

        $stats = [
            'campaigns' => MarketingCampaign::count(),
            'active' => MarketingCampaign::where('status', 'active')->count(),
            'leads' => MarketingCampaign::withCount('leads')->get()->sum('leads_count'),
            'businesses' => MarketingCampaign::distinct('owner_id')->count('owner_id'),
        ];

        return view('admin.marketing-campaigns.index', compact('campaigns', 'stats'));
    }

    public function leads(Request $request, MarketingCampaign $campaign)
    {
        $campaign->load('owner');
        $leads = $campaign->leads()->latest()->paginate(50);

        return view('admin.marketing-campaigns.leads', compact('campaign', 'leads'));
    }

    public function download(Request $request, MarketingCampaign $campaign): StreamedResponse
    {
        $questions = $campaign->configuredQuestions();
        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '-', $campaign->slug).'-leads-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($campaign, $questions) {
            $out = fopen('php://output', 'w');
            $headers = ['Lead ID', 'Name', 'WhatsApp', 'Email'];
            foreach ($questions as $question) {
                $headers[] = $question['label'];
            }
            $headers = array_merge($headers, ['UTM Source', 'UTM Medium', 'UTM Campaign', 'UTM Content', 'UTM Term', 'Landing Page', 'Submitted At']);
            fputcsv($out, $headers);

            $campaign->leads()->latest()->chunkById(500, function ($leads) use ($out, $questions) {
                foreach ($leads as $lead) {
                    $row = [$lead->id, $lead->name, $lead->whatsapp_number, $lead->email];
                    foreach ($questions as $question) {
                        $row[] = data_get($lead->responses, $question['id'], '');
                    }
                    $row = array_merge($row, [
                        $lead->utm_source,
                        $lead->utm_medium,
                        $lead->utm_campaign,
                        $lead->utm_content,
                        $lead->utm_term,
                        $lead->landing_page,
                        optional($lead->created_at)->format('Y-m-d H:i:s'),
                    ]);
                    fputcsv($out, $row);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
