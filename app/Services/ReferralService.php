<?php

namespace App\Services;

use App\Models\ProgramPartner;
use Illuminate\Http\Request;

class ReferralService
{
    /**
     * Validate and store referral in session.
     * 
     * Validates that the partner code exists, belongs to an active partnership program,
     * and the partner is active. Stores the program_partner ID in session for later retrieval.
     * 
     * @param Request $request
     * @param int $programId Optional: specific program to validate against
     * @param int $excludeUserId Optional: user ID to exclude from validation (to prevent self-referral)
     * @return bool|array Returns true if valid, or array with error details if invalid
     */
    public function processReferralCode(Request $request, $programId = null, $excludeUserId = null)
    {
        $referralCode = $request->query('ref');
        
        if (!$referralCode) {
            return false;
        }

        $query = ProgramPartner::where('partner_code', $referralCode)
            ->where('status', 'active')
            ->with('program', 'user');

        if ($programId) {
            $query->where('program_id', $programId);
        }

        $programPartner = $query->first();

        if (!$programPartner) {
            return [
                'error' => 'Invalid or inactive referral code',
                'code' => $referralCode,
            ];
        }

        // Verify the partner's program is active
        if ($programPartner->program->status !== 'active') {
            return [
                'error' => 'Partnership program is not active',
                'code' => $referralCode,
            ];
        }

        // Prevent self-referral
        if ($excludeUserId && $programPartner->user_id === $excludeUserId) {
            return [
                'error' => 'You cannot use your own referral code',
                'code' => $referralCode,
            ];
        }

        // Store in session
        $this->storeReferral($programPartner->id, $programPartner->program_id);

        return true;
    }

    /**
     * Store referral in session.
     * 
     * @param int $programPartnerId
     * @param int $programId
     * @return void
     */
    public function storeReferral($programPartnerId, $programId)
    {
        session([
            'referral_program_partner_id' => $programPartnerId,
            'referral_program_id' => $programId,
            'referral_created_at' => now()->timestamp,
        ]);
    }

    /**
     * Retrieve stored referral from session.
     * 
     * @return array|null Returns array with program_partner_id and program_id, or null if not found
     */
    public function getReferral()
    {
        $programPartnerId = session('referral_program_partner_id');
        $programId = session('referral_program_id');

        if (!$programPartnerId || !$programId) {
            return null;
        }

        return [
            'program_partner_id' => $programPartnerId,
            'program_id' => $programId,
        ];
    }

    /**
     * Get the program partner from stored referral.
     * 
     * @return ProgramPartner|null
     */
    public function getProgramPartner()
    {
        $referral = $this->getReferral();

        if (!$referral) {
            return null;
        }

        return ProgramPartner::find($referral['program_partner_id']);
    }

    /**
     * Clear referral from session.
     * 
     * @return void
     */
    public function clearReferral()
    {
        session()->forget([
            'referral_program_partner_id',
            'referral_program_id',
            'referral_created_at',
        ]);
    }

    /**
     * Check if there is a valid stored referral.
     * 
     * @return bool
     */
    public function hasReferral()
    {
        return session()->has('referral_program_partner_id') 
            && session()->has('referral_program_id');
    }

    /**
     * Generate referral link for a partner.
     * 
     * @param ProgramPartner $programPartner
     * @param string $baseUrl The base URL of the product page (e.g., '/ai-video-creation')
     * @return string
     */
    public function generateReferralLink(ProgramPartner $programPartner, $baseUrl)
    {
        return $baseUrl . '?ref=' . $programPartner->partner_code;
    }

    /**
     * Generate full URL for referral link.
     * 
     * @param ProgramPartner $programPartner
     * @param string $productSlug The product slug
     * @return string
     */
    public function generateFullReferralLink(ProgramPartner $programPartner, $productSlug)
    {
        $baseUrl = route('product.show', ['slug' => $productSlug]);
        return $baseUrl . '?ref=' . $programPartner->partner_code;
    }
}
