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
                // A valid partner code can still be invalid for this product.
                // Do not leave the previous attribution in the customer's session.
                $this->referralService->clearReferral();
                $referralError = 'This referral link is not valid for this product.';
            }
        } elseif (is_array($result)) {
            // An explicitly supplied but invalid/inactive referral code must
            // replace, not preserve, any previous referral attribution.
            $this->referralService->clearReferral();
            $referralError = $result['error'];
        }

        return view('products.show', [
            'product' => $product,
            'referralProcessed' => $referralProcessed,
            'referralError' => $referralError,
            'referringPartner' => $referringPartner,
            'referralLink' => $request->query('ref'),
        ]);
    }
}
