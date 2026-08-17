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

        $this->product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test',
            'price' => 100000,
            'currency' => 'NGN',
            'status' => 'active',
        ]);

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

    public function test_level_one_commission_calculation()
    {
        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 1,
            'commission_type' => 'percentage',
            'value' => 20,
            'maximum_amount' => null,
            'event' => 'sale',
            'status' => true,
        ]);

        $partner = User::factory()->create();
        $programPartner = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $partner->id,
            'partner_code' => 'PARTNER001',
            'status' => 'active',
            'joined_at' => now(),
        ]);

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

        $result = $this->commissionService->generateCommissionsForOrder($order);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['commissions_generated']);
        $this->assertEquals(20000, $result['total_amount']);

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

    public function test_multi_level_commission_hierarchy()
    {
        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 1,
            'commission_type' => 'percentage',
            'value' => 20,
            'event' => 'sale',
            'status' => true,
        ]);

        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 2,
            'commission_type' => 'percentage',
            'value' => 5,
            'event' => 'sale',
            'status' => true,
        ]);

        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 3,
            'commission_type' => 'percentage',
            'value' => 2,
            'event' => 'sale',
            'status' => true,
        ]);

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

        $result = $this->commissionService->generateCommissionsForOrder($order);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['commissions_generated']);
        $this->assertEquals(27000, $result['total_amount']);

        $commissions = Commission::where('order_id', $order->id)
            ->orderBy('level')
            ->get();

        $this->assertEquals(20000, $commissions[0]->commission_amount);
        $this->assertEquals($level1->id, $commissions[0]->partner_id);
        $this->assertEquals(5000, $commissions[1]->commission_amount);
        $this->assertEquals($level2->id, $commissions[1]->partner_id);
        $this->assertEquals(2000, $commissions[2]->commission_amount);
        $this->assertEquals($level3->id, $commissions[2]->partner_id);
    }

    public function test_commission_maximum_cap()
    {
        CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 1,
            'commission_type' => 'percentage',
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

        $this->commissionService->generateCommissionsForOrder($order);

        $commission = Commission::where('order_id', $order->id)->first();

        $this->assertEquals(15000, $commission->commission_amount);
    }

    public function test_commission_statistics()
    {
        $rule = CommissionRule::create([
            'program_id' => $this->program->id,
            'level' => 1,
            'commission_type' => 'percentage',
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

        $customer = User::factory()->create();
        $orders = collect([
            ['amount' => 100000, 'status' => 'available'],
            ['amount' => 50000, 'status' => 'paid'],
            ['amount' => 30000, 'status' => 'reversed'],
        ])->map(function ($data, $index) use ($customer) {
            return Order::create([
                'order_number' => 'ORD-STATS-' . ($index + 1),
                'customer_id' => $customer->id,
                'program_id' => $this->program->id,
                'partner_id' => $this->program->id ? $this->getPartnerIdForStats() : null,
                'subtotal' => $data['amount'],
                'discount' => 0,
                'total' => $data['amount'],
                'currency' => 'NGN',
                'status' => 'paid',
                'paid_at' => now(),
                'payment_provider' => 'test',
                'payment_reference' => 'REF-STATS-' . ($index + 1),
            ]);
        });

        $amounts = [20000, 10000, 6000];
        $statuses = ['available', 'paid', 'reversed'];

        foreach ($orders as $index => $order) {
            Commission::create([
                'program_id' => $this->program->id,
                'order_id' => $order->id,
                'partner_id' => $programPartner->id,
                'rule_id' => $rule->id,
                'level' => 1,
                'commission_type' => 'percentage',
                'rate' => 20,
                'base_amount' => $order->total,
                'commission_amount' => $amounts[$index],
                'status' => $statuses[$index],
                'paid_at' => $statuses[$index] === 'paid' ? now() : null,
                'reversed_at' => $statuses[$index] === 'reversed' ? now() : null,
            ]);
        }

        $stats = $this->commissionService->getCommissionStats($programPartner);

        $this->assertEquals(20000, $stats['available']);
        $this->assertEquals(10000, $stats['paid']);
        $this->assertEquals(6000, $stats['reversed']);
        $this->assertEquals(36000, $stats['total']);
    }

    private function getPartnerIdForStats(): int
    {
        return ProgramPartner::where('partner_code', 'STATS')->value('id');
    }
}
