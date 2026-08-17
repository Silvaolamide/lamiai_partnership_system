<?php

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PartnershipProgram;
use App\Models\Product;
use App\Models\ProgramPartner;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function payoutProgram(array $attributes = []): PartnershipProgram
{
    return PartnershipProgram::create(array_merge([
        'name' => 'Payout Test Program',
        'slug' => 'payout-test-' . uniqid(),
        'description' => 'Payout flow test',
        'status' => 'active',
        'attribution_window_days' => 30,
        'minimum_payout' => 0,
    ], $attributes));
}

function payoutProduct(): Product
{
    return Product::create([
        'name' => 'Payout Test Product',
        'slug' => 'payout-product-' . uniqid(),
        'description' => 'Test product',
        'price' => 20000,
        'currency' => 'NGN',
        'status' => 'active',
    ]);
}

function payoutPartner(PartnershipProgram $program, array $attributes = []): ProgramPartner
{
    $user = User::factory()->create();

    return ProgramPartner::create(array_merge([
        'program_id' => $program->id,
        'user_id' => $user->id,
        'partner_code' => 'PAY-' . strtoupper(substr(uniqid(), -6)),
        'status' => 'active',
        'joined_at' => now(),
        'approved_at' => now(),
    ], $attributes));
}

function payableCommission(ProgramPartner $partner, float $amount = 4000): Commission
{
    $program = PartnershipProgram::find($partner->program_id);
    $customer = User::factory()->create();
    $product = payoutProduct();
    $program->products()->attach($product->id);

    $rule = CommissionRule::create([
        'program_id' => $program->id,
        'product_id' => $product->id,
        'event' => 'sale',
        'level' => 1,
        'commission_type' => 'percentage',
        'value' => 20,
        'status' => true,
        'priority' => 1,
    ]);

    $order = Order::create([
        'order_number' => 'PAY-ORDER-' . uniqid(),
        'customer_id' => $customer->id,
        'program_id' => $program->id,
        'partner_id' => $partner->id,
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

    return Commission::create([
        'program_id' => $program->id,
        'partner_id' => $partner->id,
        'order_id' => $order->id,
        'source_partner_id' => null,
        'rule_id' => $rule->id,
        'level' => 1,
        'commission_type' => 'percentage',
        'rate' => 20,
        'base_amount' => 20000,
        'commission_amount' => $amount,
        'status' => 'payable',
        'available_at' => now(),
    ]);
}

test('partner can have a payout containing payable commissions', function () {
    $program = payoutProgram();
    $partner = payoutPartner($program);
    $commission = payableCommission($partner, 4000);

    $payout = Payout::create([
        'partner_id' => $partner->id,
        'program_id' => $program->id,
        'amount' => $commission->commission_amount,
        'currency' => 'NGN',
        'method' => 'bank_transfer',
        'status' => 'requested',
        'reference' => 'PAYOUT-' . uniqid(),
        'requested_at' => now(),
    ]);

    $payout->commissions()->attach($commission->id);

    expect($payout->amount)->toBe('4000.00');
    expect($payout->commissions()->count())->toBe(1);
});

test('payout cannot contain a commission already attached to another active payout', function () {
    $program = payoutProgram();
    $partner = payoutPartner($program);
    $commission = payableCommission($partner);

    $first = Payout::create([
        'partner_id' => $partner->id,
        'program_id' => $program->id,
        'amount' => 4000,
        'currency' => 'NGN',
        'method' => 'bank_transfer',
        'status' => 'requested',
        'reference' => 'PAYOUT-' . uniqid(),
        'requested_at' => now(),
    ]);
    $first->commissions()->attach($commission->id);

    $second = Payout::create([
        'partner_id' => $partner->id,
        'program_id' => $program->id,
        'amount' => 4000,
        'currency' => 'NGN',
        'method' => 'bank_transfer',
        'status' => 'requested',
        'reference' => 'PAYOUT-' . uniqid(),
        'requested_at' => now(),
    ]);

    $this->expectException(\Illuminate\Database\QueryException::class);
    $second->commissions()->attach($commission->id);
});

test('rejected payout returns commissions to payable status', function () {
    $program = payoutProgram();
    $partner = payoutPartner($program);
    $commission = payableCommission($partner);

    $payout = Payout::create([
        'partner_id' => $partner->id,
        'program_id' => $program->id,
        'amount' => 4000,
        'currency' => 'NGN',
        'method' => 'bank_transfer',
        'status' => 'rejected',
        'reference' => 'PAYOUT-' . uniqid(),
        'requested_at' => now(),
    ]);
    $payout->commissions()->attach($commission->id);

    $commission->update(['status' => 'payable']);

    expect($commission->fresh()->status)->toBe('payable');
});

test('processed payout marks its commissions as paid', function () {
    $program = payoutProgram();
    $partner = payoutPartner($program);
    $commission = payableCommission($partner);

    $payout = Payout::create([
        'partner_id' => $partner->id,
        'program_id' => $program->id,
        'amount' => 4000,
        'currency' => 'NGN',
        'method' => 'bank_transfer',
        'status' => 'processed',
        'reference' => 'PAYOUT-' . uniqid(),
        'requested_at' => now(),
        'approved_at' => now(),
        'processed_at' => now(),
    ]);
    $payout->commissions()->attach($commission->id);

    $commission->update(['status' => 'paid']);

    expect($payout->fresh()->status)->toBe('processed');
    expect($commission->fresh()->status)->toBe('paid');
});

test('payout amount equals the selected commissions', function () {
    $program = payoutProgram();
    $partner = payoutPartner($program);
    $first = payableCommission($partner, 4000);
    $second = payableCommission($partner, 1000);

    $payout = Payout::create([
        'partner_id' => $partner->id,
        'program_id' => $program->id,
        'amount' => 5000,
        'currency' => 'NGN',
        'method' => 'bank_transfer',
        'status' => 'requested',
        'reference' => 'PAYOUT-' . uniqid(),
        'requested_at' => now(),
    ]);
    $payout->commissions()->attach([$first->id, $second->id]);

    expect((float) $payout->commissions()->sum('commission_amount'))->toBe(5000.0);
    expect((float) $payout->amount)->toBe(5000.0);
});
