<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProgramPartner;
use App\Services\ReferralService;
use Illuminate\Http\Request;

class ProductShowController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function show(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->with(['partnershipPrograms' => function ($query) {
                $query->where('status', 'active');
            }])
            ->firstOrFail();

        $referralProcessed = false;
        $referralError = null;
        $referringPartner = null;

        $referralCode = trim((string) $request->query('ref'));

        if ($referralCode !== '') {
            // A partner store can contain products from several programs. The
            // public store URL identifies one of the partner's program-partner
            // records, so for a product belonging to another subscribed program
            // resolve the same user to that program's active partner record.
            $originPartner = ProgramPartner::query()
                ->where('partner_code', $referralCode)
                ->where('status', 'active')
                ->first();

            if ($originPartner) {
                $matchingProgramPartner = ProgramPartner::query()
                    ->where('user_id', $originPartner->user_id)
                    ->where('status', 'active')
                    ->whereIn('program_id', $product->partnershipPrograms->pluck('id'))
                    ->with(['program', 'user'])
                    ->first();

                if ($matchingProgramPartner) {
                    $this->referralService->storeReferral(
                        $matchingProgramPartner->id,
                        $matchingProgramPartner->program_id
                    );
                    $referralProcessed = true;
                    $referringPartner = $matchingProgramPartner;
                } else {
                    $this->referralService->clearReferral();
                    $referralError = 'This referral link is not valid for this product.';
                }
            } else {
                $this->referralService->clearReferral();
                $referralError = 'Invalid or inactive referral code.';
            }
        } elseif ($this->referralService->hasReferral()) {
            // Keep an existing attribution only when its program is applicable
            // to the product being viewed.
            $referral = $this->referralService->getReferral();
            $referralProgramId = $referral['program_id'] ?? null;

            if ($referralProgramId && $product->partnershipPrograms->contains('id', $referralProgramId)) {
                $referralProcessed = true;
                $referringPartner = $this->referralService->getProgramPartner();
            } else {
                $this->referralService->clearReferral();
            }
        }

        $landingPages = config('landing_pages', []);
        $selectedLandingPage = data_get($product->metadata, 'landing_page', 'classic');

        if (! isset($landingPages[$selectedLandingPage])) {
            $selectedLandingPage = 'classic';
        }

        $view = $landingPages[$selectedLandingPage]['view'] ?? 'products.show';

        return view($view, [
            'product' => $product,
            'referralProcessed' => $referralProcessed,
            'referralError' => $referralError,
            'referringPartner' => $referringPartner,
            'referralLink' => $request->query('ref'),
        ]);
    }
}
