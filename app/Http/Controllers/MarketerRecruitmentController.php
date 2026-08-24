<?php

namespace App\Http\Controllers;

use App\Models\MarketerLead;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class MarketerRecruitmentController extends Controller
{
    public function show(Request $request)
    {
        return view('marketer-recruitment.form', [
            'utm' => $request->only(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term']),
        ]);
    }

    public function store(Request $request)
    {
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
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->userAgent();
        $data['landing_page'] = $request->headers->get('referer');
        $data['has_sold_online'] = (bool) $data['has_sold_online'];

        MarketerLead::create($data);

        $redirect = trim((string) PlatformSetting::getValue('marketer_recruitment_redirect_url', ''));

        if ($redirect !== '') {
            return redirect()->away($redirect);
        }

        return redirect()->route('marketer.recruitment.thank-you');
    }

    public function thankYou()
    {
        return view('marketer-recruitment.thank-you');
    }

    public function admin()
    {
        return view('admin.marketer-recruitment.index', [
            'redirectUrl' => PlatformSetting::getValue('marketer_recruitment_redirect_url', ''),
            'leads' => MarketerLead::latest()->paginate(25),
            'leadCount' => MarketerLead::count(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'redirect_url' => ['nullable', 'url', 'max:2048'],
        ]);

        PlatformSetting::setValue('marketer_recruitment_redirect_url', trim((string) ($data['redirect_url'] ?? '')));

        return back()->with('success', 'Marketer recruitment settings updated.');
    }
}
