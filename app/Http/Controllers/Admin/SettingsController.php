<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\ProgramPartner;
use App\Services\PartnerApprovalService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.index', [
            'partnerSuperAdminApprovalRequired' => (bool) PlatformSetting::getValue('partner_super_admin_approval_required', true),
            'payoutDelayDays' => (int) PlatformSetting::getValue('payout_delay_days', 7),
        ]);
    }

    public function update(Request $request, PartnerApprovalService $approvalService)
    {
        $data = $request->validate([
            'payout_delay_days' => ['required', 'integer', 'min:0', 'max:90'],
        ]);

        PlatformSetting::setValue(
            'partner_super_admin_approval_required',
            $request->boolean('partner_super_admin_approval_required')
        );
        PlatformSetting::setValue('payout_delay_days', $data['payout_delay_days']);

        ProgramPartner::with('program')
            ->where('status', 'pending')
            ->each(fn (ProgramPartner $partner) => $approvalService->sync($partner));

        return back()->with('success', 'Platform settings updated successfully.');
    }
}
