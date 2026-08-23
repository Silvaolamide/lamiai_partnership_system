<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Order;
use App\Models\ProgramPartner;
use App\Models\User;
use App\Services\PartnerApprovalService;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function index(PartnerApprovalService $approvalService)
    {
        // A person can have several ProgramPartner records (one per program).
        // The admin partner directory is a people directory, so group by the
        // unique user account and expose the individual enrollments beneath it.
        $partners = User::query()
            ->whereHas('programPartners')
            ->with([
                'programPartners.program',
                'programPartners.parentPartner.user',
            ])
            ->withCount('programPartners')
            ->orderBy('name')
            ->paginate(20)
            ->through(function (User $user) {
                $memberships = $user->programPartners;
                $membershipIds = $memberships->pluck('id');
                $programs = $memberships->pluck('program')->filter()->unique('id')->values();

                $recruitedUserIds = ProgramPartner::query()
                    ->whereIn('parent_partner_id', $membershipIds)
                    ->whereNotNull('user_id')
                    ->distinct()
                    ->pluck('user_id');

                $user->admin_metrics = [
                    'sales' => (float) Order::whereIn('partner_id', $membershipIds)->where('status', 'paid')->sum('total'),
                    'orders' => Order::whereIn('partner_id', $membershipIds)->where('status', 'paid')->count(),
                    'recruits' => $recruitedUserIds->count(),
                    'active_programs' => $memberships->where('status', 'active')->count(),
                    'pending_programs' => $memberships->where('status', 'pending')->count(),
                    'earnings' => (float) Commission::whereIn('partner_id', $membershipIds)->whereNotIn('status', ['reversed', 'cancelled'])->sum('commission_amount'),
                ];
                $user->admin_programs = $programs;
                $user->admin_memberships = $memberships;

                return $user;
            });

        return view('admin.partners.index', [
            'partners' => $partners,
            'superAdminApprovalRequired' => $approvalService->superAdminApprovalRequired(),
        ]);
    }

    public function approve(ProgramPartner $partner, PartnerApprovalService $approvalService)
    {
        if ($partner->status === 'rejected') return back()->with('error', 'This application has been rejected and cannot be approved.');
        $partner = $approvalService->approveBySuperAdmin($partner);
        return back()->with('success', $partner->status === 'active' ? $partner->user->name . ' has been fully approved as a partner for ' . $partner->program->name . '.' : $partner->user->name . ' has received super admin approval for ' . $partner->program->name . ' and is awaiting the remaining requirement(s).');
    }

    public function approveForBusiness(ProgramPartner $partner, PartnerApprovalService $approvalService)
    {
        if ($partner->status === 'rejected') return back()->with('error', 'This application has been rejected and cannot be approved.');

        if (!$approvalService->businessApprovalRequired($partner->program)) {
            return back()->with('error', 'Business approval is not required for this program.');
        }

        $partner = $approvalService->approveByBusiness($partner);
        return back()->with('success', $partner->status === 'active'
            ? $partner->user->name . ' has been fully approved as a partner for ' . $partner->program->name . '.'
            : $partner->user->name . ' has received business approval for ' . $partner->program->name . ' and is awaiting the remaining requirement(s).');
    }

    public function reject(ProgramPartner $partner)
    {
        if ($partner->status !== 'pending') return back()->with('error', 'This application has already been processed.');
        $partner->update(['status' => 'rejected']);
        return back()->with('success', $partner->user->name . ' has been rejected for ' . $partner->program->name . '.');
    }

    private function generatePartnerCode(): string
    {
        do { $code = 'LAMI-' . Str::upper(Str::random(8)); } while (ProgramPartner::where('partner_code', $code)->exists());
        return $code;
    }
}
