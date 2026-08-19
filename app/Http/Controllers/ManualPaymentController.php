<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\PaymentSubmission;
use App\Models\PlatformPaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ManualPaymentController extends Controller
{
    public function show(Order $order)
    {
        $this->authorizeBuyer($order);
        abort_unless($order->status === 'pending', 404);
        return view('checkout.bank-transfer', ['order'=>$order, 'paymentSettings'=>PlatformPaymentSetting::current()]);
    }
    public function submit(Request $request, Order $order)
    {
        $this->authorizeBuyer($order);
        abort_unless($order->status === 'pending', 422);
        if ($order->paymentSubmissions()->where('status','pending')->exists()) return redirect()->route('checkout.show',['orderId'=>$order->id])->with('error','A payment proof is already awaiting verification for this order.');
        $data=$request->validate([
            'customer_name'=>['required','string','max:255'], 'customer_email'=>['required','email','max:255'], 'customer_phone'=>['nullable','string','max:30'],
            'amount'=>['required','numeric','min:0.01'], 'bank_name'=>['required','string','max:100'], 'transaction_reference'=>['required','string','max:100'],
            'transfer_date'=>['required','date'], 'proof'=>['required','file','mimes:jpg,jpeg,png,webp,pdf','max:5120']
        ]);
        if ((float)$data['amount'] !== (float)$order->total) return back()->withErrors(['amount'=>'The payment amount must match the order total.'])->withInput();
        $order->update(['customer_name'=>$data['customer_name'],'customer_email'=>$data['customer_email'],'customer_phone'=>$data['customer_phone']??null,'payment_method'=>'bank_transfer']);
        $path=$request->file('proof')->store('payment-proofs','public');
        $order->paymentSubmissions()->create(['amount'=>$data['amount'],'bank_name'=>$data['bank_name'],'transaction_reference'=>$data['transaction_reference'],'transfer_date'=>$data['transfer_date'],'proof_path'=>$path]);
        return redirect()->route('checkout.show',['orderId'=>$order->id])->with('success','Payment proof submitted. Our payment team will verify your transfer.');
    }
    private function authorizeBuyer(Order $order): void { if (Auth::check()) abort_unless($order->customer_id === Auth::id(),403); }
}
