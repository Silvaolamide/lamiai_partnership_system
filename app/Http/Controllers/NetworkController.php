<?php

namespace App\Http\Controllers;

use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use Illuminate\Http\Request;

class NetworkController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless(
            $user->hasAnyRole(['super_admin', 'program_manager', 'partner']),
            403
        );

        $selectedPartnerId = $request->integer('partner');

        if ($user->hasRole('super_admin')) {
            $programs = PartnershipProgram::query()
                ->with('owner:id,name,business_name')
                ->orderBy('name')
                ->get();

            $partners = ProgramPartner::query()
                ->with('user:id,name,email')
                ->orderBy('program_id')
                ->orderBy('parent_partner_id')
                ->orderBy('id')
                ->get();

            $title = 'Partner Recruitment Network';
            $subtitle = 'See every affiliate and the partners they have recruited across the platform.';
        } elseif ($user->hasRole('program_manager')) {
            $programs = PartnershipProgram::query()
                ->where('owner_id', $user->id)
                ->with('owner:id,name,business_name')
                ->orderBy('name')
                ->get();

            $partners = ProgramPartner::query()
                ->whereIn('program_id', $programs->pluck('id'))
                ->with('user:id,name,email')
                ->orderBy('program_id')
                ->orderBy('parent_partner_id')
                ->orderBy('id')
                ->get();

            $title = 'Affiliate Recruitment Network';
            $subtitle = 'See every affiliate in your programs and open any partner to view the team they recruited.';
        } else {
            $myPartners = ProgramPartner::query()
                ->where('user_id', $user->id)
                ->with('program.owner:id,name,business_name')
                ->get();

            $programIds = $myPartners->pluck('program_id')->unique()->values();
            $programs = PartnershipProgram::query()
                ->whereIn('id', $programIds)
                ->with('owner:id,name,business_name')
                ->orderBy('name')
                ->get();

            $allNetworkPartners = ProgramPartner::query()
                ->whereIn('program_id', $programIds)
                ->with('user:id,name,email')
                ->orderBy('program_id')
                ->orderBy('parent_partner_id')
                ->orderBy('id')
                ->get();

            $visibleIds = $myPartners->pluck('id')->flip();

            do {
                $newIds = $allNetworkPartners
                    ->filter(fn (ProgramPartner $partner) =>
                        $partner->parent_partner_id !== null
                        && $visibleIds->has($partner->parent_partner_id)
                    )
                    ->pluck('id')
                    ->reject(fn ($id) => $visibleIds->has($id));

                foreach ($newIds as $id) {
                    $visibleIds[$id] = true;
                }
            } while ($newIds->isNotEmpty());

            $partners = $allNetworkPartners
                ->filter(fn (ProgramPartner $partner) => $visibleIds->has($partner->id))
                ->values();

            $title = 'My Recruitment Network';
            $subtitle = 'Track the affiliates you recruited and the teams they are building.';
            $selectedPartnerId = null;
        }

        $selectedPartner = null;
        if ($selectedPartnerId && !$user->hasRole('partner')) {
            $selectedPartner = $partners->firstWhere('id', $selectedPartnerId);
            abort_unless($selectedPartner, 404);
        }

        $trees = $programs->map(function (PartnershipProgram $program) use ($partners, $user, $selectedPartner) {
            $programPartners = $partners->where('program_id', $program->id)->values();
            $children = $programPartners->groupBy(fn (ProgramPartner $partner) => (string) ($partner->parent_partner_id ?? 'root'));

            if ($selectedPartner && (int) $selectedPartner->program_id === (int) $program->id) {
                $roots = collect([$selectedPartner]);
            } elseif ($selectedPartner) {
                return null;
            } else {
                $roots = $programPartners->filter(function (ProgramPartner $partner) use ($user) {
                    if ($user->hasRole('partner')) {
                        return (int) $partner->user_id === (int) $user->id;
                    }

                    return $partner->parent_partner_id === null;
                })->values();
            }

            return [
                'program' => $program,
                // This is intentionally a program-enrollment count. The
                // overall totals below count unique people.
                'partners_count' => $programPartners->pluck('user_id')->filter()->unique()->count(),
                'roots' => $roots,
                'children' => $children,
                'selected_partner' => $selectedPartner,
            ];
        })->filter(fn ($tree) => $tree !== null && $tree['partners_count'] > 0)->values();

        // Network rows are program memberships. Deduplicate people for the
        // headline metrics so a partner in multiple programs is still one
        // person in the network totals.
        $uniquePartnerUserIds = $partners->pluck('user_id')->filter()->unique();
        $recruitingParentIds = ProgramPartner::query()
            ->whereIn('parent_partner_id', $partners->pluck('id'))
            ->pluck('parent_partner_id')
            ->unique();
        $recruiterUserIds = $partners
            ->whereIn('id', $recruitingParentIds)
            ->pluck('user_id')
            ->filter()
            ->unique();

        return view('network.index', [
            'trees' => $trees,
            'title' => $title,
            'subtitle' => $subtitle,
            'totalPartners' => $uniquePartnerUserIds->count(),
            'totalRecruiters' => $recruiterUserIds->count(),
            'selectedPartner' => $selectedPartner,
        ]);
    }
}
