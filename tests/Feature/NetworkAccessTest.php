<?php

namespace Tests\Feature;

use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NetworkAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_every_network(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('super_admin');

        $business = User::factory()->create(['email_verified_at' => now()]);
        $business->assignRole('program_manager');

        $program = PartnershipProgram::create([
            'owner_id' => $business->id,
            'name' => 'Test Affiliate Program',
            'slug' => 'test-affiliate-program',
            'status' => 'active',
            'attribution_window_days' => 30,
            'minimum_payout' => 5000,
        ]);

        $rootUser = User::factory()->create(['email_verified_at' => now()]);
        $childUser = User::factory()->create(['email_verified_at' => now()]);
        $siblingUser = User::factory()->create(['email_verified_at' => now()]);

        $root = ProgramPartner::create([
            'program_id' => $program->id,
            'user_id' => $rootUser->id,
            'partner_code' => 'ROOT001',
            'status' => 'active',
        ]);

        ProgramPartner::create([
            'program_id' => $program->id,
            'user_id' => $childUser->id,
            'partner_code' => 'CHILD001',
            'status' => 'active',
            'parent_partner_id' => $root->id,
        ]);

        ProgramPartner::create([
            'program_id' => $program->id,
            'user_id' => $siblingUser->id,
            'partner_code' => 'SIBLING001',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('network.index'));

        $response->assertOk();
        $response->assertSee($rootUser->name);
        $response->assertSee($childUser->name);
        $response->assertSee($siblingUser->name);
    }

    public function test_partner_can_only_view_their_own_downline(): void
    {
        $partnerUser = User::factory()->create(['email_verified_at' => now()]);
        $partnerUser->assignRole('partner');

        $business = User::factory()->create(['email_verified_at' => now()]);
        $business->assignRole('program_manager');

        $program = PartnershipProgram::create([
            'owner_id' => $business->id,
            'name' => 'Partner Network Program',
            'slug' => 'partner-network-program',
            'status' => 'active',
            'attribution_window_days' => 30,
            'minimum_payout' => 5000,
        ]);

        $root = ProgramPartner::create([
            'program_id' => $program->id,
            'user_id' => $partnerUser->id,
            'partner_code' => 'ME001',
            'status' => 'active',
        ]);

        $directRecruitUser = User::factory()->create(['email_verified_at' => now()]);
        $indirectRecruitUser = User::factory()->create(['email_verified_at' => now()]);
        $unrelatedUser = User::factory()->create(['email_verified_at' => now()]);

        $directRecruit = ProgramPartner::create([
            'program_id' => $program->id,
            'user_id' => $directRecruitUser->id,
            'partner_code' => 'DIRECT001',
            'status' => 'active',
            'parent_partner_id' => $root->id,
        ]);

        ProgramPartner::create([
            'program_id' => $program->id,
            'user_id' => $indirectRecruitUser->id,
            'partner_code' => 'INDIRECT001',
            'status' => 'active',
            'parent_partner_id' => $directRecruit->id,
        ]);

        ProgramPartner::create([
            'program_id' => $program->id,
            'user_id' => $unrelatedUser->id,
            'partner_code' => 'OTHER001',
            'status' => 'active',
        ]);

        $response = $this->actingAs($partnerUser)->get(route('network.index'));

        $response->assertOk();
        $response->assertSee($partnerUser->name);
        $response->assertSee($directRecruitUser->name);
        $response->assertSee($indirectRecruitUser->name);
        $response->assertDontSee($unrelatedUser->name);
    }

    public function test_business_users_cannot_view_partner_network(): void
    {
        $business = User::factory()->create(['email_verified_at' => now()]);
        $business->assignRole('program_manager');

        $response = $this->actingAs($business)->get(route('network.index'));

        $response->assertForbidden();
    }

    public function test_unprivileged_authenticated_users_cannot_view_partner_network(): void
    {
        $customer = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($customer)->get(route('network.index'));

        $response->assertForbidden();
    }
}
