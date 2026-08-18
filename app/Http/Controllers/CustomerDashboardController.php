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
            ->map(fn ($item) => $item->product)
            ->filter()
            ->unique('id')
            ->values();

        return view('customer.dashboard', compact('orders', 'products'));
    }
}
