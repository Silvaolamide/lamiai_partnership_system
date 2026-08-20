<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product'])
            ->where('status', 'paid')
            ->latest('paid_at')
            ->get();

        $products = $orders
            ->flatMap(fn ($order) => $order->items)
            ->filter(fn ($item) => $item->product)
            ->map(function ($item) {
                $product = $item->product;
                $delivery = is_array($product->metadata) ? ($product->metadata['delivery'] ?? []) : [];

                return [
                    'item' => $item,
                    'product' => $product,
                    'delivery_type' => $delivery['type'] ?? null,
                    'delivery_label' => $delivery['label'] ?? null,
                    'has_access' => !empty($delivery['url']),
                ];
            })
            ->unique(fn ($entry) => $entry['product']->id)
            ->values();

        return view('customer.dashboard', compact('orders', 'products'));
    }
}
