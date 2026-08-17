<?php

namespace App\Http\Controllers;

use App\Models\PartnershipProgram;
use App\Models\User;
use App\Models\ProgramPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    /**
     * Show partner application form.
     * 
     * Supports optional recruiter_code parameter to identify the recruiting partner.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $programs = PartnershipProgram::where('status', 'active')
            ->orderBy('name')
            ->get();

        $recruiterCode = $request->query('recruiter_code');
        $recruiterPartner = null;
        $recruiterError = null;

        // If recruiter code provided, validate it
        if ($recruiterCode) {
            $recruiterPartner = ProgramPartner::where('partner_code', $recruiterCode)
                ->where('status', 'active')
                ->first();

            if (!$recruiterPartner) {
                $recruiterError = 'Invalid or inactive recruiter code.';
            }
        }

        return view('partner.apply', [
            'programs' => $programs,
            'recruiterCode' => $recruiterCode,
            'recruiterPartner' => $recruiterPartner,
            'recruiterError' => $recruiterError,
        ]);
    }

    /**
     * Store new partner application.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'program_id' => [
                'required',
                'exists:partnership_programs,id',
            ],
            'recruiter_code' => ['nullable', 'string', 'exists:program_partners,partner_code'],
        ]);

        // Check if recruiter code is provided and belongs to the same program
        if ($validated['recruiter_code']) {
            $recruiterPartner = ProgramPartner::where('partner_code', $validated['recruiter_code'])
                ->where('program_id', $validated['program_id'])
                ->where('status', 'active')
                ->first();

            if (!$recruiterPartner) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['recruiter_code' => 'Recruiter code does not exist for this program or is inactive.']);
            }
        }

        DB::transaction(function () use ($validated) {
            // Create user account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Determine parent partner if recruiter code provided
            $parentPartnerId = null;
            if ($validated['recruiter_code']) {
                $recruiterPartner = ProgramPartner::where('partner_code', $validated['recruiter_code'])
                    ->where('program_id', $validated['program_id'])
                    ->where('status', 'active')
                    ->first();

                if ($recruiterPartner) {
                    $parentPartnerId = $recruiterPartner->id;
                }
            }

            // Create program partner record
            ProgramPartner::create([
                'program_id' => $validated['program_id'],
                'user_id' => $user->id,
                'partner_code' => 'PENDING-' . Str::upper(Str::random(8)),
                'status' => 'pending',
                'parent_partner_id' => $parentPartnerId,
                'joined_at' => now(),
            ]);
        });

        return redirect()
            ->route('partner.apply')
            ->with(
                'success',
                'Your application has been submitted and is awaiting approval.'
            );
    }
}
