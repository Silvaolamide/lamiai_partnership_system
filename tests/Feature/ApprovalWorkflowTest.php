<?php

namespace Tests\Feature;

use App\Models\PartnershipProgram;
use App\Models\PlatformSetting;
use App\Models\ProgramPartner;
use App\Models\User;
use App\Services\PartnerApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function program(bool $businessApproval = false): PartnershipProgram
    {
        return PartnershipProgram::create([
            'owner_id' => User::factory()->create()->id,
            'name' => 'Test Program',
            'slug' => 'test-program-' . uniqid(),
            'status' => 'active',
            'settings' => ['partner_business_approval_required' => $businessApproval],
        ]);
    }

    private function partner(PartnershipProgram $program): ProgramPartner
    {
        return ProgramPartner::create([
            'program_id' => $program->id,
            'user_id' => User::factory()->create(['email_verified_at' => now()])->id,
            'partner_code' => 'PENDING-' . strtoupper(substr(uniqid(), -6)),
            'status' => 'pending',
            'joined_at' => now(),
        ]);
    }

    public function test_email_only_can_activate_partner_when_super_admin_and_business_approval_are_off(): void
    {
        PlatformSetting::setValue('partner_super_admin_approval_required', false);
        $partner = $this->partner($this->program(false));

        app(PartnerApprovalService::class)->sync($partner);

        $this->assertEquals('active', $partner->fresh()->status);
        $this->assertNotNull($partner->fresh()->super_admin_approved_at);
        $this->assertNotNull($partner->fresh()->business_approved_at);
    }

    public function test_super_admin_plus_email_requires_super_admin_approval(): void
    {
        PlatformSetting::setValue('partner_super_admin_approval_required', true);
        $partner = $this->partner($this->program(false));
        $service = app(PartnerApprovalService::class);

        $service->sync($partner);
        $this->assertEquals('pending', $partner->fresh()->status);

        $service->approveBySuperAdmin($partner->fresh());
        $this->assertEquals('active', $partner->fresh()->status);
    }

    public function test_business_plus_email_requires_business_approval_when_super_admin_is_off(): void
    {
        PlatformSetting::setValue('partner_super_admin_approval_required', false);
        $partner = $this->partner($this->program(true));
        $service = app(PartnerApprovalService::class);

        $service->sync($partner);
        $this->assertEquals('pending', $partner->fresh()->status);

        $service->approveByBusiness($partner->fresh());
        $this->assertEquals('active', $partner->fresh()->status);
    }

    public function test_all_three_requirements_can_be_enabled(): void
    {
        PlatformSetting::setValue('partner_super_admin_approval_required', true);
        $partner = $this->partner($this->program(true));
        $service = app(PartnerApprovalService::class);

        $service->sync($partner);
        $this->assertEquals('pending', $partner->fresh()->status);

        $service->approveBySuperAdmin($partner->fresh());
        $this->assertEquals('pending', $partner->fresh()->status);

        $service->approveByBusiness($partner->fresh());
        $this->assertEquals('active', $partner->fresh()->status);
    }

    public function test_business_onboarding_requires_super_admin_approval_in_addition_to_email(): void
    {
        $business = User::factory()->create([
            'email_verified_at' => now(),
            'business_super_admin_approved_at' => null,
        ]);
        $business->assignRole('program_manager');

        $this->actingAs($business)
            ->get(route('business.onboarding', ['step' => 'profile']))
            ->assertRedirect(route('business.pending'));

        $business->update(['business_super_admin_approved_at' => now()]);

        $this->actingAs($business)
            ->get(route('business.onboarding', ['step' => 'profile']))
            ->assertOk();
    }
}
