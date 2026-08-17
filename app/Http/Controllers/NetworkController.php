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
            $user->hasAnyRole(['super_admin', 'partner']),
            403
        );

        if ($user->hasRole('super_admin')) {
            // Super admins can inspect the complete recruitment network across
            // every business/program on the platform.
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
        } else {
            // A partner can only see their own recruitment tree. Start with
            // their memberships, then recursively include descendants only.
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
        }

        $trees = $programs->map(function (PartnershipProgram $program) use ($partners, $user) {
            $programPartners = $partners->where('program_id', $program->id)->values();
            $children = $programPartners->groupBy(fn (ProgramPartner $partner) => (string) ($partner->parent_partner_id ?? 'root'));

            $roots = $programPartners->filter(function (ProgramPartner $partner) use ($user) {
                if ($user->hasRole('partner')) {
                    return (int) $partner->user_id === (int) $user->id;
                }

                return $partner->parent_partner_id === null;
            })->values();

            return [
                'program' => $program,
                'partners_count' => $programPartners->count(),
                'roots' => $roots,
                'children' => $children,
            ];
        })->filter(fn (array $tree) => $tree['partners_count'] > 0)->values();

        return view('network.index', [
            'trees' => $trees,
            'title' => $title,
            'subtitle' => $subtitle,
            'totalPartners' => $partners->count(),
            'totalRecruiters' => $partners->filter(fn (ProgramPartner $partner) => $partner->childPartners()->exists())->count(),
        ]);
    }
}
