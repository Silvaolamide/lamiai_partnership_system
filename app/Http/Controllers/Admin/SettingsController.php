<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformPaymentSetting;
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
            'adminChargePercent' => (float) PlatformSetting::getValue('admin_charge_percent', 0),
            'paymentSettings' => PlatformPaymentSetting::current(),
        ]);
    }

    public function update(Request $request, PartnerApprovalService $approvalService)
    {
        $data = $request->validate([
            'payout_delay_days' => ['required', 'integer', 'min:0', 'max:90'],
            'admin_charge_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'support_whatsapp' => ['nullable', 'string', 'max:50'],
            'support_email' => ['nullable', 'email', 'max:255'],
        ]);

        PlatformSetting::setValue('partner_super_admin_approval_required', $request->boolean('partner_super_admin_approval_required'));
        PlatformSetting::setValue('payout_delay_days', $data['payout_delay_days']);
        PlatformSetting::setValue('admin_charge_percent', $data['admin_charge_percent']);

        PlatformPaymentSetting::current()->update([
            'bank_name' => $data['bank_name'] ?? null,
            'account_name' => $data['account_name'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'support_phone' => $data['support_phone'] ?? null,
            'support_whatsapp' => $data['support_whatsapp'] ?? null,
            'support_email' => $data['support_email'] ?? null,
        ]);

        ProgramPartner::with('program')->where('status', 'pending')->each(fn (ProgramPartner $partner) => $approvalService->sync($partner));

        return back()->with('success', 'Platform settings updated successfully.');
    }
}
