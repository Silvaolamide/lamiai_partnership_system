<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentDispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentDisputeController extends Controller
{
    public function store(Request $request, Order $order)
    {
        if (Auth::check()) abort_unless($order->customer_id === Auth::id(), 403);
        $data = $request->validate([
            'reason' => ['required','string','max:255'],
            'message' => ['required','string','max:5000'],
            'attachment' => ['nullable','file','mimes:jpg,jpeg,png,webp,pdf','max:5120'],
        ]);
        $open = $order->paymentDisputes()->where('status','open')->exists();
        if ($open) return back()->with('error','There is already an open payment dispute for this order.');
        $path = $request->file('attachment')?->store('payment-disputes','public');
        $order->paymentDisputes()->create([
            'customer_id' => Auth::id(),
            'reason' => $data['reason'],
            'message' => $data['message'],
            'attachment_path' => $path,
        ]);
        return back()->with('success','Your payment dispute has been sent to customer support.');
    }
}
