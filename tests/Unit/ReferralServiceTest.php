<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ReferralServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $referralService;
    protected $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referralService = app(ReferralService::class);

        $this->program = PartnershipProgram::create([
            'name' => 'Test Program',
            'slug' => 'test-program',
            'description' => 'Test',
            'status' => 'active',
            'attribution_window_days' => 30,
            'minimum_payout' => 5000,
        ]);
    }

    public function test_valid_referral_code_is_stored()
    {
        $partner = User::factory()->create();
        $programPartner = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $partner->id,
            'partner_code' => 'VALIDCODE',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $request = Request::create('/product/test?ref=VALIDCODE');
        $result = $this->referralService->processReferralCode($request, $this->program->id);

        $this->assertTrue($result);
        $this->assertTrue($this->referralService->hasReferral());

        $referral = $this->referralService->getReferral();
        $this->assertEquals($programPartner->id, $referral['program_partner_id']);
        $this->assertEquals($this->program->id, $referral['program_id']);
    }

    public function test_invalid_referral_code_returns_error()
    {
        $request = Request::create('/product/test?ref=INVALIDCODE');
        $result = $this->referralService->processReferralCode($request, $this->program->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Invalid', $result['error']);
        $this->assertFalse($this->referralService->hasReferral());
    }

    public function test_inactive_partner_code_returns_error()
    {
        $partner = User::factory()->create();
        ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $partner->id,
            'partner_code' => 'INACTIVE',
            'status' => 'inactive',
            'joined_at' => now(),
        ]);

        $request = Request::create('/product/test?ref=INACTIVE');
        $result = $this->referralService->processReferralCode($request, $this->program->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_missing_ref_parameter_returns_false()
    {
        $request = Request::create('/product/test');
        $result = $this->referralService->processReferralCode($request, $this->program->id);

        $this->assertFalse($result);
    }

    public function test_referral_code_from_different_program_rejected()
    {
        $otherProgram = PartnershipProgram::create([
            'name' => 'Other Program',
            'slug' => 'other-program',
            'description' => 'Other',
            'status' => 'active',
            'attribution_window_days' => 30,
            'minimum_payout' => 5000,
        ]);

        $partner = User::factory()->create();
        ProgramPartner::create([
            'program_id' => $otherProgram->id,
            'user_id' => $partner->id,
            'partner_code' => 'OTHERCODE',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $request = Request::create('/product/test?ref=OTHERCODE');
        $result = $this->referralService->processReferralCode($request, $this->program->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_self_referral_is_prevented()
    {
        $user = User::factory()->create();

        ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $user->id,
            'partner_code' => 'SELFREF',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $request = Request::create('/product/test?ref=SELFREF');
        $result = $this->referralService->processReferralCode($request, $this->program->id, $user->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('own', strtolower($result['error']));
    }

    public function test_referral_can_be_cleared()
    {
        // A stored referral must reference a real active partner/program so
        // hasReferral() can validate the attribution before it is cleared.
        $partner = User::factory()->create();
        $programPartner = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $partner->id,
            'partner_code' => 'CLEARTEST',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->referralService->storeReferral($programPartner->id, $this->program->id);
        $this->assertTrue($this->referralService->hasReferral());

        $this->referralService->clearReferral();
        $this->assertFalse($this->referralService->hasReferral());
    }

    public function test_get_program_partner_from_referral()
    {
        $partner = User::factory()->create();
        $programPartner = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $partner->id,
            'partner_code' => 'GETTEST',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->referralService->storeReferral($programPartner->id, $this->program->id);
        $retrieved = $this->referralService->getProgramPartner();

        $this->assertNotNull($retrieved);
        $this->assertEquals($programPartner->id, $retrieved->id);
        $this->assertEquals($partner->id, $retrieved->user_id);
    }

    public function test_referral_link_generation()
    {
        $partner = User::factory()->create();
        $programPartner = ProgramPartner::create([
            'program_id' => $this->program->id,
            'user_id' => $partner->id,
            'partner_code' => 'LINKTEST',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $link = $this->referralService->generateReferralLink($programPartner, '/product/ai-video');

        $this->assertStringContainsString('/product/ai-video', $link);
        $this->assertStringContainsString('ref=LINKTEST', $link);
    }
}
