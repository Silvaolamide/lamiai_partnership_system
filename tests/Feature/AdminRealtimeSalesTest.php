<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRealtimeSalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_fetch_realtime_sales(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->getJson(route('admin.realtime.sales'));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'timestamp',
            ]);
    }

    public function test_non_super_admin_cannot_fetch_realtime_sales(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('program_manager');

        $this->actingAs($user)
            ->getJson(route('admin.realtime.sales'))
            ->assertForbidden();
    }
}
