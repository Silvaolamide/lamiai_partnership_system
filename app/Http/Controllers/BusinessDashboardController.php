<?php

namespace App\Http\Controllers;

use App\Models\BusinessPayout;
use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\Product;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $ownerId = $request->user()->id;

        $programs = PartnershipProgram::query()
            ->where('owner_id', $ownerId)
            ->withCount(['partners', 'products', 'orders'])
            ->with(['commissionRules' => fn ($query) => $query->where('status', true)->orderBy('level')])
            ->latest()
            ->get();

        $programIds = $programs->pluck('id');

        $products = Product::query()
            ->where('owner_id', $ownerId)
            ->with('partnershipPrograms')
            ->latest()
            ->get();

        $orders = Order::query()
            ->whereIn('program_id', $programIds)
            ->with(['partner.user', 'customer', 'program', 'commissions', 'businessPayout'])
            ->latest()
            ->get();

        $commissions = Commission::query()
            ->whereIn('program_id', $programIds)
            ->with(['partner.user', 'program', 'order'])
            ->latest()
            ->get();

        $completedOrders = $orders->filter(fn ($order) => in_array($order->status, ['paid', 'completed', 'processing', 'fulfilled'], true));
        $affiliateRevenue = (float) $completedOrders->sum('total');
        $commissionTotal = (float) $commissions->whereNotIn('status', ['reversed', 'cancelled'])->sum('commission_amount');
        $paidCommission = (float) $commissions->where('status', 'paid')->sum('commission_amount');
        $pendingCommission = (float) $commissions->whereIn('status', ['available', 'pending', 'approved', 'payable'])->sum('commission_amount');

        $delayDays = max(0, (int) PlatformSetting::getValue('payout_delay_days', 7));
        $cutoff = now()->subDays($delayDays);
        $eligibleBusinessOrders = $completedOrders->filter(function ($order) use ($cutoff) {
            return $order->status === 'paid'
                && !$order->business_payout_id
                && $order->paid_at
                && $order->paid_at->lte($cutoff)
                && (!$order->partner_id || $order->commissions->isNotEmpty());
        });

        $businessAvailable = (float) $eligibleBusinessOrders->sum(function ($order) {
            $commissionsForOrder = $order->commissions->whereNotIn('status', ['reversed', 'cancelled'])->sum('commission_amount');
            return max(0, (float) $order->total - (float) $commissionsForOrder);
        });

        $businessPaid = (float) BusinessPayout::where('business_id', $ownerId)
            ->whereIn('status', ['processed', 'paid'])
            ->sum('amount');
        $businessRequested = (float) BusinessPayout::where('business_id', $ownerId)
            ->whereIn('status', ['requested', 'approved', 'processing'])
            ->sum('amount');

        $topAffiliates = $commissions
            ->whereNotIn('status', ['reversed', 'cancelled'])
            ->groupBy('partner_id')
            ->map(function ($rows) {
                $first = $rows->first();
                return [
                    'partner' => $first?->partner,
                    'sales' => $rows->pluck('order_id')->filter()->unique()->count(),
                    'revenue' => (float) $rows->sum(fn ($commission) => (float) $commission->base_amount),
                    'commission' => (float) $rows->sum(fn ($commission) => (float) $commission->commission_amount),
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

        $recentOrders = $orders->take(8);
        $recentCommissions = $commissions->take(8);

        $stats = [
            'revenue' => $affiliateRevenue,
            'sales' => $completedOrders->count(),
            'commission' => $commissionTotal,
            'paid_commission' => $paidCommission,
            'pending_commission' => $pendingCommission,
            'affiliates' => $programs->sum('partners_count'),
            'products' => $products->count(),
            'programs' => $programs->count(),
            'business_available' => $businessAvailable,
            'business_paid' => $businessPaid,
            'business_requested' => $businessRequested,
            'payout_delay_days' => $delayDays,
        ];

        return view('business.dashboard', compact(
            'programs',
            'products',
            'orders',
            'commissions',
            'recentOrders',
            'recentCommissions',
            'topAffiliates',
            'stats'
        ));
    }
}
