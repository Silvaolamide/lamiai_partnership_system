<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\PaymentSubmission;
use App\Models\Order;
use App\Models\PlatformPaymentSetting;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PaymentController extends Controller
{
    public function index(){ $payments=PaymentSubmission::with(['order.customer','verifier'])->latest()->paginate(25); return view('admin.payments.index',compact('payments')); }
    public function show(PaymentSubmission $payment){ $payment->load(['order.customer','order.items.product','order.partner.user','order.program','verifier']); return view('admin.payments.show',['payment'=>$payment,'paymentSettings'=>PlatformPaymentSetting::current()]); }
    public function confirm(PaymentSubmission $payment, CommissionService $commissionService){
        if($payment->status!=='pending') return back()->with('error','This payment has already been processed.');
        DB::transaction(function() use($payment,$commissionService){ $payment=PaymentSubmission::lockForUpdate()->findOrFail($payment->id); if($payment->status!=='pending') return; $order=Order::lockForUpdate()->findOrFail($payment->order_id); if($order->status==='paid'){ $payment->update(['status'=>'confirmed','verified_by'=>auth()->id(),'verified_at'=>now()]); return; } $order->update(['status'=>'paid','paid_at'=>now(),'payment_provider'=>'manual_bank_transfer','payment_reference'=>$payment->transaction_reference,'payment_method'=>'bank_transfer']); $payment->update(['status'=>'confirmed','verified_by'=>auth()->id(),'verified_at'=>now()]); $commissionService->generateCommissionsForOrder($order->fresh(['partner','items.product'])); });
        return back()->with('success','Payment confirmed and order marked as paid.');
    }
    public function reject(Request $request, PaymentSubmission $payment){ $data=$request->validate(['rejection_reason'=>['required','string','max:2000']]); if($payment->status!=='pending') return back()->with('error','This payment has already been processed.'); $payment->update(['status'=>'rejected','rejection_reason'=>$data['rejection_reason'],'verified_by'=>auth()->id(),'verified_at'=>now()]); return back()->with('success','Payment rejected.'); }
}
