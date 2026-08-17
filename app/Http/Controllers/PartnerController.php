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
    public function create()
    {
        $programs = PartnershipProgram::where('status', 'active')
            ->orderBy('name')
            ->get();
        return view('partner.apply', compact('programs'));
    }
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
        ]);

        DB::transaction(function () use ($validated) {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            ProgramPartner::create([
                'program_id' => $validated['program_id'],
                'user_id' => $user->id,

                // We generate this after approval.
                'partner_code' => 'PENDING-' . Str::upper(Str::random(8)),

                'status' => 'pending',

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