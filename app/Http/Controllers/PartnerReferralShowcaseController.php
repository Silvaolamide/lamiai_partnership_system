<?php

namespace App\Http\Controllers;

use App\Models\ProgramPartner;
use Illuminate\Http\Request;

class PartnerReferralShowcaseController extends Controller
{
    public function show(Request $request, string $partnerCode)
    {
        $partner = ProgramPartner::query()
            ->where('partner_code', $partnerCode)
            ->where('status', 'active')
            ->with(['user'])
            ->firstOrFail();

        // The referral code identifies the originating program/partner record,
        // but the storefront should expose products from every active program
        // this partner is subscribed to.
        $subscribedPrograms = ProgramPartner::query()
            ->where('user_id', $partner->user_id)
            ->where('status', 'active')
            ->with([
                'program' => fn ($query) => $query->where('status', 'active')->with([
                    'products' => fn ($productQuery) => $productQuery->where('status', 'active')->orderBy('name'),
                    'commissionRules' => fn ($rules) => $rules->where('status', true)->where('event', 'sale')->orderBy('level')->orderByDesc('priority'),
                ]),
            ])
            ->get()
            ->filter(fn ($programPartner) => $programPartner->program)
            ->values();

        $program = $partner->program()->where('status', 'active')->with([
            'products' => fn ($query) => $query->where('status', 'active')->orderBy('name'),
            'commissionRules' => fn ($rules) => $rules->where('status', true)->where('event', 'sale')->orderBy('level')->orderByDesc('priority'),
        ])->first();

        abort_unless($program, 404);

        // Preserve the originating program for referral attribution. The
        // storefront product catalogue is broader than this one program.
        $request->session()->put([
            'referral_program_partner_id' => $partner->id,
            'referral_program_id' => $partner->program_id,
            'referral_created_at' => now()->timestamp,
        ]);

        $products = $subscribedPrograms
            ->flatMap(fn ($programPartner) => $programPartner->program->products)
            ->unique('id')
            ->sortBy('name')
            ->values();

        $rules = $program->commissionRules;
        $directRule = $rules->firstWhere('level', 1);
        $recruiterRule = $rules->firstWhere('level', 2);

        return view('partner.referral-showcase', [
            'partner' => $partner,
            'program' => $program,
            'subscribedPrograms' => $subscribedPrograms,
            'products' => $products,
            'directRule' => $directRule,
            'recruiterRule' => $recruiterRule,
            'joinUrl' => route('partner.apply', ['recruiter_code' => $partner->partner_code]),
        ]);
    }
}
