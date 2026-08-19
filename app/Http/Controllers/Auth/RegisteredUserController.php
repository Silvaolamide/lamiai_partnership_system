<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /** @throws ValidationException */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $isBusinessRegistration = $request->session()->pull('business_onboarding_intent', false) === true;
        $registrationType = $isBusinessRegistration ? 'business' : 'customer';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'registration_type' => $registrationType,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($isBusinessRegistration ? 'program_manager' : 'customer');
        event(new Registered($user));
        Auth::login($user);

        return $isBusinessRegistration
            ? redirect()->route('business.pending')
            : redirect()->route('customer.dashboard');
    }
}
