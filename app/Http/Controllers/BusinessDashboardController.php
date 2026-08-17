<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\Product;
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
            ->with(['partner.user', 'customer', 'program'])
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
