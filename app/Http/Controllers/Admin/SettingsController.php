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
        ]);
    }

    public function update(Request $request, PartnerApprovalService $approvalService)
    {
        $required = $request->boolean('partner_super_admin_approval_required');

        PlatformSetting::setValue('partner_super_admin_approval_required', $required);

        ProgramPartner::with('program')
            ->where('status', 'pending')
            ->each(fn (ProgramPartner $partner) => $approvalService->sync($partner));

        return back()->with('success', 'Partner approval settings updated successfully.');
    }
}
