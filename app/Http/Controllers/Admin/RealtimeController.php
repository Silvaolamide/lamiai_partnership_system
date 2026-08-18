<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RealtimeController extends Controller
{
    public function sales(Request $request): StreamedResponse
    {
        return response()->stream(function () {
            $orders = Order::with(['customer', 'partner.user', 'program'])->latest()->limit(10)->get();
            echo "event: sales\n";
            echo 'data: '.json_encode($orders->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => $order->customer?->name ?? $order->customer_name ?? 'Guest customer',
                'program' => $order->program?->name,
                'partner' => $order->partner?->user?->name ?? 'Direct',
                'currency' => $order->currency,
                'total' => (float) $order->total,
                'created_at' => $order->created_at?->toIso8601String(),
            ])->values())."\n\n";
            echo "event: heartbeat\n";
            echo 'data: '.json_encode(['at' => now()->toIso8601String()])."\n\n";
            if (ob_get_level() > 0) ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
