<?php

namespace App\Http\Controllers;

use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Services\PartnerApprovalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessPartnerApprovalController extends Controller
{
    private function program(Request $request, PartnershipProgram $program): PartnershipProgram
    {
        abort_unless((int) $program->owner_id === (int) $request->user()->id, 403);
        return $program;
    }

    public function approve(Request $request, PartnershipProgram $program, ProgramPartner $partner, PartnerApprovalService $approvalService)
    {
        $program = $this->program($request, $program);
        abort_unless((int) $partner->program_id === (int) $program->id, 404);

        if (!$approvalService->businessApprovalRequired($program)) {
            return back()->with('error', 'Business approval is disabled for this program.');
        }

        $partner = $approvalService->approveByBusiness($partner);

        return back()->with(
            'success',
            $partner->status === 'active'
                ? $partner->user->name . ' is now an active partner.'
                : $partner->user->name . ' has been approved by your business and is awaiting the remaining requirement(s).'
        );
    }

    public function reject(Request $request, PartnershipProgram $program, ProgramPartner $partner)
    {
        $program = $this->program($request, $program);
        abort_unless((int) $partner->program_id === (int) $program->id, 404);

        if ($partner->status !== 'pending') {
            return back()->with('error', 'This application has already been processed.');
        }

        $partner->update(['status' => 'rejected']);

        return back()->with('success', $partner->user->name . ' has been rejected from this program.');
    }
}
