<?php

namespace App\Http\Controllers;

use App\Models\PlatformPaymentSetting;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ManualPaymentController extends Controller
{
    public function __construct(
        protected CheckoutOrderService $checkoutOrderService,
    ) {}

    public function show(Product $product)
    {
        $this->validateProduct($product);

        return view('checkout.bank-transfer', [
            'product' => $product,
            'paymentSettings' => PlatformPaymentSetting::current(),
        ]);
    }

    /**
     * This is the only action in the bank-transfer flow that creates the order.
     */
    public function submit(Request $request, Product $product)
    {
        $this->validateProduct($product);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'bank_name' => ['required', 'string', 'max:100'],
            'transaction_reference' => ['required', 'string', 'max:100'],
            'transfer_date' => ['required', 'date'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        if ((float) $data['amount'] !== (float) $product->price) {
            return back()
                ->withErrors(['amount' => 'The payment amount must match the order total.'])
                ->withInput();
        }

        $wasLoggedIn = Auth::check();
        $order = $this->checkoutOrderService->create($product, $data);
        $path = $request->file('proof')->store('payment-proofs', 'public');

        $order->update([
            'payment_method' => 'bank_transfer',
        ]);

        $order->paymentSubmissions()->create([
            'amount' => $data['amount'],
            'bank_name' => $data['bank_name'],
            'transaction_reference' => $data['transaction_reference'],
            'transfer_date' => $data['transfer_date'],
            'proof_path' => $path,
        ]);

        $accountCreated = false;
        $passwordEmailSent = false;

        if (!$wasLoggedIn) {
            $email = strtolower($data['customer_email']);
            $customer = User::whereRaw('LOWER(email) = ?', [$email])->first();

            if (!$customer) {
                $customer = User::create([
                    'name' => $data['customer_name'],
                    'email' => $email,
                    'registration_type' => 'customer',
                    'password' => Hash::make(Str::random(64)),
                ]);
                $customer->assignRole('customer');
                $accountCreated = true;

                try {
                    $passwordEmailSent = Password::sendResetLink(['email' => $email]) === Password::RESET_LINK_SENT;
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            // Safely attach the order to the matching account without authenticating the browser session.
            $order->update(['customer_id' => $customer->id]);
        }

        $request->session()->put('checkout_order_id', $order->id);
        $request->session()->forget('checkout_product_id');

        return redirect()
            ->route('checkout.bank-transfer', ['product' => $product->id])
            ->with('success', 'Your order has been placed and your payment proof has been submitted. Our payment team will verify the transfer before marking the order as paid.')
            ->with('bank_transfer_submitted', true)
            ->with('bank_transfer_order_number', $order->order_number)
            ->with('bank_transfer_show_dashboard', $wasLoggedIn)
            ->with('bank_transfer_account_created', $accountCreated)
            ->with('bank_transfer_password_email_sent', $passwordEmailSent);
    }

    private function validateProduct(Product $product): void
    {
        abort_unless($product->status === 'active', 404);

        if (Auth::check() && (int) $product->owner_id === (int) Auth::id() && Auth::user()->hasRole('program_manager')) {
            abort(403, 'You cannot purchase a product owned by your business.');
        }
    }
}
