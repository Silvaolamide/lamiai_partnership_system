<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\PlatformSetting;
use App\Models\ProgramPartner;
use App\Models\User;
use App\Services\PartnerApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PlatformChangesTest extends TestCase
{
    use RefreshDatabase;

    private function business(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email_verified_at' => now(),
            'business_name' => 'Acme Business',
            'business_super_admin_approved_at' => now(),
        ], $overrides));
    }

    private function program(User $business, bool $businessApproval = false): PartnershipProgram
    {
        return PartnershipProgram::create([
            'owner_id' => $business->id,
            'name' => 'Acme Affiliate Program',
            'slug' => 'acme-' . uniqid(),
            'status' => 'active',
            'settings' => ['partner_business_approval_required' => $businessApproval],
            'minimum_payout' => 0,
        ]);
    }

    private function partner(PartnershipProgram $program, array $overrides = []): ProgramPartner
    {
        return ProgramPartner::create(array_merge([
            'program_id' => $program->id,
            'user_id' => User::factory()->create(['email_verified_at' => now()])->id,
            'partner_code' => 'PENDING-' . strtoupper(substr(uniqid(), -6)),
            'status' => 'pending',
            'joined_at' => now(),
        ], $overrides));
    }

    private function paidOrder(User $business, ?ProgramPartner $partner = null, float $total = 100000, ?Carbon $paidAt = null): array
    {
        $program = $this->program($business);
        $partner ??= $this->partner($program, ['status' => 'active', 'approved_at' => now()]);

        $rule = CommissionRule::create([
            'program_id' => $program->id,
            'event' => 'sale',
            'level' => 1,
            'commission_type' => 'percentage',
            'value' => 20,
            'status' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(substr(uniqid(), -8)),
            'program_id' => $program->id,
            'partner_id' => $partner->id,
            'subtotal' => $total,
            'discount' => 0,
            'total' => $total,
            'currency' => 'NGN',
            'status' => 'paid',
            'paid_at' => $paidAt ?: now()->subDays(10),
        ]);

        Commission::create([
            'program_id' => $program->id,
            'order_id' => $order->id,
            'partner_id' => $partner->id,
            'rule_id' => $rule->id,
            'level' => 1,
            'commission_type' => 'percentage',
            'rate' => 20,
            'base_amount' => $total,
            'commission_amount' => round($total * .20, 2),
            'status' => 'available',
            'available_at' => now()->subDay(),
        ]);

        return [$program, $partner, $order];
    }

    public function test_admin_settings_are_protected_from_non_super_admins(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->get(route('admin.settings'))->assertForbidden();
    }

    public function test_super_admin_can_update_admin_charge_and_payout_delay(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'partner_super_admin_approval_required' => '1',
                'payout_delay_days' => 14,
                'admin_charge_percent' => '5.5',
            ])
            ->assertRedirect();

        $this->assertSame('5.5', (string) PlatformSetting::getValue('admin_charge_percent'));
        $this->assertSame('14', (string) PlatformSetting::getValue('payout_delay_days'));
        $this->assertTrue((bool) PlatformSetting::getValue('partner_super_admin_approval_required'));
    }

    public function test_admin_charge_is_validated_between_zero_and_one_hundred_percent(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('super_admin');

        foreach ([-0.01, 100.01] as $charge) {
            $this->actingAs($admin)
                ->from(route('admin.settings'))
                ->put(route('admin.settings.update'), [
                    'payout_delay_days' => 7,
                    'admin_charge_percent' => $charge,
                ])
                ->assertSessionHasErrors('admin_charge_percent');
        }
    }

    public function test_email_is_a_required_partner_approval_condition(): void
    {
        PlatformSetting::setValue('partner_super_admin_approval_required', false);
        $business = $this->business();
        $program = $this->program($business, false);
        $partner = $this->partner($program, [
            'user_id' => User::factory()->create(['email_verified_at' => null])->id,
        ]);

        $requirements = app(PartnerApprovalService::class)->requirements($program);

        $this->assertTrue($requirements['email']);
        $this->assertFalse($requirements['super_admin']);
        $this->assertFalse($requirements['business']);
        $this->assertNull($partner->user->email_verified_at);
    }

    public function test_email_verified_partner_can_be_activated_when_optional_approvals_are_off(): void
    {
        PlatformSetting::setValue('partner_super_admin_approval_required', false);
        $business = $this->business();
        $partner = $this->partner($this->program($business, false));

        app(PartnerApprovalService::class)->sync($partner);

        $partner = $partner->fresh();
        $this->assertSame('active', $partner->status);
        $this->assertNotNull($partner->approved_at);
        $this->assertNotNull($partner->super_admin_approved_at);
        $this->assertNotNull($partner->business_approved_at);
    }

    public function test_super_admin_and_email_workflow_requires_super_admin_approval(): void
    {
        PlatformSetting::setValue('partner_super_admin_approval_required', true);
        $business = $this->business();
        $partner = $this->partner($this->program($business, false));
        $service = app(PartnerApprovalService::class);

        $service->sync($partner);
        $this->assertSame('pending', $partner->fresh()->status);

        $service->approveBySuperAdmin($partner->fresh());
        $this->assertSame('active', $partner->fresh()->status);
    }

    public function test_business_and_email_workflow_requires_business_approval(): void
    {
        PlatformSetting::setValue('partner_super_admin_approval_required', false);
        $business = $this->business();
        $partner = $this->partner($this->program($business, true));
        $service = app(PartnerApprovalService::class);

        $service->sync($partner);
        $this->assertSame('pending', $partner->fresh()->status);

        $service->approveByBusiness($partner->fresh());
        $this->assertSame('active', $partner->fresh()->status);
    }

    public function test_all_partner_approval_requirements_can_be_combined(): void
    {
        PlatformSetting::setValue('partner_super_admin_approval_required', true);
        $business = $this->business();
        $partner = $this->partner($this->program($business, true));
        $service = app(PartnerApprovalService::class);

        $service->sync($partner);
        $this->assertSame('pending', $partner->fresh()->status);

        $service->approveBySuperAdmin($partner->fresh());
        $this->assertSame('pending', $partner->fresh()->status);

        $service->approveByBusiness($partner->fresh());
        $this->assertSame('active', $partner->fresh()->status);
    }

    public function test_business_onboarding_requires_email_verification_and_super_admin_approval(): void
    {
        $business = $this->business([
            'email_verified_at' => null,
            'business_super_admin_approved_at' => null,
        ]);
        $business->assignRole('program_manager');

        $this->actingAs($business)
            ->get(route('business.onboarding', ['step' => 'profile']))
            ->assertRedirect();

        $business->update(['email_verified_at' => now()]);
        $business->refresh();

        $this->actingAs($business)
            ->get(route('business.onboarding', ['step' => 'profile']))
            ->assertRedirect(route('business.pending'));

        $business->update(['business_super_admin_approved_at' => now()]);
        $business->refresh();

        $this->actingAs($business)
            ->get(route('business.onboarding', ['step' => 'profile']))
            ->assertOk();
    }

    public function test_admin_charge_is_deducted_from_business_net_payout(): void
    {
        PlatformSetting::setValue('payout_delay_days', 0);
        PlatformSetting::setValue('admin_charge_percent', 5);

        $business = $this->business();
        [, , $order] = $this->paidOrder($business, null, 100000, now()->subDay());

        $this->actingAs($business)
            ->post(route('business.payouts.store'), [
                'order_ids' => [$order->id],
                'method' => 'bank_transfer',
            ])
            ->assertRedirect(route('business.payouts.index'));

        $this->assertDatabaseHas('business_payouts', [
            'business_id' => $business->id,
            'amount' => 75000,
            'status' => 'requested',
        ]);
    }

    public function test_zero_admin_charge_preserves_business_net_payout(): void
    {
        PlatformSetting::setValue('payout_delay_days', 0);
        PlatformSetting::setValue('admin_charge_percent', 0);

        $business = $this->business();
        [, , $order] = $this->paidOrder($business, null, 100000, now()->subDay());

        $this->actingAs($business)->post(route('business.payouts.store'), [
            'order_ids' => [$order->id],
            'method' => 'bank_transfer',
        ])->assertRedirect();

        $this->assertDatabaseHas('business_payouts', ['amount' => 80000]);
    }

    public function test_admin_charge_is_clamped_to_one_hundred_percent_for_payout_calculation(): void
    {
        PlatformSetting::setValue('payout_delay_days', 0);
        PlatformSetting::setValue('admin_charge_percent', 125);

        $business = $this->business();
        [, , $order] = $this->paidOrder($business, null, 100000, now()->subDay());

        $this->actingAs($business)
            ->post(route('business.payouts.store'), [
                'order_ids' => [$order->id],
                'method' => 'bank_transfer',
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('business_payouts', 0);
    }

    public function test_admin_charge_is_applied_after_all_partner_commissions(): void
    {
        PlatformSetting::setValue('payout_delay_days', 0);
        PlatformSetting::setValue('admin_charge_percent', 10);

        $business = $this->business();
        $program = $this->program($business);
        $partnerUser = User::factory()->create(['email_verified_at' => now()]);
        $partner = ProgramPartner::create([
            'program_id' => $program->id,
            'user_id' => $partnerUser->id,
            'partner_code' => 'LAMI-TEST01',
            'status' => 'active',
            'joined_at' => now(),
            'approved_at' => now(),
        ]);

        $rule = CommissionRule::create([
            'program_id' => $program->id,
            'event' => 'sale',
            'level' => 1,
            'commission_type' => 'percentage',
            'value' => 20,
            'status' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-ADMIN-CHARGE',
            'program_id' => $program->id,
            'partner_id' => $partner->id,
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'currency' => 'NGN',
            'status' => 'paid',
            'paid_at' => now()->subDay(),
        ]);

        Commission::create([
            'program_id' => $program->id,
            'order_id' => $order->id,
            'partner_id' => $partner->id,
            'rule_id' => $rule->id,
            'level' => 1,
            'commission_type' => 'percentage',
            'rate' => 20,
            'base_amount' => 100000,
            'commission_amount' => 20000,
            'status' => 'available',
            'available_at' => now()->subHour(),
        ]);

        $this->actingAs($business)->post(route('business.payouts.store'), [
            'order_ids' => [$order->id],
            'method' => 'bank_transfer',
        ])->assertRedirect();

        $this->assertDatabaseHas('business_payouts', [
            'business_id' => $business->id,
            'amount' => 70000,
        ]);
    }

    public function test_business_payout_cannot_be_requested_twice_for_the_same_sale(): void
    {
        PlatformSetting::setValue('payout_delay_days', 0);
        PlatformSetting::setValue('admin_charge_percent', 5);

        $business = $this->business();
        [, , $order] = $this->paidOrder($business, null, 100000, now()->subDay());

        $payload = ['order_ids' => [$order->id], 'method' => 'bank_transfer'];
        $this->actingAs($business)->post(route('business.payouts.store'), $payload)->assertRedirect();
        $this->actingAs($business)->post(route('business.payouts.store'), $payload)->assertStatus(422);

        $this->assertDatabaseCount('business_payouts', 1);
    }

    public function test_business_payout_respects_the_configured_holding_period_with_admin_charge(): void
    {
        PlatformSetting::setValue('payout_delay_days', 7);
        PlatformSetting::setValue('admin_charge_percent', 5);

        $business = $this->business();
        [, , $order] = $this->paidOrder($business, null, 100000, Carbon::now()->subDays(2));

        $this->actingAs($business)
            ->post(route('business.payouts.store'), [
                'order_ids' => [$order->id],
                'method' => 'bank_transfer',
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('business_payouts', 0);
    }
}
