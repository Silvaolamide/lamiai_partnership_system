<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Product;
use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Models\Order;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $commissionService;
    protected $program;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->commissionService = app(CommissionService::class);

        // Create product
        $this->product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test',
            'price' => 100000,
            'currency' => 'NGN',
            'status' => 'active',
        ]);

        // Create partnership program
        $this->program = PartnershipProgram::create([
            'name' => 'Test Program',
            'slug' => 'test-program',
            'description' => 'Test',
            'status' => 'active',
            'attribution_window_days' => 30,
            'minimum_payout' => 5000,
        ]);

        $this->program->products()->attach($this->product->id);
    }

    /**
     * Test Level 1 commission calculation
     */
    public function test_level_one_commission_calculation()
    {
        // Create 20% Level 1 commission rule
        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 1,
            'type' => 'percentage',
            'value' => 20,
            'maximum_amount' => null,
            'event' => 'sale',
            'status' => true,
        ]);

        // Create partner
        $partner = User::factory()->create();
        $programPartner = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $partner->id,
            'partner_code' => 'PARTNER001',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Create paid order
        $customer = User::factory()->create();
        $order = Order::create([
            'order_number' => 'ORD-001',
            'customer_id' => $customer->id,
            'program_id' => $this->program->id,
            'partner_id' => $programPartner->id,
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'currency' => 'NGN',
            'status' => 'paid',
            'paid_at' => now(),
            'payment_provider' => 'test',
            'payment_reference' => 'REF-001',
        ]);

        // Generate commissions
        $result = $this->commissionService->generateCommissionsForOrder($order);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['commissions_generated']); // Only level 1
        $this->assertEquals(20000, $result['total_amount']); // 20% of 100000

        // Verify commission record
        $commission = Commission::where('order_id', $order->id)
            ->where('level', 1)
            ->first();

        $this->assertNotNull($commission);
        $this->assertEquals($programPartner->id, $commission->partner_id);
        $this->assertEquals(20000, $commission->commission_amount);
        $this->assertEquals(20, $commission->rate);
        $this->assertEquals(100000, $commission->base_amount);
        $this->assertEquals('available', $commission->status);
    }

    /**
     * Test multi-level commission hierarchy
     */
    public function test_multi_level_commission_hierarchy()
    {
        // Create commission rules
        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 1,
            'type' => 'percentage',
            'value' => 20,
            'event' => 'sale',
            'status' => true,
        ]);

        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 2,
            'type' => 'percentage',
            'value' => 5,
            'event' => 'sale',
            'status' => true,
        ]);

        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 3,
            'type' => 'percentage',
            'value' => 2,
            'event' => 'sale',
            'status' => true,
        ]);

        // Create partner hierarchy
        $level3Partner = User::factory()->create();
        $level3 = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $level3Partner->id,
            'partner_code' => 'L3',
            'status' => 'active',
            'parent_partner_id' => null,
            'joined_at' => now(),
        ]);

        $level2Partner = User::factory()->create();
        $level2 = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $level2Partner->id,
            'partner_code' => 'L2',
            'status' => 'active',
            'parent_partner_id' => $level3->id,
            'joined_at' => now(),
        ]);

        $level1Partner = User::factory()->create();
        $level1 = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $level1Partner->id,
            'partner_code' => 'L1',
            'status' => 'active',
            'parent_partner_id' => $level2->id,
            'joined_at' => now(),
        ]);

        // Create order through level 1 partner
        $customer = User::factory()->create();
        $order = Order::create([
            'order_number' => 'ORD-HIERARCHY',
            'customer_id' => $customer->id,
            'program_id' => $this->program->id,
            'partner_id' => $level1->id,
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'currency' => 'NGN',
            'status' => 'paid',
            'paid_at' => now(),
            'payment_provider' => 'test',
            'payment_reference' => 'REF-HIERARCHY',
        ]);

        // Generate commissions
        $result = $this->commissionService->generateCommissionsForOrder($order);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['commissions_generated']);
        $this->assertEquals(27000, $result['total_amount']); // 20000 + 5000 + 2000

        // Verify each level
        $commissions = Commission::where('order_id', $order->id)
            ->orderBy('level')
            ->get();

        // Level 1: 20% = 20000
        $this->assertEquals(20000, $commissions[0]->commission_amount);
        $this->assertEquals($level1->id, $commissions[0]->partner_id);

        // Level 2: 5% = 5000
        $this->assertEquals(5000, $commissions[1]->commission_amount);
        $this->assertEquals($level2->id, $commissions[1]->partner_id);

        // Level 3: 2% = 2000
        $this->assertEquals(2000, $commissions[2]->commission_amount);
        $this->assertEquals($level3->id, $commissions[2]->partner_id);
    }

    /**
     * Test commission with maximum cap
     */
    public function test_commission_maximum_cap()
    {
        // Create rule with 20% but max 15000
        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 1,
            'type' => 'percentage',
            'value' => 20,
            'maximum_amount' => 15000,
            'event' => 'sale',
            'status' => true,
        ]);

        $partner = User::factory()->create();
        $programPartner = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $partner->id,
            'partner_code' => 'CAPPED',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $customer = User::factory()->create();
        $order = Order::create([
            'order_number' => 'ORD-CAPPED',
            'customer_id' => $customer->id,
            'program_id' => $this->program->id,
            'partner_id' => $programPartner->id,
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'currency' => 'NGN',
            'status' => 'paid',
            'paid_at' => now(),
            'payment_provider' => 'test',
            'payment_reference' => 'REF-CAPPED',
        ]);

        $result = $this->commissionService->generateCommissionsForOrder($order);

        $commission = Commission::where('order_id', $order->id)->first();

        // Should be capped at 15000 instead of 20000 (20%)
        $this->assertEquals(15000, $commission->commission_amount);
    }

    /**
     * Test commission statistics
     */
    public function test_commission_statistics()
    {
        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 1,
            'type' => 'percentage',
            'value' => 20,
            'event' => 'sale',
            'status' => true,
        ]);

        $partner = User::factory()->create();
        $programPartner = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $partner->id,
            'partner_code' => 'STATS',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Create multiple commissions with different statuses
        Commission::create([
            'program_id' => $this->program->id,
            'order_id' => 1,
            'partner_id' => $programPartner->id,
            'rule_id' => 1,
            'level' => 1,
            'commission_type' => 'percentage',
            'rate' => 20,
            'base_amount' => 100000,
            'commission_amount' => 20000,
            'status' => 'available',
        ]);

        Commission::create([
            'program_id' => $this->program->id,
            'order_id' => 2,
            'partner_id' => $programPartner->id,
            'rule_id' => 1,
            'level' => 1,
            'commission_type' => 'percentage',
            'rate' => 20,
            'base_amount' => 50000,
            'commission_amount' => 10000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        Commission::create([
            'program_id' => $this->program->id,
            'order_id' => 3,
            'partner_id' => $programPartner->id,
            'rule_id' => 1,
            'level' => 1,
            'commission_type' => 'percentage',
            'rate' => 20,
            'base_amount' => 30000,
            'commission_amount' => 6000,
            'status' => 'reversed',
            'reversed_at' => now(),
        ]);

        $stats = $this->commissionService->getCommissionStats($programPartner);

        $this->assertEquals(20000, $stats['available']);
        $this->assertEquals(10000, $stats['paid']);
        $this->assertEquals(6000, $stats['reversed']);
        $this->assertEquals(36000, $stats['total']);
    }
}
