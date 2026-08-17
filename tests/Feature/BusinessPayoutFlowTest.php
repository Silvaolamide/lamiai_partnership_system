<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\PlatformSetting;
use App\Models\ProgramPartner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BusinessPayoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private function business(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'business_name' => 'Acme Business',
            'business_super_admin_approved_at' => now(),
        ]);
    }

    public function test_business_can_request_net_sale_payout_after_holding_period(): void
    {
        PlatformSetting::setValue('payout_delay_days', 0);

        $business = $this->business();
        $partnerUser = User::factory()->create(['email_verified_at' => now()]);
        $program = PartnershipProgram::create([
            'owner_id' => $business->id,
            'name' => 'Acme Affiliate Program',
            'slug' => 'acme-affiliate-program',
            'status' => 'active',
            'minimum_payout' => 0,
        ]);
        $partner = ProgramPartner::create([
            'program_id' => $program->id,
            'user_id' => $partnerUser->id,
            'partner_code' => 'ACME001',
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
            'order_number' => 'ORD-BP-001',
            'program_id' => $program->id,
            'partner_id' => $partner->id,
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'currency' => 'NGN',
            'status' => 'paid',
            'paid_at' => now()->subHour(),
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
            'available_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($business)->post(route('business.payouts.store'), [
            'order_ids' => [$order->id],
            'method' => 'bank_transfer',
        ]);

        $response->assertRedirect(route('business.payouts.index'));
        $this->assertDatabaseHas('business_payouts', [
            'business_id' => $business->id,
            'amount' => 80000,
            'status' => 'requested',
        ]);
        $this->assertNotNull(Order::find($order->id)->business_payout_id);
    }

    public function test_business_cannot_request_sale_before_holding_period_expires(): void
    {
        PlatformSetting::setValue('payout_delay_days', 7);

        $business = $this->business();
        $program = PartnershipProgram::create([
            'owner_id' => $business->id,
            'name' => 'Acme Direct Program',
            'slug' => 'acme-direct-program',
            'status' => 'active',
        ]);
        $order = Order::create([
            'order_number' => 'ORD-BP-002',
            'program_id' => $program->id,
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'currency' => 'NGN',
            'status' => 'paid',
            'paid_at' => Carbon::now()->subDays(2),
        ]);

        $response = $this->actingAs($business)->post(route('business.payouts.store'), [
            'order_ids' => [$order->id],
            'method' => 'bank_transfer',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('business_payouts', 0);
    }

    public function test_rejected_business_payout_releases_sales_back_to_balance(): void
    {
        PlatformSetting::setValue('payout_delay_days', 0);

        $business = $this->business();
        $program = PartnershipProgram::create([
            'owner_id' => $business->id,
            'name' => 'Acme Release Program',
            'slug' => 'acme-release-program',
            'status' => 'active',
        ]);
        $order = Order::create([
            'order_number' => 'ORD-BP-003',
            'program_id' => $program->id,
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'currency' => 'NGN',
            'status' => 'paid',
            'paid_at' => now()->subHour(),
        ]);

        $this->actingAs($business)->post(route('business.payouts.store'), [
            'order_ids' => [$order->id],
            'method' => 'bank_transfer',
        ])->assertRedirect();

        $payout = \App\Models\BusinessPayout::firstOrFail();
        $this->assertNotNull(Order::find($order->id)->business_payout_id);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->patch(route('admin.business-payouts.reject', $payout))
            ->assertRedirect();

        $this->assertDatabaseHas('business_payouts', ['id' => $payout->id, 'status' => 'rejected']);
        $this->assertNull(Order::find($order->id)->business_payout_id);
    }
}
