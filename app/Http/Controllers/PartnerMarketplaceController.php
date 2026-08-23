<?php

namespace App\Http\Controllers;

use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Services\PartnerApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PartnerMarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $programs = PartnershipProgram::query()
            ->where('status', 'active')
            ->with(['owner', 'products', 'commissionRules' => fn ($query) => $query->where('status', true)->where('event', 'sale')->orderBy('level')])
            ->withCount('partners')
            ->latest()
            ->paginate(12);

        $joinedProgramIds = $request->user()
            ? ProgramPartner::where('user_id', $request->user()->id)->pluck('program_id')->all()
            : [];

        return view('partner.marketplace.index', compact('programs', 'joinedProgramIds'));
    }

    public function show(Request $request, PartnershipProgram $program, PartnerApprovalService $approvalService): View
    {
        abort_unless($program->status === 'active', 404);

        $program->load([
            'owner',
            'products',
            'commissionRules' => fn ($query) => $query->where('status', true)->where('event', 'sale')->orderBy('level'),
        ]);

        $membership = $request->user()
            ? ProgramPartner::where('user_id', $request->user()->id)->where('program_id', $program->id)->first()
            : null;

        $approval = $membership ? $approvalService->approvalSummary($membership) : null;

        return view('partner.marketplace.show', compact('program', 'membership', 'approval'));
    }

    public function join(Request $request, PartnershipProgram $program, PartnerApprovalService $approvalService)
    {
        abort_unless($program->status === 'active', 404);

        $existing = ProgramPartner::where('user_id', $request->user()->id)
            ->where('program_id', $program->id)
            ->first();

        if ($existing) {
            return redirect()->route('partner.marketplace.show', $program)->with('success',
                $existing->status === 'active'
                    ? 'You are already an active affiliate for this program.'
                    : 'Your affiliate application is already awaiting approval.'
            );
        }

        $partner = ProgramPartner::create([
            'program_id' => $program->id,
            'user_id' => $request->user()->id,
            'partner_code' => 'PENDING-' . Str::upper(Str::random(8)),
            'status' => 'pending',
            'approval_context' => 'program',
            'joined_at' => now(),
        ]);

        $partner = $approvalService->syncProgramEnrollment($partner->load('program'));
        $approval = $approvalService->approvalSummary($partner);

        if ($partner->status === 'active') {
            return redirect()->route('partner.marketplace.show', $program)
                ->with('success', 'You are now an active partner for this program. You can start promoting its products immediately.');
        }

        $message = $approval['approver']
            ? 'Your application has been submitted and is awaiting approval from ' . $approval['approver'] . '.'
            : 'Your application needs additional verification before it can become active.';

        return redirect()->route('partner.marketplace.show', $program)->with('success', $message);
    }
}
