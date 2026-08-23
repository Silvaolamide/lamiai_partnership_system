<?php

namespace App\Services;

use App\Models\PartnershipProgram;
use App\Models\PlatformSetting;
use App\Models\ProgramPartner;
use Illuminate\Support\Str;

class PartnerApprovalService
{
    /** @deprecated Kept for compatibility with existing admin/settings code. */
    public function superAdminApprovalRequired(): bool
    {
        return (bool) PlatformSetting::getValue('partner_super_admin_approval_required', true);
    }

    public function businessApprovalRequired(PartnershipProgram $program): bool
    {
        return (bool) ($program->settings['partner_business_approval_required'] ?? false);
    }

    /**
     * Program membership is adoption-first. Manual approval is opt-in per program.
     * Email verification is enforced by the verified middleware before partner access.
     */
    public function programEnrollmentRequirements(PartnershipProgram $program): array
    {
        return [
            'email' => true,
            'super_admin' => (bool) ($program->settings['partner_super_admin_approval_required'] ?? false),
            'business' => $this->businessApprovalRequired($program),
        ];
    }

    public function requirements(PartnershipProgram $program): array
    {
        return $this->programEnrollmentRequirements($program);
    }

    public function sync(ProgramPartner $partner): ProgramPartner
    {
        return $this->syncWithRequirements($partner, $this->requirementsForPartner($partner));
    }

    public function syncProgramEnrollment(ProgramPartner $partner): ProgramPartner
    {
        return $this->syncWithRequirements($partner, $this->programEnrollmentRequirements($partner->program));
    }

    public function requirementsForPartner(ProgramPartner $partner): array
    {
        return $this->programEnrollmentRequirements($partner->program);
    }

    public function approvalSummary(ProgramPartner $partner): array
    {
        $requirements = $this->requirementsForPartner($partner);

        return [
            'requires_super_admin' => $requirements['super_admin'],
            'requires_business' => $requirements['business'],
            'pending_super_admin' => $requirements['super_admin'] && !$partner->super_admin_approved_at,
            'pending_business' => $requirements['business'] && !$partner->business_approved_at,
            'approver' => $requirements['business'] && $requirements['super_admin']
                ? 'Program owner and Super Admin'
                : ($requirements['business'] ? 'Program owner' : ($requirements['super_admin'] ? 'Super Admin' : null)),
        ];
    }

    private function syncWithRequirements(ProgramPartner $partner, array $requirements): ProgramPartner
    {
        if ($partner->status === 'rejected') {
            return $partner;
        }

        if (!$requirements['super_admin'] && !$partner->super_admin_approved_at) {
            $partner->forceFill(['super_admin_approved_at' => now()])->save();
        }

        if (!$requirements['business'] && !$partner->business_approved_at) {
            $partner->forceFill(['business_approved_at' => now()])->save();
        }

        $ready = (!$requirements['super_admin'] || $partner->super_admin_approved_at)
            && (!$requirements['business'] || $partner->business_approved_at);

        if ($ready && $partner->status !== 'active') {
            $partner->forceFill([
                'status' => 'active',
                'partner_code' => str_starts_with((string) $partner->partner_code, 'PENDING-')
                    ? $this->generatePartnerCode()
                    : $partner->partner_code,
                'approved_at' => $partner->approved_at ?: now(),
            ])->save();
        } elseif (!$ready && $partner->status === 'active') {
            $partner->forceFill(['status' => 'pending'])->save();
        }

        return $partner->refresh();
    }

    public function approveBySuperAdmin(ProgramPartner $partner): ProgramPartner
    {
        if ($partner->status === 'rejected') return $partner;
        $partner->forceFill(['super_admin_approved_at' => now()])->save();
        return $this->syncWithRequirements($partner->refresh(), $this->requirementsForPartner($partner));
    }

    public function approveByBusiness(ProgramPartner $partner): ProgramPartner
    {
        if ($partner->status === 'rejected') return $partner;
        $partner->forceFill(['business_approved_at' => now()])->save();
        return $this->syncWithRequirements($partner->refresh(), $this->requirementsForPartner($partner));
    }

    private function generatePartnerCode(): string
    {
        do {
            $code = 'LAMI-' . Str::upper(Str::random(8));
        } while (ProgramPartner::where('partner_code', $code)->exists());
        return $code;
    }
}
