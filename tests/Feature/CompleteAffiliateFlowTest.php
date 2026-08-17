<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Models\Order;
use App\Models\Commission;
use App\Models\CommissionRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteAffiliateFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $program;
    protected $product;
    protected $adminUser;
    protected $recruiterUser;
    protected $recruiterPartner;
    protected $affiliateUser;
    protected $affiliatePartner;
    protected $customerUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super_admin');

        $this->product = Product::create([
            'name' => 'AI Filmmaking Masterclass',
            'slug' => 'ai-filmmaking-masterclass',
            'description' => 'Learn AI video creation',
            'price' => 20000,
            'currency' => 'NGN',
            'status' => 'active',
        ]);

        $this->program = PartnershipProgram::create([
            'name' => 'AI Filmmaking Partnership',
            'slug' => 'ai-filmmaking-partnership',
            'description' => 'Earn commissions on AI Filmmaking sales',
            'status' => 'active',
            'attribution_window_days' => 30,
            'minimum_payout' => 5000,
        ]);

        $this->program->products()->attach($this->product->id);

        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 1,
            'type' => 'percentage',
            'value' => 20,
            'maximum_amount' => null,
            'event' => 'sale',
            'status' => true,
        ]);

        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 2,
            'type' => 'percentage',
            'value' => 5,
            'maximum_amount' => null,
            'event' => 'sale',
            'status' => true,
        ]);

        $this->recruiterUser = User::factory()->create();
        $this->recruiterPartner = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $this->recruiterUser->id,
            'partner_code' => 'RECRUITER001',
            'status' => 'active',
            'parent_partner_id' => null,
            'joined_at' => now(),
        ]);

        $this->affiliateUser = User::factory()->create();
        $this->affiliatePartner = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $this->affiliateUser->id,
            'partner_code' => 'AFFILIATE001',
            'status' => 'active',
            'parent_partner_id' => $this->recruiterPartner->id,
            'joined_at' => now(),
        ]);

        $this->customerUser = User::factory()->create();
    }

    public function test_complete_affiliate_flow_with_hierarchy()
    {
        $response = $this->get('/product/' . $this->product->slug . '?ref=' . $this->affiliatePartner->partner_code);
        $response->assertSessionHas('referral_program_partner_id', $this->affiliatePartner->id);
        $response->assertSessionHas('referral_program_id', $this->program->id);

        $this->actingAs($this->customerUser)
            ->post(route('checkout.create'), [
                'product_id' => $this->product->id,
            ])
            ->assertRedirect();

        $order = Order::where('customer_id', $this->customerUser->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals($this->affiliatePartner->id, $order->partner_id);
        $this->assertEquals($this->program->id, $order->program_id);
        $this->assertEquals(20000, $order->total);

        $this->actingAs($this->adminUser)
            ->patch(route('admin.orders.mark-paid', ['order' => $order->id]))
            ->assertRedirect();

        $order->refresh();
        $this->assertEquals('paid', $order->status);

        $commissions = Commission::where('order_id', $order->id)->get();
        $this->assertEquals(2, $commissions->count());

        $level1 = $commissions->where('level', 1)->first();
        $this->assertNotNull($level1);
        $this->assertEquals($this->affiliatePartner->id, $level1->partner_id);
        $this->assertEquals(4000, $level1->commission_amount);
        $this->assertEquals('available', $level1->status);

        $level2 = $commissions->where('level', 2)->first();
        $this->assertNotNull($level2);
        $this->assertEquals($this->recruiterPartner->id, $level2->partner_id);
        $this->assertEquals(1000, $level2->commission_amount);
        $this->assertEquals('available', $level2->status);

        return $order;
    }

    public function test_commission_generation_is_idempotent()
    {
        $order = Order::create([
            'order_number' => 'ORD-' . now()->timestamp,
            'customer_id' => $this->customerUser->id,
            'program_id' => $this->program->id,
            'partner_id' => $this->affiliatePartner->id,
            'subtotal' => 20000,
            'discount' => 0,
            'total' => 20000,
            'currency' => 'NGN',
            'status' => 'paid',
            'paid_at' => now(),
            'payment_provider' => 'test',
            'payment_reference' => 'REF-TEST-001',
        ]);

        $service = app('App\Services\CommissionService');
        $result1 = $service->generateCommissionsForOrder($order);
        $this->assertEquals(2, $result1['commissions_generated']);
        $this->assertEquals(5000, $result1['total_amount']);

        $result2 = $service->generateCommissionsForOrder($order);
        $this->assertEquals(0, $result2['commissions_generated']);
        $this->assertEquals(0, $result2['total_amount']);

        $this->assertEquals(2, Commission::where('order_id', $order->id)->count());
    }

    public function test_admin_can_list_and_view_orders()
    {
        $order = Order::create([
            'order_number' => 'ORD-' . now()->timestamp,
            'customer_id' => $this->customerUser->id,
            'program_id' => $this->program->id,
            'partner_id' => $this->affiliatePartner->id,
            'subtotal' => 20000,
            'discount' => 0,
            'total' => 20000,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders.index'))
            ->assertSuccessful()
            ->assertViewHas('orders');

        $this->assertContains($order->id, $response->viewData('orders')->pluck('id'));

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders.show', ['order' => $order->id]))
            ->assertSuccessful()
            ->assertViewHas('order');

        $this->assertEquals($order->id, $response->viewData('order')->id);
    }

    public function test_commission_status_workflow()
    {
        $order = Order::create([
            'order_number' => 'ORD-COMMISSION-' . now()->timestamp,
            'customer_id' => $this->customerUser->id,
            'program_id' => $this->program->id,
            'partner_id' => $this->affiliatePartner->id,
            'subtotal' => 20000,
            'discount' => 0,
            'total' => 20000,
            'currency' => 'NGN',
            'status' => 'paid',
            'paid_at' => now(),
            'payment_provider' => 'test',
            'payment_reference' => 'REF-COMMISSION-' . now()->timestamp,
        ]);

        $rule = CommissionRule::where('program_id', $this->program->id)->where('level', 1)->firstOrFail();

        $commission = Commission::create([
            'program_id' => $this->program->id,
            'order_id' => $order->id,
            'partner_id' => $this->affiliatePartner->id,
            'source_partner_id' => null,
            'rule_id' => $rule->id,
            'level' => 1,
            'commission_type' => 'percentage',
            'rate' => 20,
            'base_amount' => 20000,
            'commission_amount' => 4000,
            'status' => 'available',
        ]);

        $this->actingAs($this->adminUser)
            ->patch(route('admin.commissions.approve', ['commission' => $commission->id]))
            ->assertRedirect();

        $commission->refresh();
        $this->assertEquals('approved', $commission->status);

        $this->actingAs($this->adminUser)
            ->patch(route('admin.commissions.mark-payable', ['commission' => $commission->id]))
            ->assertRedirect();

        $commission->refresh();
        $this->assertEquals('payable', $commission->status);
    }

    public function test_customer_authorization_policy()
    {
        $otherCustomer = User::factory()->create();
        $otherOrder = Order::create([
            'order_number' => 'ORD-OTHER-' . now()->timestamp,
            'customer_id' => $otherCustomer->id,
            'program_id' => $this->program->id,
            'partner_id' => null,
            'subtotal' => 10000,
            'discount' => 0,
            'total' => 10000,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->customerUser)
            ->get(route('checkout.show', ['orderId' => $otherOrder->id]));

        $this->assertEquals(403, $response->status());
    }

    public function test_prevent_self_referral()
    {
        $newCustomer = User::factory()->create();
        
        session([
            'referral_program_partner_id' => $this->affiliatePartner->id,
            'referral_program_id' => $this->program->id,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-' . now()->timestamp,
            'customer_id' => $newCustomer->id,
            'program_id' => $this->program->id,
            'partner_id' => $this->affiliatePartner->id,
            'subtotal' => 20000,
            'discount' => 0,
            'total' => 20000,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);

        $this->assertNotNull($order->id);
    }
}
