<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;

class ProductAccessController extends Controller
{
    public function show(Request $request, OrderItem $item)
    {
        $item->load(['order', 'product']);

        abort_unless($item->order && (int) $item->order->customer_id === (int) $request->user()->id, 403);
        abort_unless($item->order->status === 'paid', 403);
        abort_unless($item->product, 404);

        $delivery = is_array($item->product->metadata) ? ($item->product->metadata['delivery'] ?? []) : [];
        $type = $delivery['type'] ?? null;
        $url = $delivery['url'] ?? null;

        if (!$url) {
            return redirect()->route('customer.dashboard')
                ->with('status', 'This product is paid for, but its access instructions have not been configured yet. Please contact the product owner.');
        }

        if (!in_array($type, ['link', 'video', 'ebook', 'download', 'course'], true)) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'This product has an unsupported delivery configuration.');
        }

        return redirect()->away($url);
    }
}
