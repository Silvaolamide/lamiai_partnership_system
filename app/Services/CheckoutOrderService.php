<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutOrderService
{
    public function create(Product $product, array $customer = []): Order
    {
        $product->loadMissing(['partnershipPrograms' => function ($query) {
            $query->where('status', 'active');
        }]);

        [$program, $partnerId] = $this->resolveReferral($product);

        return DB::transaction(function () use ($product, $program, $partnerId, $customer) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => Auth::id(),
                'customer_name' => $customer['customer_name'] ?? Auth::user()?->name,
                'customer_email' => $customer['customer_email'] ?? Auth::user()?->email,
                'customer_phone' => $customer['customer_phone'] ?? Auth::user()?->phone,
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
    }

    private function resolveReferral(Product $product): array
    {
        $referralService = app(ReferralService::class);
        $referral = $referralService->getReferral();
        $program = null;
        $partnerId = null;

        if ($referral) {
            $program = $product->partnershipPrograms->firstWhere('id', $referral['program_id']);

            if ($program) {
                $partner = $referralService->getProgramPartner();

                if ($partner && $partner->program_id == $program->id && $partner->status === 'active') {
                    if (!(Auth::check() && (int) $partner->user_id === (int) Auth::id())) {
                        $partnerId = $partner->id;
                    }
                } else {
                    $referralService->clearReferral();
                    $program = null;
                }
            } else {
                $referralService->clearReferral();
            }
        }

        if (!$program) {
            $program = $product->partnershipPrograms->first();
        }

        return [$program, $partnerId];
    }

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . date('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
