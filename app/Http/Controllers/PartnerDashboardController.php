<?php

namespace App\Http\Controllers;

use App\Models\PaymentSubmission;
use App\Models\ProgramPartner;
use App\Services\CommissionService;
use Illuminate\Support\Facades\Auth;

class PartnerDashboardController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index()
    {
        $user = Auth::user();

        $partners = ProgramPartner::with([
            'program.products',
            'program.commissionRules',
        ])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        $programStats = $partners->map(function ($partner) {
            $stats = $this->commissionService->getCommissionStats($partner);
            $orders = $partner->orders()->with(['customer', 'items.product', 'commissions.partner.user', 'commissions.rule'])->latest()->get();
            $paidOrders = $orders->whereIn('status', ['paid', 'completed', 'processing', 'fulfilled']);
            $pendingPaymentConfirmations = PaymentSubmission::query()
                ->where('status', 'pending')
                ->whereHas('order', fn ($query) => $query->where('partner_id', $partner->id))
                ->count();

            $directCommission = $partner->commissions()
                ->where('level', 1)
                ->whereNotIn('status', ['reversed', 'cancelled'])
                ->sum('commission_amount');

            $recruiterCommission = $partner->commissions()
                ->where('level', '>', 1)
                ->whereNotIn('status', ['reversed', 'cancelled'])
                ->sum('commission_amount');

            $saleBreakdown = $paidOrders->map(function ($order) use ($partner) {
                $commissions = $order->commissions->whereNotIn('status', ['reversed', 'cancelled'])->values();
                $partnerEarnings = (float) $commissions->where('partner_id', $partner->id)->sum('commission_amount');
                $otherCommissions = (float) $commissions->where('partner_id', '!=', $partner->id)->sum('commission_amount');
                $businessNet = max(0, (float) $order->total - (float) $commissions->sum('commission_amount'));

                return [
                    'order' => $order,
                    'commissions' => $commissions,
                    'sale_value' => (float) $order->total,
                    'partner_earnings' => $partnerEarnings,
                    'other_commissions' => $otherCommissions,
                    'business_net' => $businessNet,
                    'total_commissions' => (float) $commissions->sum('commission_amount'),
                ];
            });

            $grossSales = (float) $paidOrders->sum('total');
            $totalCommissions = (float) $saleBreakdown->sum('total_commissions');

            return [
                'partner' => $partner,
                'program' => $partner->program,
                'stats' => $stats,
                'recruited_partners_count' => $partner->childPartners()->count(),
                'total_orders' => $orders->count(),
                'paid_orders' => $paidOrders->count(),
                'pending_payment_confirmations' => $pendingPaymentConfirmations,
                'paid_sales_amount' => $grossSales,
                'direct_commission' => (float) $directCommission,
                'recruiter_commission' => (float) $recruiterCommission,
                'gross_sales' => $grossSales,
                'total_commissions' => $totalCommissions,
                'net_business_revenue' => max(0, $grossSales - $totalCommissions),
                'sale_breakdown' => $saleBreakdown,
            ];
        });

        return view('partner.dashboard', [
            'programStats' => $programStats,
            'totalPending' => (float) $programStats->sum(fn ($p) => $p['stats']['pending']),
            'totalPaid' => (float) $programStats->sum(fn ($p) => $p['stats']['paid']),
            'totalSales' => (int) $programStats->sum('paid_orders'),
            'totalSalesAmount' => (float) $programStats->sum('paid_sales_amount'),
            'totalRecruited' => (int) $programStats->sum('recruited_partners_count'),
            'totalNetBusinessRevenue' => (float) $programStats->sum('net_business_revenue'),
            'totalPendingPaymentConfirmations' => (int) $programStats->sum('pending_payment_confirmations'),
        ]);
    }
}
