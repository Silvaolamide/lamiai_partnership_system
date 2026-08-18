<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessPayout;
use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\Product;
use App\Models\ProgramPartner;
use App\Models\Payout;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $paidOrders = Order::where('status', 'paid');
        $paidSales = (float) $paidOrders->sum('total');
        $commissions = (float) Commission::where('status', '!=', 'reversed')->sum('commission_amount');
        $stats = [
            'businesses' => User::role('program_manager')->count(), 'pending_businesses' => User::role('program_manager')->whereNull('business_super_admin_approved_at')->whereNull('business_rejected_at')->count(),
            'partners' => ProgramPartner::count(), 'active_partners' => ProgramPartner::where('status', 'active')->count(), 'pending_partners' => ProgramPartner::where('status', 'pending')->count(),
            'programs' => PartnershipProgram::count(), 'active_programs' => PartnershipProgram::where('status', 'active')->count(), 'products' => Product::count(), 'customers' => User::role('customer')->count(),
            'orders' => Order::count(), 'paid_orders' => $paidOrders->count(), 'sales' => $paidSales, 'commissions' => $commissions,
            'payable_commissions' => (float) Commission::where('status', 'payable')->sum('commission_amount'), 'paid_commissions' => (float) Commission::where('status', 'paid')->sum('commission_amount'),
            'pending_payouts' => (float) Payout::whereIn('status', ['pending', 'approved'])->sum('amount'), 'net_platform_revenue' => $paidSales - $commissions,
        ];
        $recentOrders = Order::with(['customer', 'partner.user', 'program'])->latest()->limit(10)->get();
        $topPartners = ProgramPartner::with(['user', 'program'])->withSum(['commissions as earnings' => fn ($q) => $q->where('status', '!=', 'reversed')], 'commission_amount')->orderByDesc('earnings')->limit(8)->get();
        $programs = PartnershipProgram::withCount(['partners', 'orders', 'products'])->withSum(['orders as sales' => fn ($q) => $q->where('status', 'paid')], 'total')->latest()->limit(8)->get();
        $businesses = User::role('program_manager')->latest()->limit(10)->get()->map(function ($business) {
            $programIds = PartnershipProgram::where('owner_id', $business->id)->pluck('id');
            $business->dashboard_metrics = ['programs' => $programIds->count(), 'partners' => ProgramPartner::whereIn('program_id', $programIds)->count(), 'orders' => Order::whereIn('program_id', $programIds)->where('status', 'paid')->count(), 'sales' => (float) Order::whereIn('program_id', $programIds)->where('status', 'paid')->sum('total')];
            return $business;
        });
        $pendingActions = [
            ['label' => 'Business approvals', 'count' => $stats['pending_businesses'], 'route' => 'admin.businesses.index'],
            ['label' => 'Partner approvals', 'count' => $stats['pending_partners'], 'route' => 'admin.partners.index'],
            ['label' => 'Partner payouts', 'count' => Payout::whereIn('status', ['requested', 'pending'])->count(), 'route' => 'admin.payouts.index'],
            ['label' => 'Business payouts', 'count' => BusinessPayout::whereIn('status', ['requested', 'pending'])->count(), 'route' => 'admin.business-payouts.index'],
        ];
        return view('admin.dashboard', compact('stats', 'recentOrders', 'topPartners', 'programs', 'businesses', 'pendingActions'));
    }
}
