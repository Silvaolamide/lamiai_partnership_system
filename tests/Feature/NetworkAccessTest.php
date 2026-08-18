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

        $businessA = User::factory()->create(['email_verified_at' => now()]);
        $businessA->assignRole('program_manager');

        $businessB = User::factory()->create(['email_verified_at' => now()]);
        $businessB->assignRole('program_manager');

        $programA = $this->createProgram($businessA, 'Business A Program', 'business-a-program');
        $programB = $this->createProgram($businessB, 'Business B Program', 'business-b-program');

        $rootAUser = User::factory()->create(['email_verified_at' => now()]);
        $childAUser = User::factory()->create(['email_verified_at' => now()]);
        $rootBUser = User::factory()->create(['email_verified_at' => now()]);

        $rootA = $this->createPartner($programA, $rootAUser, 'ROOTA001');
        $this->createPartner($programA, $childAUser, 'CHILDA001', $rootA->id);
        $this->createPartner($programB, $rootBUser, 'ROOTB001');

        $response = $this->actingAs($admin)->get(route('network.index'));

        $response->assertOk();
        $response->assertSee($rootAUser->name);
        $response->assertSee($childAUser->name);
        $response->assertSee($rootBUser->name);
    }

    public function test_business_program_manager_can_view_partners_under_their_business(): void
    {
        $business = User::factory()->create(['email_verified_at' => now()]);
        $business->assignRole('program_manager');

        $otherBusiness = User::factory()->create(['email_verified_at' => now()]);
        $otherBusiness->assignRole('program_manager');

        $program = $this->createProgram($business, 'My Business Program', 'my-business-program');
        $otherProgram = $this->createProgram($otherBusiness, 'Other Business Program', 'other-business-program');

        $rootUser = User::factory()->create(['email_verified_at' => now()]);
        $childUser = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $root = $this->createPartner($program, $rootUser, 'MYROOT001');
        $this->createPartner($program, $childUser, 'MYCHILD001', $root->id);
        $this->createPartner($otherProgram, $otherUser, 'OTHER001');

        $response = $this->actingAs($business)->get(route('network.index'));

        $response->assertOk();
        $response->assertSee($rootUser->name);
        $response->assertSee($childUser->name);
        $response->assertDontSee($otherUser->name);
    }

    public function test_business_program_manager_can_drill_into_a_partner_under_their_business(): void
    {
        $business = User::factory()->create(['email_verified_at' => now()]);
        $business->assignRole('program_manager');

        $otherBusiness = User::factory()->create(['email_verified_at' => now()]);
        $otherBusiness->assignRole('program_manager');

        $program = $this->createProgram($business, 'My Business Program', 'my-business-program');
        $otherProgram = $this->createProgram($otherBusiness, 'Other Business Program', 'other-business-program');

        $rootUser = User::factory()->create(['email_verified_at' => now()]);
        $childUser = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $root = $this->createPartner($program, $rootUser, 'MYROOT001');
        $this->createPartner($program, $childUser, 'MYCHILD001', $root->id);
        $otherRoot = $this->createPartner($otherProgram, $otherUser, 'OTHER001');

        $response = $this->actingAs($business)->get(route('network.index', ['partner' => $root->id]));

        $response->assertOk();
        $response->assertSee($rootUser->name);
        $response->assertDontSee($otherUser->name);

        $otherResponse = $this->actingAs($business)->get(route('network.index', ['partner' => $otherRoot->id]));
        $otherResponse->assertNotFound();
    }

    public function test_partner_can_only_view_their_own_downline(): void
    {
        $partnerUser = User::factory()->create(['email_verified_at' => now()]);
        $partnerUser->assignRole('partner');

        $business = User::factory()->create(['email_verified_at' => now()]);
        $business->assignRole('program_manager');

        $program = $this->createProgram($business, 'Partner Network Program', 'partner-network-program');

        $root = $this->createPartner($program, $partnerUser, 'ME001');

        $directRecruitUser = User::factory()->create(['email_verified_at' => now()]);
        $indirectRecruitUser = User::factory()->create(['email_verified_at' => now()]);
        $unrelatedUser = User::factory()->create(['email_verified_at' => now()]);

        $directRecruit = $this->createPartner($program, $directRecruitUser, 'DIRECT001', $root->id);
        $this->createPartner($program, $indirectRecruitUser, 'INDIRECT001', $directRecruit->id);
        $this->createPartner($program, $unrelatedUser, 'OTHER001');

        $response = $this->actingAs($partnerUser)->get(route('network.index'));

        $response->assertOk();
        $response->assertSee($partnerUser->name);
        $response->assertSee($directRecruitUser->name);
        $response->assertSee($indirectRecruitUser->name);
        $response->assertDontSee($unrelatedUser->name);
    }

    public function test_partner_cannot_use_another_partner_as_network_root(): void
    {
        $partnerUser = User::factory()->create(['email_verified_at' => now()]);
        $partnerUser->assignRole('partner');

        $business = User::factory()->create(['email_verified_at' => now()]);
        $business->assignRole('program_manager');

        $program = $this->createProgram($business, 'Partner Network Program', 'partner-network-program');
        $root = $this->createPartner($program, $partnerUser, 'ME001');

        $otherUser = User::factory()->create(['email_verified_at' => now()]);
        $other = $this->createPartner($program, $otherUser, 'OTHER001');

        $response = $this->actingAs($partnerUser)->get(route('network.index', ['partner' => $other->id]));

        $response->assertOk();
        $response->assertSee($partnerUser->name);
        $response->assertDontSee($otherUser->name);
    }

    public function test_unprivileged_authenticated_users_cannot_view_partner_network(): void
    {
        $customer = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($customer)->get(route('network.index'));

        $response->assertForbidden();
    }

    private function createProgram(User $owner, string $name, string $slug): PartnershipProgram
    {
        return PartnershipProgram::create([
            'owner_id' => $owner->id,
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
            'attribution_window_days' => 30,
            'minimum_payout' => 5000,
        ]);
    }

    private function createPartner(
        PartnershipProgram $program,
        User $user,
        string $code,
        ?int $parentPartnerId = null
    ): ProgramPartner {
        return ProgramPartner::create([
            'program_id' => $program->id,
            'user_id' => $user->id,
            'partner_code' => $code,
            'status' => 'active',
            'parent_partner_id' => $parentPartnerId,
        ]);
    }
}
