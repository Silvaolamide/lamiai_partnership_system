<?php

namespace App\Services;

use App\Models\PartnershipProgram;
use App\Models\PlatformSetting;
use App\Models\ProgramPartner;
use Illuminate\Support\Str;

class PartnerApprovalService
{
    public function superAdminApprovalRequired(): bool
    {
        return (bool) PlatformSetting::getValue('partner_super_admin_approval_required', true);
    }

    public function businessApprovalRequired(PartnershipProgram $program): bool
    {
        return (bool) ($program->settings['partner_business_approval_required'] ?? false);
    }

    public function requirements(PartnershipProgram $program): array
    {
        return [
            'email' => true,
            'super_admin' => $this->superAdminApprovalRequired(),
            'business' => $this->businessApprovalRequired($program),
        ];
    }

    public function sync(ProgramPartner $partner): ProgramPartner
    {
        if ($partner->status === 'rejected') {
            return $partner;
        }

        $requirements = $this->requirements($partner->program);

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
        if ($partner->status === 'rejected') {
            return $partner;
        }

        $partner->forceFill(['super_admin_approved_at' => now()])->save();
        return $this->sync($partner->refresh());
    }

    public function approveByBusiness(ProgramPartner $partner): ProgramPartner
    {
        if ($partner->status === 'rejected') {
            return $partner;
        }

        $partner->forceFill(['business_approved_at' => now()])->save();
        return $this->sync($partner->refresh());
    }

    private function generatePartnerCode(): string
    {
        do {
            $code = 'LAMI-' . Str::upper(Str::random(8));
        } while (ProgramPartner::where('partner_code', $code)->exists());

        return $code;
    }
}
