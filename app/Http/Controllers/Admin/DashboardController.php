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
        $businessId = $request->filled('business_id') ? (int) $request->input('business_id') : null;
        $programId = $request->filled('program_id') ? (int) $request->input('program_id') : null;
        $base = $this->analytics->summary($from, $to, $businessId, $programId);
        $stats = [
            'businesses' => User::role('program_manager')->count(), 'pending_businesses' => User::role('program_manager')->whereNull('business_super_admin_approved_at')->whereNull('business_rejected_at')->count(),
            'partners' => $base['partners'], 'active_partners' => $base['active_partners'], 'pending_partners' => $base['pending_partners'], 'programs' => $base['programs'],
            'active_programs' => PartnershipProgram::when($businessId, fn ($q) => $q->where('owner_id', $businessId))->when($programId, fn ($q) => $q->whereKey($programId))->where('status', 'active')->count(),
            'products' => $base['products'], 'customers' => $base['customers'], 'orders' => Order::count(), 'paid_orders' => $base['orders'], 'sales' => $base['gross_sales'], 'commissions' => $base['commission_total'],
            'payable_commissions' => $base['payable'], 'paid_commissions' => $base['paid_commissions'], 'pending_payouts' => $base['pending_payouts'], 'net_platform_revenue' => $base['net_revenue'],
        ];

        $recentOrders = $this->analytics->orderQuery($from, $to, $businessId, $programId)->with(['customer', 'partner.user', 'program'])->latest()->limit(10)->get();
        $topPartners = ProgramPartner::whereIn('program_id', PartnershipProgram::select('id')->when($businessId, fn ($q) => $q->where('owner_id', $businessId))->when($programId, fn ($q) => $q->whereKey($programId)))->with(['user', 'program'])->withSum(['commissions as earnings' => fn ($q) => $q->whereNotIn('status', ['reversed', 'cancelled'])->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))], 'commission_amount')->orderByDesc('earnings')->limit(8)->get();
        $programs = PartnershipProgram::when($businessId, fn ($q) => $q->where('owner_id', $businessId))->when($programId, fn ($q) => $q->whereKey($programId))->withCount(['partners', 'orders', 'products'])->withSum(['orders as sales' => fn ($q) => $q->whereIn('status', AdminAnalyticsService::PAID_STATUSES)->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))], 'total')->latest()->limit(8)->get();
        $businesses = User::role('program_manager')->when($businessId, fn ($q) => $q->whereKey($businessId))->latest()->limit(10)->get()->map(function ($business) use ($from, $to, $programId) {
            $programIds = PartnershipProgram::where('owner_id', $business->id)->when($programId, fn ($q) => $q->whereKey($programId))->pluck('id');
            $paid = Order::whereIn('program_id', $programIds)->whereIn('status', AdminAnalyticsService::PAID_STATUSES)->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to));
            $business->dashboard_metrics = ['programs' => $programIds->count(), 'partners' => ProgramPartner::whereIn('program_id', $programIds)->count(), 'orders' => (clone $paid)->count(), 'sales' => (float) (clone $paid)->sum('total')]; return $business;
        });

        $businessPartnerApprovalQuery = ProgramPartner::query()
            ->where('status', 'pending')
            ->whereNull('business_approved_at')
            ->whereHas('program', fn ($q) => $q->where('settings->partner_business_approval_required', true));
        if ($businessId) $businessPartnerApprovalQuery->whereHas('program', fn ($q) => $q->where('owner_id', $businessId));
        if ($programId) $businessPartnerApprovalQuery->where('program_id', $programId);
        $pendingBusinessPartnerApprovals = $businessPartnerApprovalQuery->count();

        // Only accounts explicitly created through the business registration flow belong here.
        $incompleteBusinessRegistrations = User::query()
            ->where('registration_type', 'business')
            ->where(function ($q) {
                $q->whereNull('email_verified_at')
                    ->orWhereDoesntHave('roles', fn ($role) => $role->where('name', 'program_manager'))
                    ->orWhereNull('business_super_admin_approved_at');
            })
            ->count();

        $pendingActions = [
            ['label' => 'URGENT: Business registration recovery', 'count' => $incompleteBusinessRegistrations, 'route' => 'admin.registrations.index'],
            ['label' => 'URGENT: Business partner approvals', 'count' => $pendingBusinessPartnerApprovals, 'route' => 'admin.partners.index'],
            ['label' => 'Business approvals', 'count' => $stats['pending_businesses'], 'route' => 'admin.businesses.index'], ['label' => 'Partner approvals', 'count' => $stats['pending_partners'], 'route' => 'admin.partners.index'],
            ['label' => 'Partner payouts', 'count' => Payout::whereIn('status', ['requested', 'pending'])->count(), 'route' => 'admin.payouts.index'], ['label' => 'Business payouts', 'count' => BusinessPayout::whereIn('status', ['requested', 'pending'])->count(), 'route' => 'admin.business-payouts.index'],
        ];
        $series = $this->analytics->series($from, $to, $businessId, $programId);
        $businessOptions = User::role('program_manager')->orderBy('name')->get(['id','name','business_name']);
        $programOptions = PartnershipProgram::when($businessId, fn ($q) => $q->where('owner_id', $businessId))->orderBy('name')->get(['id','name','owner_id']);
        return view('admin.dashboard', compact('stats', 'recentOrders', 'topPartners', 'programs', 'businesses', 'pendingActions', 'series', 'from', 'to', 'businessOptions', 'programOptions', 'businessId', 'programId'));
    }

    public function realtimeSales(Request $request)
    {
        $limit = min(max((int) $request->input('limit', 10), 1), 25);
        $orders = Order::query()->with(['customer', 'partner.user', 'program'])->whereIn('status', AdminAnalyticsService::PAID_STATUSES)->latest('paid_at')->latest('id')->limit($limit)->get();
        return response()->json(['data' => $orders->map(fn (Order $order) => ['id' => $order->id, 'order_number' => $order->order_number, 'amount' => (float) $order->total, 'status' => $order->status, 'paid_at' => optional($order->paid_at)->toIso8601String(), 'customer' => $order->customer?->name ?? $order->customer?->email ?? 'Customer', 'customer_email' => $order->customer?->email, 'partner' => $order->partner?->user?->name, 'program' => $order->program?->name, 'url' => route('admin.orders.show', $order)])->values(), 'timestamp' => now()->toIso8601String()]);
    }
}
