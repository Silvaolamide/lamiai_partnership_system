<?php

namespace App\Http\Controllers;

use App\Models\Product;
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

        $result = $this->referralService->processReferralCode($request);

        if ($result === true) {
            $referral = $this->referralService->getReferral();
            $referralProgramId = $referral['program_id'] ?? null;

            $programMatchesProduct = $referralProgramId
                && $product->partnershipPrograms->contains('id', $referralProgramId);

            if ($programMatchesProduct) {
                $referralProcessed = true;
                $referringPartner = $this->referralService->getProgramPartner();
            } else {
                $this->referralService->clearReferral();
                $referralError = 'This referral link is not valid for this product.';
            }
        } elseif (is_array($result)) {
            $this->referralService->clearReferral();
            $referralError = $result['error'];
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
