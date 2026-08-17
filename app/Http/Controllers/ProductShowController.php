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

    /**
     * Display a product with referral code handling.
     * 
     * @param Request $request
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        // Process referral code from URL
        $referralProcessed = false;
        $referralError = null;
        $referringPartner = null;

        $result = $this->referralService->processReferralCode($request);
        
        if ($result === true) {
            $referralProcessed = true;
            $referringPartner = $this->referralService->getProgramPartner();
        } elseif (is_array($result)) {
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
