<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramPartner;
use App\Services\PartnerApprovalService;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function index(PartnerApprovalService $approvalService)
    {
        $partners = ProgramPartner::with([
            'user',
            'program',
            'parentPartner.user',
        ])
        ->latest()
        ->paginate(20);

        return view('admin.partners.index', [
            'partners' => $partners,
            'superAdminApprovalRequired' => $approvalService->superAdminApprovalRequired(),
        ]);
    }

    public function approve(ProgramPartner $partner, PartnerApprovalService $approvalService)
    {
        if ($partner->status === 'rejected') {
            return back()->with('error', 'This application has been rejected and cannot be approved.');
        }

        $partner = $approvalService->approveBySuperAdmin($partner);

        return back()->with(
            'success',
            $partner->status === 'active'
                ? $partner->user->name . ' has been fully approved as a partner.'
                : $partner->user->name . ' has received super admin approval and is awaiting the remaining requirement(s).'
        );
    }

    public function reject(ProgramPartner $partner)
    {
        if ($partner->status !== 'pending') {
            return back()->with('error', 'This application has already been processed.');
        }

        $partner->update(['status' => 'rejected']);

        return back()->with(
            'success',
            $partner->user->name . ' has been rejected.'
        );
    }

    private function generatePartnerCode(): string
    {
        do {
            $code = 'LAMI-' . Str::upper(Str::random(8));
        } while (ProgramPartner::where('partner_code', $code)->exists());

        return $code;
    }
}
