<?php

use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Models\User;
use App\Services\AdminAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function analyticsBusiness(): User
{
    $business = User::factory()->create(['name' => 'Analytics Business']);
    Role::findOrCreate('program_manager', 'web');
    $business->assignRole('program_manager');
    return $business;
}

function analyticsProgram(User $business, string $name): PartnershipProgram
{
    return PartnershipProgram::create([
        'owner_id' => $business->id,
        'name' => $name,
        'slug' => strtolower(str_replace(' ', '-', $name)).'-'.uniqid(),
        'description' => 'Analytics test program',
        'status' => 'active',
        'attribution_window_days' => 30,
        'minimum_payout' => 0,
    ]);
}

function analyticsPartner(PartnershipProgram $program, string $code): ProgramPartner
{
    $user = User::factory()->create(['email_verified_at' => now()]);

    return ProgramPartner::create([
        'program_id' => $program->id,
        'user_id' => $user->id,
        'partner_code' => $code,
        'status' => 'active',
    ]);
}

test('admin analytics uses commission_amount and filters by business and program', function () {
    $businessA = analyticsBusiness();
    $businessB = analyticsBusiness();
    $programA = analyticsProgram($businessA, 'Program A');
    $programB = analyticsProgram($businessB, 'Program B');
    $partnerA = analyticsPartner($programA, 'ANALYTICS-A-'.uniqid());
    $partnerB = analyticsPartner($programB, 'ANALYTICS-B-'.uniqid());

    $orderA = Order::create([
        'order_number' => 'A-'.uniqid(), 'program_id' => $programA->id, 'subtotal' => 10000, 'discount' => 0, 'total' => 10000,
        'currency' => 'NGN', 'status' => 'paid', 'payment_provider' => 'test', 'payment_reference' => uniqid(), 'paid_at' => now(),
    ]);
    $orderB = Order::create([
        'order_number' => 'B-'.uniqid(), 'program_id' => $programB->id, 'subtotal' => 20000, 'discount' => 0, 'total' => 20000,
        'currency' => 'NGN', 'status' => 'paid', 'payment_provider' => 'test', 'payment_reference' => uniqid(), 'paid_at' => now(),
    ]);

    Commission::create([
        'program_id' => $programA->id, 'order_id' => $orderA->id, 'partner_id' => $partnerA->id, 'level' => 1,
        'commission_type' => 'percentage', 'rate' => 10, 'base_amount' => 10000, 'commission_amount' => 1000, 'status' => 'approved',
    ]);
    Commission::create([
        'program_id' => $programB->id, 'order_id' => $orderB->id, 'partner_id' => $partnerB->id, 'level' => 1,
        'commission_type' => 'percentage', 'rate' => 10, 'base_amount' => 20000, 'commission_amount' => 2000, 'status' => 'approved',
    ]);

    $service = app(AdminAnalyticsService::class);
    $stats = $service->summary(null, null, $businessA->id, $programA->id);

    expect($stats['gross_sales'])->toBe(10000.0)
        ->and($stats['commission_total'])->toBe(1000.0)
        ->and($stats['net_revenue'])->toBe(9000.0)
        ->and($stats['orders'])->toBe(1);
});

test('analytics sales series is grouped by day without loading order rows', function () {
    $business = analyticsBusiness();
    $program = analyticsProgram($business, 'Series Program');
    Order::create([
        'order_number' => 'S-'.uniqid(), 'program_id' => $program->id, 'subtotal' => 5000, 'discount' => 0, 'total' => 5000,
        'currency' => 'NGN', 'status' => 'paid', 'payment_provider' => 'test', 'payment_reference' => uniqid(), 'paid_at' => now(),
    ]);

    $series = app(AdminAnalyticsService::class)->series(now()->startOfDay(), now()->endOfDay(), $business->id, $program->id);

    expect($series)->toHaveCount(1)
        ->and($series[0]['sales'])->toBe(5000.0)
        ->and($series[0]['orders'])->toBe(1);
});
