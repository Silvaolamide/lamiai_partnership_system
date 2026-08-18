<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\ReferralService;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected $referralService;
    protected $commissionService;

    public function __construct(ReferralService $referralService, CommissionService $commissionService)
    {
        $this->referralService = $referralService;
        $this->commissionService = $commissionService;
    }

    /**
     * Start checkout from a product sales page.
     * Guests are sent through the customer authentication context and returned here.
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        if (isset($validated['product_id'])) {
            $product = Product::where('id', $validated['product_id'])
                ->where('status', 'active')
                ->firstOrFail();

            $request->session()->put('pending_checkout_product_id', $product->id);
        }

        if (!Auth::check()) {
            return redirect()->route('login', ['context' => 'customer']);
        }

        $productId = $request->session()->pull('pending_checkout_product_id');

        if (!$productId) {
            return redirect()->route('dashboard');
        }

        return $this->createOrderForAuthenticatedCustomer($productId, $request);
    }

    /**
     * Create a new order from an authenticated product purchase.
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        return $this->createOrderForAuthenticatedCustomer($validated['product_id'], $request);
    }

    protected function createOrderForAuthenticatedCustomer(int $productId, Request $request)
    {
        $product = Product::where('id', $productId)
            ->where('status', 'active')
            ->firstOrFail();

        $referral = $this->referralService->getReferral();

        $order = Order::create([
            'order_number' => $this->generateOrderNumber(),
            'customer_id' => Auth::id(),
            'program_id' => $referral['program_id'] ?? null,
            'partner_id' => $referral['program_partner_id'] ?? null,
            'subtotal' => $product->price,
            'discount' => 0,
            'total' => $product->price,
            'currency' => $product->currency,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total' => $product->price,
        ]);

        return redirect()->route('checkout.show', ['orderId' => $order->id]);
    }

    /**
     * Display checkout page for an order.
     */
    public function show($orderId)
    {
        $order = Order::with(['items', 'items.product', 'partner.user', 'program'])
            ->findOrFail($orderId);

        $this->authorize('view', $order);

        return view('checkout.show', compact('order'));
    }

    /**
     * Confirm payment and mark order as paid.
     */
    public function confirm($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->customer_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('checkout.show', ['orderId' => $order->id])
                ->with('error', 'This order has already been processed.');
        }

        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $this->generatePaymentReference(),
            'payment_provider' => 'demo',
        ]);

        try {
            $this->commissionService->generateCommissionsForOrder($order);
        } catch (\Exception $e) {
            \Log::error('Commission generation failed for order ' . $order->id . ': ' . $e->getMessage());
        }

        $this->referralService->clearReferral();

        return redirect()->route('order.success', ['orderId' => $order->id])
            ->with('success', 'Payment confirmed! Your order has been processed.');
    }

    public function success($orderId)
    {
        $order = Order::with(['items', 'items.product', 'partner.user', 'program', 'commissions'])
            ->findOrFail($orderId);

        $this->authorize('view', $order);

        return view('orders.success', compact('order'));
    }

    private function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . date('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    private function generatePaymentReference()
    {
        return 'PAY-' . Str::uuid();
    }
}
