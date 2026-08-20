<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\ReferralService;
use App\Services\CommissionService;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected $referralService;
    protected $commissionService;
    protected $paystackService;

    public function __construct(
        ReferralService $referralService,
        CommissionService $commissionService,
        PaystackService $paystackService
    ) {
        $this->referralService = $referralService;
        $this->commissionService = $commissionService;
        $this->paystackService = $paystackService;
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

        // Businesses may sell their own products, but cannot purchase their own products.
        // Partners and administrators are allowed to purchase products.
        if (Auth::check() && (int) $product->owner_id === (int) Auth::id() && Auth::user()->hasRole('program_manager')) {
            return redirect()->back()->with('error', 'You cannot purchase a product owned by your business.');
        }

        $referral = $this->referralService->getReferral();
        $program = null;
        $partnerId = null;

        if ($referral) {
            $program = $product->partnershipPrograms->firstWhere('id', $referral['program_id']);

            if ($program) {
                $partner = $this->referralService->getProgramPartner();

                if ($partner && $partner->program_id == $program->id && $partner->status === 'active') {
                    // A partner may buy through their own storefront, but the order must not
                    // be attributed to that same partner. CommissionService also enforces this
                    // rule as a final server-side safeguard.
                    if (!(Auth::check() && (int) $partner->user_id === (int) Auth::id())) {
                        $partnerId = $partner->id;
                    }
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
                'customer_name' => Auth::user()?->name,
                'customer_email' => Auth::user()?->email,
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

        $request->session()->put('checkout_order_id', $order->id);

        return redirect()->route('checkout.show', ['orderId' => $order->id]);
    }

    public function show(Request $request, $orderId)
    {
        $order = Order::with(['items', 'items.product', 'partner.user', 'program'])
            ->findOrFail($orderId);

        if (Auth::check()) {
            if ($order->customer_id !== Auth::id()) {
                abort(403);
            }
        } elseif ((int) $request->session()->get('checkout_order_id') !== (int) $order->id) {
            abort(403);
        }

        return view('checkout.show', compact('order'));
    }

    public function paystack(Request $request, $orderId)
    {
        $order = Order::with('customer')->findOrFail($orderId);

        if (Auth::check()) {
            if ($order->customer_id !== Auth::id()) {
                abort(403, 'Unauthorized');
            }
        } elseif ((int) $request->session()->get('checkout_order_id') !== (int) $order->id) {
            abort(403, 'Unauthorized');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('order.post-payment', ['orderId' => $order->id]);
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $order->update([
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'] ?? null,
        ]);

        try {
            $transaction = $this->paystackService->initialize($order, $validated['customer_email']);

            return redirect()->away($transaction['authorization_url']);
        } catch (\Throwable $e) {
            Log::error('Paystack initialization failed', [
                'order_id' => $order->id,
                'exception' => $e,
            ]);

            return redirect()->route('checkout.show', ['orderId' => $order->id])
                ->with('error', 'We could not start the payment. Please try again.');
        }
    }

    public function paystackCallback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('home')->with('error', 'Payment reference was not supplied.');
        }

        try {
            $data = $this->paystackService->verify($reference);
            $order = Order::where('payment_reference', $reference)->first();

            if (!$order) {
                return redirect()->route('home')->with('error', 'We could not find the order for this payment.');
            }

            $this->completePaystackOrder($order, $data);

            return $this->postPaymentRedirect($request, $order);
        } catch (\Throwable $e) {
            Log::error('Paystack callback verification failed', [
                'reference' => $reference,
                'exception' => $e,
            ]);

            return redirect()->route('home')
                ->with('error', 'Payment verification failed. Please contact support if your account was charged.');
        }
    }

    public function paystackWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');

        if (!$this->paystackService->validWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $event = json_decode($payload, true);

        if (($event['event'] ?? null) !== 'charge.success') {
            return response()->json(['message' => 'Event received']);
        }

        $data = $event['data'] ?? [];
        $reference = $data['reference'] ?? null;

        if (!$reference) {
            return response()->json(['message' => 'Missing transaction reference'], 400);
        }

        $order = Order::where('payment_reference', $reference)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        try {
            $this->completePaystackOrder($order, $data);
        } catch (\Throwable $e) {
            Log::error('Paystack webhook order completion failed', [
                'order_id' => $order->id,
                'reference' => $reference,
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Unable to process event'], 500);
        }

        return response()->json(['message' => 'Event processed']);
    }

    public function confirm(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        if (Auth::check()) {
            if ($order->customer_id !== Auth::id()) {
                abort(403, 'Unauthorized');
            }
        } elseif ((int) $request->session()->get('checkout_order_id') !== (int) $order->id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'payment_method' => ['required', 'in:demo'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
        ]);

        if ($order->status !== 'pending') {
            return redirect()->route('order.post-payment', ['orderId' => $order->id]);
        }

        $order->update([
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $this->generatePaymentReference(),
            'payment_provider' => 'demo',
        ]);

        try {
            $this->commissionService->generateCommissionsForOrder(
                $order->fresh(['partner', 'items.product'])
            );
        } catch (\Throwable $e) {
            Log::error('Demo commission generation failed', [
                'order_id' => $order->id,
                'exception' => $e,
            ]);

            return redirect()->route('order.post-payment', ['orderId' => $order->id])
                ->with('warning', 'Payment was recorded, but commission processing needs administrator attention.');
        }

        $this->referralService->clearReferral();

        return $this->postPaymentRedirect($request, $order);
    }

    public function postPayment($orderId)
    {
        $order = Order::with(['items.product', 'commissions'])->findOrFail($orderId);

        if ($order->status !== 'paid') {
            return redirect()->route('checkout.show', ['orderId' => $order->id]);
        }

        if (Auth::check() && $order->customer_id === Auth::id()) {
            return redirect()->route('customer.dashboard');
        }

        session()->put('pending_customer_order_id', $order->id);

        return redirect()->route('customer.login', ['order' => $order->id])
            ->with('status', 'Payment successful. Sign in or create your customer account to access your purchase.');
    }

    private function postPaymentRedirect(Request $request, Order $order)
    {
        if (Auth::check() && $order->customer_id === Auth::id()) {
            return redirect()->route('customer.dashboard');
        }

        $request->session()->put('pending_customer_order_id', $order->id);

        return redirect()->route('customer.login', ['order' => $order->id])
            ->with('status', 'Payment successful. Sign in or create your customer account to access your purchase.');
    }

    private function completePaystackOrder(Order $order, array $data): void
    {
        if ($order->status === 'paid') {
            return;
        }

        if (($data['status'] ?? null) !== 'success') {
            throw new \RuntimeException('Paystack transaction was not successful.');
        }

        $expectedAmount = (int) round(((float) $order->total) * 100);
        $actualAmount = (int) ($data['amount'] ?? 0);
        $actualCurrency = strtoupper((string) ($data['currency'] ?? ''));

        if ($actualAmount !== $expectedAmount || $actualCurrency !== strtoupper($order->currency)) {
            throw new \RuntimeException('Paystack amount or currency does not match the order.');
        }

        DB::transaction(function () use ($order, $data) {
            $lockedOrder = Order::lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status === 'paid') {
                return;
            }

            $lockedOrder->update([
                'status' => 'paid',
                'paid_at' => $data['paid_at'] ?? now(),
                'payment_provider' => 'paystack',
                'payment_reference' => $data['reference'],
            ]);

            $this->commissionService->generateCommissionsForOrder(
                $lockedOrder->fresh(['partner', 'items.product'])
            );
        });

        $this->referralService->clearReferral();
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
