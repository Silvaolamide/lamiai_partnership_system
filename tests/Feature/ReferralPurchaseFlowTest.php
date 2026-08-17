<?php

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PartnershipProgram;
use App\Models\Product;
use App\Models\ProgramPartner;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use App\Services\ReferralService;

function makePartnershipProgram(array $attributes = []): PartnershipProgram
{
    return PartnershipProgram::create(array_merge([
        'name' => 'Test Partnership',
        'slug' => 'test-partnership-' . uniqid(),
        'description' => 'Test program',
        'status' => 'active',
        'attribution_window_days' => 30,
        'minimum_payout' => 0,
    ], $attributes));
}

function makeProduct(array $attributes = []): Product
{
    return Product::create(array_merge([
        'name' => 'Test Product',
        'slug' => 'test-product-' . uniqid(),
        'description' => 'Test product',
        'price' => 20000,
        'currency' => 'NGN',
        'status' => 'active',
    ], $attributes));
}

function makePartner(PartnershipProgram $program, array $attributes = []): ProgramPartner
{
    $user = User::factory()->create();

    return ProgramPartner::create(array_merge([
        'program_id' => $program->id,
        'user_id' => $user->id,
        'partner_code' => 'TEST-' . strtoupper(substr(uniqid(), -6)),
        'status' => 'active',
        'joined_at' => now(),
        'approved_at' => now(),
    ], $attributes));
}

test('valid referral code is stored in session and invalid code is rejected', function () {
    $program = makePartnershipProgram();
    $product = makeProduct();
    $program->products()->attach($product->id);
    $partner = makePartner($program, ['partner_code' => 'VALID123']);

    $response = $this->get('/product/' . $product->slug . '?ref=VALID123');

    $response->assertOk();
    $this->assertSame($partner->id, session('referral_program_partner_id'));
    $this->assertSame($program->id, session('referral_program_id'));

    $this->get('/product/' . $product->slug . '?ref=NOPE999')
        ->assertOk();

    $this->assertNull(session('referral_program_partner_id'));
});

test('referral attribution expires according to the program window', function () {
    $program = makePartnershipProgram(['attribution_window_days' => 1]);
    $partner = makePartner($program);
    $service = app(ReferralService::class);

    $request = Request::create('/product/test?ref=' . $partner->partner_code, 'GET', [
        'ref' => $partner->partner_code,
    ]);

    expect($service->processReferralCode($request))->toBeTrue();

    $this->travel(2)->days();

    expect($service->getReferral())->toBeNull();
    $this->assertNull(session('referral_program_partner_id'));
});

test('paid referred order generates 20 percent seller and 5 percent recruiter commissions from rules', function () {
    $program = makePartnershipProgram();
    $product = makeProduct();
    $program->products()->attach($product->id);

    $recruiter = makePartner($program, ['partner_code' => 'RECRUITER']);
    $seller = makePartner($program, [
        'partner_code' => 'SELLER',
        'parent_partner_id' => $recruiter->id,
    ]);

    CommissionRule::create([
        'program_id' => $program->id,
        'product_id' => null,
        'event' => 'sale',
        'level' => 1,
        'commission_type' => 'percentage',
        'value' => 20,
        'status' => true,
        'priority' => 0,
    ]);

    CommissionRule::create([
        'program_id' => $program->id,
        'product_id' => null,
        'event' => 'sale',
        'level' => 2,
        'commission_type' => 'percentage',
        'value' => 5,
        'status' => true,
        'priority' => 0,
    ]);

    $customer = User::factory()->create();
    $order = Order::create([
        'order_number' => 'TEST-' . uniqid(),
        'customer_id' => $customer->id,
        'program_id' => $program->id,
        'partner_id' => $seller->id,
        'subtotal' => 20000,
        'discount' => 0,
        'total' => 20000,
        'currency' => 'NGN',
        'status' => 'paid',
        'payment_provider' => 'demo',
        'payment_reference' => 'PAY-' . uniqid(),
        'paid_at' => now(),
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 20000,
        'total' => 20000,
    ]);

    $result = app(CommissionService::class)->generateCommissionsForOrder($order->fresh());

    expect($result['commissions_generated'])->toBe(2);
    expect((float) Commission::where('partner_id', $seller->id)->first()->commission_amount)->toBe(4000.0);
    expect((float) Commission::where('partner_id', $recruiter->id)->first()->commission_amount)->toBe(1000.0);
});

test('unpaid order generates no commissions', function () {
    $program = makePartnershipProgram();
    $seller = makePartner($program);

    CommissionRule::create([
        'program_id' => $program->id,
        'event' => 'sale',
        'level' => 1,
        'commission_type' => 'percentage',
        'value' => 20,
        'status' => true,
        'priority' => 0,
    ]);

    $customer = User::factory()->create();
    $order = Order::create([
        'order_number' => 'TEST-' . uniqid(),
        'customer_id' => $customer->id,
        'program_id' => $program->id,
        'partner_id' => $seller->id,
        'subtotal' => 20000,
        'discount' => 0,
        'total' => 20000,
        'currency' => 'NGN',
        'status' => 'pending',
    ]);

    expect(fn () => app(CommissionService::class)->generateCommissionsForOrder($order))
        ->toThrow(Exception::class);

    expect(Commission::count())->toBe(0);
});

test('commission generation is idempotent for the same order', function () {
    $program = makePartnershipProgram();
    $seller = makePartner($program);

    CommissionRule::create([
        'program_id' => $program->id,
        'event' => 'sale',
        'level' => 1,
        'commission_type' => 'percentage',
        'value' => 20,
        'status' => true,
        'priority' => 0,
    ]);

    $customer = User::factory()->create();
    $order = Order::create([
        'order_number' => 'TEST-' . uniqid(),
        'customer_id' => $customer->id,
        'program_id' => $program->id,
        'partner_id' => $seller->id,
        'subtotal' => 20000,
        'discount' => 0,
        'total' => 20000,
        'currency' => 'NGN',
        'status' => 'paid',
        'payment_provider' => 'demo',
        'payment_reference' => 'PAY-' . uniqid(),
        'paid_at' => now(),
    ]);

    $first = app(CommissionService::class)->generateCommissionsForOrder($order);
    $second = app(CommissionService::class)->generateCommissionsForOrder($order->fresh());

    expect($first['commissions_generated'])->toBe(1);
    expect($second['status'])->toBe('already_exists');
    expect(Commission::where('order_id', $order->id)->count())->toBe(1);
});
