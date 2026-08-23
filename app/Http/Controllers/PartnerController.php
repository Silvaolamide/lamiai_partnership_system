<?php

namespace App\Http\Controllers;

use App\Models\PartnershipProgram;
use App\Models\User;
use App\Models\ProgramPartner;
use App\Services\PartnerApprovalService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function create(Request $request)
    {
        $programs = PartnershipProgram::where('status', 'active')->orderBy('name')->get();
        $recruiterCode = $request->query('recruiter_code');
        $recruiterPartner = null;
        $recruiterError = null;
        if ($recruiterCode) {
            $recruiterPartner = ProgramPartner::where('partner_code', $recruiterCode)->where('status', 'active')->first();
            if (!$recruiterPartner) $recruiterError = 'Invalid or inactive recruiter code.';
        }
        return view('partner.apply', compact('programs', 'recruiterCode', 'recruiterPartner', 'recruiterError'));
    }

    public function store(Request $request, PartnerApprovalService $approvalService)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'program_id' => ['required', 'exists:partnership_programs,id'],
            'recruiter_code' => ['nullable', 'string', 'exists:program_partners,partner_code'],
        ]);

        $program = PartnershipProgram::findOrFail($validated['program_id']);
        $recruiterCode = $validated['recruiter_code'] ?? null;

        if ($recruiterCode) {
            $recruiterPartner = ProgramPartner::where('partner_code', $recruiterCode)
                ->where('program_id', $validated['program_id'])
                ->where('status', 'active')
                ->first();
            if (!$recruiterPartner) {
                return redirect()->back()->withInput()->withErrors([
                    'recruiter_code' => 'Recruiter code does not exist for this program or is inactive.',
                ]);
            }
        }

        $user = null;
        $partner = null;

        DB::transaction(function () use ($validated, $recruiterCode, $program, &$user, &$partner, $approvalService) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'registration_type' => 'partner',
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('partner');

            $parentPartnerId = null;
            if ($recruiterCode) {
                $recruiterPartner = ProgramPartner::where('partner_code', $recruiterCode)
                    ->where('program_id', $validated['program_id'])
                    ->where('status', 'active')
                    ->first();
                $parentPartnerId = $recruiterPartner?->id;
            }

            // Account creation and program membership are separate concepts.
            // The membership carries its own approval context and policy.
            $partner = ProgramPartner::create([
                'program_id' => $program->id,
                'user_id' => $user->id,
                'partner_code' => 'PENDING-' . Str::upper(Str::random(8)),
                'status' => 'pending',
                'approval_context' => 'initial',
                'parent_partner_id' => $parentPartnerId,
                'joined_at' => now(),
            ]);

            // Initial registration now follows the same program-specific approval
            // engine as later program enrollments. Email verification is still
            // enforced by the verified middleware before partner access.
            $approvalService->syncProgramEnrollment($partner->load('program'));
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('partner.dashboard');
    }
}
