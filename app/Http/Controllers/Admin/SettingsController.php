<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.index', [
            'partnerSuperAdminApprovalRequired' => (bool) PlatformSetting::getValue('partner_super_admin_approval_required', true),
        ]);
    }

    public function update(Request $request)
    {
        PlatformSetting::setValue(
            'partner_super_admin_approval_required',
            $request->boolean('partner_super_admin_approval_required')
        );

        return back()->with('success', 'Partner approval settings updated successfully.');
    }
}
