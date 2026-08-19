<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\PaymentDispute;
use Illuminate\Support\Facades\DB;
class PaymentDisputeController extends Controller
{
    public function index(){ $disputes=PaymentDispute::with(['order','customer'])->latest()->paginate(25); return view('admin.payment-disputes.index',compact('disputes')); }
    public function resolve(PaymentDispute $dispute){ if($dispute->status==='resolved') return back(); DB::transaction(fn()=> $dispute->update(['status'=>'resolved','resolved_by'=>auth()->id(),'resolved_at'=>now()])); return back()->with('success','Payment dispute marked resolved.'); }
}
