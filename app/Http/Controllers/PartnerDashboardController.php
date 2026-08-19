<?php

namespace App\Http\Controllers;

use App\Models\Click;
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

        $partners = ProgramPartner::with(['program.products', 'program.commissionRules'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        $programStats = $partners->map(function ($partner) {
            $stats = $this->commissionService->getCommissionStats($partner);
            $orders = $partner->orders()->with(['customer', 'items.product', 'commissions.partner.user', 'commissions.rule'])->latest()->get();
            $paidOrders = $orders->whereIn('status', ['paid', 'completed', 'processing', 'fulfilled']);
            $pendingPaymentConfirmations = PaymentSubmission::query()->where('status', 'pending')->whereHas('order', fn ($query) => $query->where('partner_id', $partner->id))->count();

            $directCommission = $partner->commissions()->where('level', 1)->whereNotIn('status', ['reversed', 'cancelled'])->sum('commission_amount');
            $recruiterCommission = $partner->commissions()->where('level', '>', 1)->whereNotIn('status', ['reversed', 'cancelled'])->sum('commission_amount');

            $saleBreakdown = $paidOrders->map(function ($order) use ($partner) {
                $commissions = $order->commissions->whereNotIn('status', ['reversed', 'cancelled'])->values();
                $partnerEarnings = (float) $commissions->where('partner_id', $partner->id)->sum('commission_amount');
                $otherCommissions = (float) $commissions->where('partner_id', '!=', $partner->id)->sum('commission_amount');
                $businessNet = max(0, (float) $order->total - (float) $commissions->sum('commission_amount'));

                return ['order' => $order, 'commissions' => $commissions, 'sale_value' => (float) $order->total, 'partner_earnings' => $partnerEarnings, 'other_commissions' => $otherCommissions, 'business_net' => $businessNet, 'total_commissions' => (float) $commissions->sum('commission_amount')];
            });

            $productPerformance = $partner->program->products->map(function ($product) use ($paidOrders, $partner) {
                $items = $paidOrders->flatMap(fn ($order) => $order->items->where('product_id', $product->id));
                $unitsSold = (int) $items->sum('quantity');
                $revenue = (float) $items->sum('total');
                $ordersCount = $items->pluck('order_id')->unique()->count();
                $averageOrderValue = $ordersCount > 0 ? $revenue / $ordersCount : 0;
                $commissionRules = $partner->program->commissionRules->where('product_id', $product->id)->where('status', true)->sortBy('priority')->values();
                $commission = (float) $paidOrders->flatMap(fn ($order) => $order->commissions->where('partner_id', $partner->id)->whereNotIn('status', ['reversed', 'cancelled']))->filter(fn ($commission) => $commission->order && $commission->order->items->contains('product_id', $product->id))->sum('commission_amount');
                $lastSale = $items->map(fn ($item) => $item->order)->sortByDesc('created_at')->first();
                $rulesSummary = $commissionRules->map(fn ($rule) => [
                    'event' => $rule->event,
                    'level' => $rule->level,
                    'type' => $rule->commission_type,
                    'value' => (float) $rule->value,
                    'maximum_amount' => $rule->maximum_amount !== null ? (float) $rule->maximum_amount : null,
                ])->values()->all();

                return [
                    'product' => $product,
                    'units_sold' => $unitsSold,
                    'orders_count' => $ordersCount,
                    'revenue' => $revenue,
                    'commission' => $commission,
                    'average_order_value' => $averageOrderValue,
                    'last_sale_at' => optional($lastSale)->created_at,
                    'rules' => $rulesSummary,
                    'is_top_seller' => false,
                ];
            });

            $maxRevenue = (float) $productPerformance->max('revenue');
            $productPerformance = $productPerformance->map(function ($item) use ($maxRevenue) {
                $item['is_top_seller'] = $maxRevenue > 0 && $item['revenue'] === $maxRevenue;
                return $item;
            })->sortByDesc('revenue')->values();

            $clicks = Click::query()->where('program_id', $partner->program_id)->where('partner_id', $partner->id)->count();
            $conversionRate = $clicks > 0 ? ($paidOrders->count() / $clicks) * 100 : 0;
            $grossSales = (float) $paidOrders->sum('total');
            $totalCommissions = (float) $saleBreakdown->sum('total_commissions');
            $bestProduct = $productPerformance->first();

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
                'product_performance' => $productPerformance,
                'clicks' => $clicks,
                'conversion_rate' => $conversionRate,
                'average_sale_value' => $paidOrders->count() > 0 ? $grossSales / $paidOrders->count() : 0,
                'best_product' => $bestProduct,
            ];
        });

        $allProducts = $programStats->flatMap(fn ($program) => $program['product_performance']);
        $topProducts = $allProducts->sortByDesc('revenue')->values();

        return view('partner.dashboard', [
            'programStats' => $programStats,
            'totalPending' => (float) $programStats->sum(fn ($p) => $p['stats']['pending']),
            'totalPaid' => (float) $programStats->sum(fn ($p) => $p['stats']['paid']),
            'totalSales' => (int) $programStats->sum('paid_orders'),
            'totalSalesAmount' => (float) $programStats->sum('paid_sales_amount'),
            'totalRecruited' => (int) $programStats->sum('recruited_partners_count'),
            'totalNetBusinessRevenue' => (float) $programStats->sum('net_business_revenue'),
            'totalPendingPaymentConfirmations' => (int) $programStats->sum('pending_payment_confirmations'),
            'topProducts' => $topProducts,
        ]);
    }
}
