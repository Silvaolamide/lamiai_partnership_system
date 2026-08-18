<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessPayout;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Models\Payout;
use App\Models\User;
use App\Services\AdminAnalyticsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly AdminAnalyticsService $analytics) {}

    public function index(Request $request)
    {
        [$from, $to] = $this->analytics->dateRange($request);
        $base = $this->analytics->summary($from, $to);
        $stats = [
            'businesses' => User::role('program_manager')->count(),
            'pending_businesses' => User::role('program_manager')->whereNull('business_super_admin_approved_at')->whereNull('business_rejected_at')->count(),
            'partners' => $base['partners'], 'active_partners' => $base['active_partners'], 'pending_partners' => $base['pending_partners'],
            'programs' => $base['programs'], 'active_programs' => PartnershipProgram::where('status', 'active')->count(), 'products' => $base['products'], 'customers' => $base['customers'],
            'orders' => Order::count(), 'paid_orders' => $base['orders'], 'sales' => $base['gross_sales'], 'commissions' => $base['commission_total'],
            'payable_commissions' => $base['payable'], 'paid_commissions' => $base['paid_commissions'], 'pending_payouts' => $base['pending_payouts'],
            'net_platform_revenue' => $base['net_revenue'],
        ];

        $recentOrders = $this->analytics->orderQuery($from, $to)->with(['customer', 'partner.user', 'program'])->latest()->limit(10)->get();
        $topPartners = ProgramPartner::with(['user', 'program'])->withSum(['commissions as earnings' => fn ($q) => $q->whereNotIn('status', ['reversed', 'cancelled'])->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))], 'commission_amount')->orderByDesc('earnings')->limit(8)->get();
        $programs = PartnershipProgram::withCount(['partners', 'orders', 'products'])->withSum(['orders as sales' => fn ($q) => $q->whereIn('status', AdminAnalyticsService::PAID_STATUSES)->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))], 'total')->latest()->limit(8)->get();
        $businesses = User::role('program_manager')->latest()->limit(10)->get()->map(function ($business) use ($from, $to) {
            $programIds = PartnershipProgram::where('owner_id', $business->id)->pluck('id');
            $paid = Order::whereIn('program_id', $programIds)->whereIn('status', AdminAnalyticsService::PAID_STATUSES)->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to));
            $business->dashboard_metrics = ['programs' => $programIds->count(), 'partners' => ProgramPartner::whereIn('program_id', $programIds)->count(), 'orders' => (clone $paid)->count(), 'sales' => (float) (clone $paid)->sum('total')];
            return $business;
        });
        $pendingActions = [
            ['label' => 'Business approvals', 'count' => $stats['pending_businesses'], 'route' => 'admin.businesses.index'],
            ['label' => 'Partner approvals', 'count' => $stats['pending_partners'], 'route' => 'admin.partners.index'],
            ['label' => 'Partner payouts', 'count' => Payout::whereIn('status', ['requested', 'pending'])->count(), 'route' => 'admin.payouts.index'],
            ['label' => 'Business payouts', 'count' => BusinessPayout::whereIn('status', ['requested', 'pending'])->count(), 'route' => 'admin.business-payouts.index'],
        ];
        $series = $this->analytics->series($from, $to);
        return view('admin.dashboard', compact('stats', 'recentOrders', 'topPartners', 'programs', 'businesses', 'pendingActions', 'series', 'from', 'to'));
    }
}
