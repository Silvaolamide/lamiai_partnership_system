<?php

namespace App\Services;

use App\Models\ProgramPartner;
use Illuminate\Http\Request;

class ReferralService
{
    /**
     * Validate a referral code and store the partner attribution in session.
     *
     * A referral visit never creates a commission. It only records which
     * active partner should receive credit if the customer later purchases.
     */
    public function processReferralCode(Request $request, $programId = null, $excludeUserId = null)
    {
        $referralCode = trim((string) $request->query('ref'));

        if ($referralCode === '') {
            return false;
        }

        $query = ProgramPartner::query()
            ->where('partner_code', $referralCode)
            ->where('status', 'active')
            ->with(['program', 'user']);

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

        $program = $programPartner->program;

        if (!$program || $program->status !== 'active') {
            return [
                'error' => 'Partnership program is not active',
                'code' => $referralCode,
            ];
        }

        $now = now();

        if ($program->starts_at && $now->lt($program->starts_at)) {
            return [
                'error' => 'Partnership program has not started',
                'code' => $referralCode,
            ];
        }

        if ($program->ends_at && $now->gt($program->ends_at)) {
            return [
                'error' => 'Partnership program has ended',
                'code' => $referralCode,
            ];
        }

        if ($excludeUserId && (int) $programPartner->user_id === (int) $excludeUserId) {
            return [
                'error' => 'You cannot use your own referral code',
                'code' => $referralCode,
            ];
        }

        $this->storeReferral($programPartner->id, $programPartner->program_id);

        return true;
    }

    /**
     * Store partner attribution in the session.
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
     * Return the currently attributed partner/program, or null when the
     * attribution is missing or has expired.
     */
    public function getReferral()
    {
        $programPartnerId = session('referral_program_partner_id');
        $programId = session('referral_program_id');
        $createdAt = session('referral_created_at');

        if (!$programPartnerId || !$programId || !$createdAt) {
            return null;
        }

        $programPartner = ProgramPartner::with('program')->find($programPartnerId);

        if (!$programPartner || $programPartner->program_id != $programId || $programPartner->status !== 'active') {
            $this->clearReferral();
            return null;
        }

        $program = $programPartner->program;

        if (!$program || $program->status !== 'active') {
            $this->clearReferral();
            return null;
        }

        $windowDays = max(0, (int) $program->attribution_window_days);
        $expiresAt = now()->setTimestamp((int) $createdAt)->addDays($windowDays);

        if (now()->greaterThan($expiresAt)) {
            $this->clearReferral();
            return null;
        }

        return [
            'program_partner_id' => $programPartnerId,
            'program_id' => $programId,
        ];
    }

    public function getProgramPartner()
    {
        $referral = $this->getReferral();

        if (!$referral) {
            return null;
        }

        return ProgramPartner::with(['program', 'user'])->find($referral['program_partner_id']);
    }

    public function clearReferral()
    {
        session()->forget([
            'referral_program_partner_id',
            'referral_program_id',
            'referral_created_at',
        ]);
    }

    public function hasReferral()
    {
        return $this->getReferral() !== null;
    }

    public function generateReferralLink(ProgramPartner $programPartner, $baseUrl)
    {
        return $baseUrl . '?ref=' . urlencode($programPartner->partner_code);
    }

    public function generateFullReferralLink(ProgramPartner $programPartner, $productSlug)
    {
        $baseUrl = route('product.show', ['slug' => $productSlug]);
        return $baseUrl . '?ref=' . urlencode($programPartner->partner_code);
    }
}
