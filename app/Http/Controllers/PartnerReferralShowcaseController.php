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
            ->with([
                'user',
                'program' => fn ($query) => $query->where('status', 'active')->with([
                    'products' => fn ($productQuery) => $productQuery->where('status', 'active')->orderBy('name'),
                    'commissionRules' => fn ($rules) => $rules->where('status', true)->where('event', 'sale')->orderBy('level')->orderByDesc('priority'),
                ]),
            ])
            ->firstOrFail();

        $program = $partner->program;
        abort_unless($program, 404);

        $request->session()->put([
            'referral_program_partner_id' => $partner->id,
            'referral_program_id' => $partner->program_id,
            'referral_created_at' => now()->timestamp,
        ]);

        $products = $program->products;
        $rules = $program->commissionRules;
        $directRule = $rules->firstWhere('level', 1);
        $recruiterRule = $rules->firstWhere('level', 2);

        return view('partner.referral-showcase', [
            'partner' => $partner,
            'program' => $program,
            'products' => $products,
            'directRule' => $directRule,
            'recruiterRule' => $recruiterRule,
            'joinUrl' => route('partner.apply', ['recruiter_code' => $partner->partner_code]),
        ]);
    }
}
