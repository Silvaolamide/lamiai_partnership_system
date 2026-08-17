<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\ReferralService;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function create(Request $request)
    {
        $validated = $request->validate(['product_id' => ['required', 'exists:products,id']]);

        $product = Product::with(['partnershipPrograms' => function ($query) {
            $query->where('status', 'active');
        }])->findOrFail($validated['product_id']);

        if ($product->status !== 'active') {
            return redirect()->back()->with('error', 'This product is not available for purchase.');
        }

        $referral = $this->referralService->getReferral();
        $program = null;
        $partnerId = null;

        if ($referral) {
            $program = $product->partnershipPrograms->firstWhere('id', $referral['program_id']);

            if ($program) {
                $partner = $this->referralService->getProgramPartner();

                if ($partner && $partner->program_id == $program->id && $partner->status === 'active') {
                    $partnerId = $partner->id;
                } else {
                    $this->referralService->clearReferral();
                }
            } else {
                $this->referralService->clearReferral();
            }
        }

        if (!$program) {
            $program = $product->partnershipPrograms->first();
        }

        $order = DB::transaction(function () use ($product, $program, $partnerId) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => Auth::id(),
                'program_id' => $program?->id,
                'partner_id' => $partnerId,
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

            return $order;
        });

        return redirect()->route('checkout.show', ['orderId' => $order->id]);
    }

    public function show($orderId)
    {
        $order = Order::with(['items', 'items.product', 'partner.user', 'program'])
            ->findOrFail($orderId);

        $this->authorize('view', $order);

        return view('checkout.show', compact('order'));
    }

    public function confirm(Request $request, $orderId)
    {
        $validated = $request->validate(['payment_method' => ['required', 'in:demo']]);

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
            'payment_provider' => $validated['payment_method'],
        ]);

        try {
            $this->commissionService->generateCommissionsForOrder(
                $order->fresh(['partner', 'items.product'])
            );
        } catch (\Throwable $e) {
            \Log::error('Commission generation failed for order ' . $order->id . ': ' . $e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e,
            ]);

            return redirect()->route('order.success', ['orderId' => $order->id])
                ->with('warning', 'Payment was recorded, but commission processing needs administrator attention.');
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
