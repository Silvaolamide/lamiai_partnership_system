<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
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
        $order = $this->pendingOrder($request);

        return view('auth.customer-login', [
            'order' => $order,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
        }

        $request->session()->regenerate();

        $order = $this->pendingOrder($request);
        if ($order) {
            if (strtolower((string) $order->customer_email) !== strtolower((string) Auth::user()->email)) {
                Auth::logout();
                $request->session()->regenerate();

                return back()->withErrors([
                    'email' => 'Please sign in with the same email address used for your purchase.',
                ])->withInput();
            }

            $this->attachOrderToCustomer($request, $order);
            return redirect()->route('customer.dashboard');
        }

        return redirect()->route('customer.dashboard');
    }

    public function createRegister(Request $request): View
    {
        $order = $this->pendingOrder($request);

        return view('auth.customer-register', [
            'order' => $order,
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $order = $this->pendingOrder($request);

        $emailRules = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class];
        if ($order) {
            $emailRules[] = 'in:'.$order->customer_email;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('customer');

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        if ($order) {
            $this->attachOrderToCustomer($request, $order);
        }

        return redirect()->route('customer.dashboard');
    }

    private function pendingOrder(Request $request): ?Order
    {
        $orderId = $request->session()->get('pending_customer_order_id')
            ?: $request->query('order');

        if (!$orderId) {
            return null;
        }

        $order = Order::find($orderId);

        if (!$order || $order->status !== 'paid' || $order->customer_id !== null) {
            return null;
        }

        $request->session()->put('pending_customer_order_id', $order->id);

        return $order;
    }

    private function attachOrderToCustomer(Request $request, Order $order): void
    {
        $user = Auth::user();

        if (strtolower((string) $order->customer_email) !== strtolower((string) $user->email)) {
            abort(403, 'The purchase email does not match this account.');
        }

        $order->update([
            'customer_id' => $user->id,
            'customer_name' => $user->name,
        ]);

        $request->session()->forget('pending_customer_order_id');
        $request->session()->forget('checkout_order_id');
    }
}
