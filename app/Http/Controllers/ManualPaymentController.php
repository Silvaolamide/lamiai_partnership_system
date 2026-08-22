<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PlatformPaymentSetting;
use App\Services\CheckoutOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $request->session()->put('checkout_order_id', $order->id);
        $request->session()->forget('checkout_product_id');

        return redirect()
            ->route('checkout.bank-transfer', ['product' => $product->id])
            ->with('success', 'Your order has been placed and your payment proof has been submitted. Our payment team will verify the transfer before marking the order as paid.')
            ->with('bank_transfer_submitted', true)
            ->with('bank_transfer_order_number', $order->order_number);
    }

    private function validateProduct(Product $product): void
    {
        abort_unless($product->status === 'active', 404);

        if (Auth::check() && (int) $product->owner_id === (int) Auth::id() && Auth::user()->hasRole('program_manager')) {
            abort(403, 'You cannot purchase a product owned by your business.');
        }
    }
}
