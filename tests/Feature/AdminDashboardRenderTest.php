<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_renders_successfully(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->get(route('admin'))
            ->assertOk();
    }
}
