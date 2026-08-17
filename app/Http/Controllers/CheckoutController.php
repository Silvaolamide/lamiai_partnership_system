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
     * Create a new order from product purchase.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Verify product is active
        if ($product->status !== 'active') {
            return redirect()->back()->with('error', 'This product is not available for purchase.');
        }

        $referral = $this->referralService->getReferral();

        // Create order
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

        // Create order item
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
     * 
     * @param Order $orderId
     * @return \Illuminate\View\View
     */
    public function show($orderId)
    {
        $order = Order::with(['items', 'items.product', 'partner.user', 'program'])
            ->findOrFail($orderId);

        // Use policy to check authorization
        $this->authorize('view', $order);

        return view('checkout.show', compact('order'));
    }

    /**
     * Confirm payment and mark order as paid.
     * 
     * In a real system, this would be called from a payment gateway webhook.
     * For testing/demo purposes, we'll manually mark the order as paid.
     * 
     * @param Order $orderId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Ensure current user owns this order
        if ($order->customer_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Prevent confirming already paid orders
        if ($order->status !== 'pending') {
            return redirect()->route('checkout.show', ['orderId' => $order->id])
                ->with('error', 'This order has already been processed.');
        }

        // Mark order as paid
        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $this->generatePaymentReference(),
            'payment_provider' => 'demo', // demo payment for testing
        ]);

        // Generate commissions based on commission rules
        try {
            $this->commissionService->generateCommissionsForOrder($order);
        } catch (\Exception $e) {
            \Log::error('Commission generation failed for order ' . $order->id . ': ' . $e->getMessage());
            // Don't fail the order even if commission generation fails
            // This can be retried later by admin
        }

        // Clear referral from session
        $this->referralService->clearReferral();

        return redirect()->route('order.success', ['orderId' => $order->id])
            ->with('success', 'Payment confirmed! Your order has been processed.');
    }

    /**
     * Display order success page.
     * 
     * @param Order $orderId
     * @return \Illuminate\View\View
     */
    public function success($orderId)
    {
        $order = Order::with(['items', 'items.product', 'partner.user', 'program', 'commissions'])
            ->findOrFail($orderId);

        // Use policy to check authorization
        $this->authorize('view', $order);

        return view('orders.success', compact('order'));
    }

    /**
     * Generate unique order number.
     * 
     * @return string
     */
    private function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . date('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Generate unique payment reference.
     * 
     * @return string
     */
    private function generatePaymentReference()
    {
        return 'PAY-' . Str::uuid();
    }
}
