<?php

namespace App\Http\Controllers;

use App\Models\BusinessPayout;
use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\Product;
use App\Models\ProgramPartner;
use App\Models\PaymentSubmission;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $ownerId = $request->user()->id;

        $programs = PartnershipProgram::query()->where('owner_id', $ownerId)->withCount(['partners','products','orders'])->with(['commissionRules' => fn ($query) => $query->where('status',true)->orderBy('level')])->latest()->get();
        $programIds = $programs->pluck('id');
        $products = Product::query()->where('owner_id',$ownerId)->with('partnershipPrograms')->latest()->get();
        $orders = Order::query()->whereIn('program_id',$programIds)->with(['partner.user','customer','program','commissions.partner.user','commissions.rule','items.product','businessPayout'])->latest()->get();
        $commissions = Commission::query()->whereIn('program_id',$programIds)->with(['partner.user','program','order'])->latest()->get();
        $completedOrders = $orders->filter(fn ($order) => in_array($order->status,['paid','completed','processing','fulfilled'],true));
        $grossRevenue = (float)$completedOrders->sum('total');
        $commissionTotal = (float)$commissions->whereNotIn('status',['reversed','cancelled'])->sum('commission_amount');
        $paidCommission = (float)$commissions->where('status','paid')->sum('commission_amount');
        $pendingCommission = (float)$commissions->whereIn('status',['available','pending','approved','payable'])->sum('commission_amount');
        $adminChargePercent = min(100,max(0,(float)PlatformSetting::getValue('admin_charge_percent',0)));
        $platformChargeTotal = (float)$completedOrders->sum(function($order) use ($adminChargePercent){
            return ((float)$order->platform_fee_amount > 0)
                ? (float)$order->platform_fee_amount
                : round((float)$order->total * ($adminChargePercent / 100), 2);
        });
        $delayDays = max(0,(int)PlatformSetting::getValue('payout_delay_days',7));
        $cutoff = now()->subDays($delayDays);
        $eligibleBusinessOrders = $completedOrders->filter(fn($order) => $order->status==='paid' && !$order->business_payout_id && $order->paid_at && $order->paid_at->lte($cutoff));
        $businessAvailable = (float)$eligibleBusinessOrders->sum(function($order) use ($adminChargePercent){
            $fee = ((float)$order->platform_fee_amount > 0) ? (float)$order->platform_fee_amount : round((float)$order->total * ($adminChargePercent / 100), 2);
            return max(0,(float)$order->total-(float)$order->commissions->whereNotIn('status',['reversed','cancelled'])->sum('commission_amount')-$fee);
        });
        $businessPaid = (float)BusinessPayout::where('business_id',$ownerId)->whereIn('status',['processed','paid'])->sum('amount');
        $businessRequested = (float)BusinessPayout::where('business_id',$ownerId)->whereIn('status',['requested','approved','processing'])->sum('amount');

        // This is a count of pending program applications, not new partner
        // accounts. A partner applying to two programs creates two pending
        // enrollments but remains one person.
        $pendingPartnerApprovals = ProgramPartner::whereIn('program_id', $programIds)
            ->where('status', 'pending')
            ->whereHas('program', fn ($query) => $query->where('owner_id', $ownerId)->where('settings->partner_business_approval_required', true))
            ->whereNull('business_approved_at')
            ->count();

        $pendingPaymentConfirmations = PaymentSubmission::query()
            ->where('status', 'pending')
            ->whereHas('order', fn ($query) => $query->whereIn('program_id', $programIds))
            ->count();

        $topAffiliates = $commissions->whereNotIn('status',['reversed','cancelled'])->groupBy('partner_id')->map(function($rows){ $first=$rows->first(); return ['partner'=>$first?->partner,'sales'=>$rows->pluck('order_id')->filter()->unique()->count(),'revenue'=>(float)$rows->sum(fn($c)=>(float)$c->base_amount),'commission'=>(float)$rows->sum(fn($c)=>(float)$c->commission_amount)]; })->sortByDesc('revenue')->take(5)->values();
        $recentOrders = $orders->take(8);
        $recentCommissions = $commissions->take(8);

        $recruitedPartners = ProgramPartner::whereIn('program_id',$programIds)->with(['user','program','parentPartner.user'])->latest('created_at')->take(8)->get();
        $activities = collect();
        foreach ($recentOrders as $order) {
            $activities->push(['type'=>'sale','date'=>$order->paid_at ?? $order->created_at,'title'=>'Sale made','description'=>($order->customer?->name ?? $order->customer_name ?? 'Customer').' purchased '.($order->items->pluck('product.name')->filter()->join(', ') ?: 'a product'),'partner'=>$order->partner?->user?->name,'amount'=>(float)$order->total,'order'=>$order]);
        }
        foreach ($recruitedPartners as $newPartner) {
            $activities->push(['type'=>'enrollment','date'=>$newPartner->created_at,'title'=>'Program enrollment','description'=>($newPartner->parentPartner?->user?->name ?? 'A partner').' referred '.($newPartner->user?->name ?? 'a partner').' into '.($newPartner->program?->name ?? 'a program'),'partner'=>$newPartner->user?->name,'amount'=>null,'order'=>null]);
        }
        $activities = $activities->sortByDesc('date')->take(15)->values();

        // A business can have the same person enrolled in multiple owned
        // programs. The headline affiliate/partner metric is therefore based
        // on distinct users, while each program's partners_count remains a
        // valid program-specific enrollment count.
        $uniquePartnerCount = ProgramPartner::whereIn('program_id', $programIds)
            ->whereNotNull('user_id')
            ->distinct()
            ->count('user_id');

        $stats = [
            'revenue'=>$grossRevenue,
            'sales'=>$completedOrders->count(),
            'commission'=>$commissionTotal,
            'paid_commission'=>$paidCommission,
            'pending_commission'=>$pendingCommission,
            'platform_charge'=>$platformChargeTotal,
            'affiliates'=>$uniquePartnerCount,
            'products'=>$products->count(),
            'programs'=>$programs->count(),
            'business_available'=>$businessAvailable,
            'business_paid'=>$businessPaid,
            'business_requested'=>$businessRequested,
            'payout_delay_days'=>$delayDays,
            'pending_partner_approvals'=>$pendingPartnerApprovals,
            'pending_payment_confirmations'=>$pendingPaymentConfirmations,
            'net_revenue'=>max(0,$grossRevenue-$commissionTotal-$platformChargeTotal),
        ];

        return view('business.dashboard',compact('programs','products','orders','commissions','recentOrders','recentCommissions','topAffiliates','stats','activities'));
    }
}
