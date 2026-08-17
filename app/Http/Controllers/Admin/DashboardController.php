<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\Product;
use App\Models\ProgramPartner;
use App\Models\Payout;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'partners' => ProgramPartner::count(),
            'active_partners' => ProgramPartner::where('status', 'active')->count(),
            'pending_partners' => ProgramPartner::where('status', 'pending')->count(),
            'programs' => PartnershipProgram::count(),
            'active_programs' => PartnershipProgram::where('status', 'active')->count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'paid_orders' => Order::where('status', 'paid')->count(),
            'sales' => Order::where('status', 'paid')->sum('total'),
            'commissions' => Commission::sum('commission_amount'),
            'payable_commissions' => Commission::where('status', 'payable')->sum('commission_amount'),
            'paid_commissions' => Commission::where('status', 'paid')->sum('commission_amount'),
            'pending_payouts' => Payout::whereIn('status', ['pending', 'approved'])->sum('amount'),
        ];

        $recentOrders = Order::with(['customer', 'partner.user', 'program'])
            ->latest()
            ->limit(8)
            ->get();

        $topPartners = ProgramPartner::with(['user', 'program'])
            ->withSum(['commissions as earnings' => fn ($q) => $q->where('status', '!=', 'reversed')], 'commission_amount')
            ->orderByDesc('earnings')
            ->limit(8)
            ->get();

        $programs = PartnershipProgram::withCount(['partners', 'orders', 'products'])
            ->withSum(['orders as sales' => fn ($q) => $q->where('status', 'paid')], 'total')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'topPartners', 'programs'));
    }
}
