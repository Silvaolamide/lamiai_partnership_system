<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function createLogin(Request $request): View
    {
        $this->storeIntendedUrl($request);

        return view('auth.customer-login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function createRegister(Request $request): View
    {
        $this->storeIntendedUrl($request);

        return view('auth.customer-register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('customer');

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function storeIntendedUrl(Request $request): void
    {
        $continue = $request->query('continue');

        if (!$continue) {
            return;
        }

        $appUrl = rtrim(config('app.url'), '/');
        if (str_starts_with($continue, $appUrl . '/') || str_starts_with($continue, '/')) {
            $request->session()->put('url.intended', $continue);
        }
    }
}
