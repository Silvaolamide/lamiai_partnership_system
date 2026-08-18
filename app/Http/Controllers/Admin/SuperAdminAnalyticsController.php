<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessPayout;
use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Models\User;
use App\Services\AdminAnalyticsService;
use Illuminate\Http\Request;

class SuperAdminAnalyticsController extends Controller
{
    public function __construct(private readonly AdminAnalyticsService $analytics) {}

    public function businesses(Request $request)
    {
        $businesses = User::role('program_manager')->latest()->limit(100)->get()->map(function ($business) {
            $programIds = PartnershipProgram::where('owner_id', $business->id)->pluck('id');
            $partnerIds = ProgramPartner::whereIn('program_id', $programIds)->pluck('user_id')->unique();
            $paid = Order::whereIn('program_id', $programIds)->whereIn('status', AdminAnalyticsService::PAID_STATUSES);
            $business->analytics = [
                'programs' => $programIds->count(),
                'partners' => $partnerIds->count(),
                'active_partners' => ProgramPartner::whereIn('program_id', $programIds)->where('status', 'active')->count(),
                'orders' => (clone $paid)->count(),
                'sales' => (float) (clone $paid)->sum('total'),
            ];
            return $business;
        });

        return view('admin.analytics.businesses', compact('businesses'));
    }

    public function business(Request $request, User $business)
    {
        abort_unless($business->hasRole('program_manager'), 404);

        $programs = PartnershipProgram::where('owner_id', $business->id)
            ->withCount(['partners', 'products', 'orders'])
            ->with(['commissionRules'])
            ->latest()->get();
        $programIds = $programs->pluck('id');

        $partners = ProgramPartner::whereIn('program_id', $programIds)
            ->with(['user', 'parentPartner.user', 'program'])
            ->withCount('children')->latest()->limit(100)->get();

        $ordersQuery = Order::whereIn('program_id', $programIds);
        $paidOrdersQuery = (clone $ordersQuery)->whereIn('status', AdminAnalyticsService::PAID_STATUSES);
        $orders = (clone $ordersQuery)->with(['customer', 'partner.user', 'program'])->latest()->limit(100)->get();
        $commissions = Commission::whereIn('program_id', $programIds)
            ->whereNotIn('status', ['reversed', 'cancelled'])
            ->with(['partner.user', 'program', 'rule'])
            ->latest()->limit(100)->get();
        $payouts = BusinessPayout::where('business_id', $business->id)->latest()->limit(100)->get();

        $customerIds = (clone $ordersQuery)->whereNotNull('customer_id')->distinct()->pluck('customer_id');
        $customerEmails = (clone $ordersQuery)->whereNotNull('customer_email')->distinct()->pluck('customer_email');
        $customers = User::whereIn('id', $customerIds)->orWhereIn('email', $customerEmails)->latest()->limit(100)->get();

        $gross = (float) (clone $paidOrdersQuery)->sum('total');
        $commissionTotal = (float) Commission::whereIn('program_id', $programIds)
            ->whereIn('status', ['approved', 'payable', 'paid'])
            ->sum('commission_amount');
        $refunded = (float) (clone $ordersQuery)->where('status', 'refunded')->sum('total');
        $paidOrderCount = (clone $paidOrdersQuery)->count();
        $net = max(0, $gross - $commissionTotal);

        $stats = [
            'programs' => $programs->count(),
            'active_programs' => $programs->where('status', 'active')->count(),
            'partners' => ProgramPartner::whereIn('program_id', $programIds)->count(),
            'active_partners' => ProgramPartner::whereIn('program_id', $programIds)->where('status', 'active')->count(),
            'recruited_partners' => ProgramPartner::whereIn('program_id', $programIds)->whereNotNull('parent_partner_id')->count(),
            'customers' => $customers->count(),
            'orders' => (clone $ordersQuery)->count(),
            'paid_orders' => $paidOrderCount,
            'gross_sales' => $gross,
            'commissions' => $commissionTotal,
            'net_revenue' => $net,
            'refunded' => $refunded,
            'average_order' => $paidOrderCount ? $gross / $paidOrderCount : 0,
            'payouts_requested' => (float) BusinessPayout::where('business_id', $business->id)->sum('amount'),
            'payouts_paid' => (float) BusinessPayout::where('business_id', $business->id)->where('status', 'paid')->sum('amount'),
        ];

        $recentActivity = (clone $ordersQuery)->with(['customer', 'partner.user', 'program'])->latest()->limit(20)->get()->map(fn ($order) => [
            'type' => 'sale', 'label' => 'Sale', 'description' => ($order->customer?->name ?? $order->customer_name ?? $order->customer_email ?? 'Customer') . ' purchased via ' . ($order->partner?->user?->name ?? 'direct'), 'date' => $order->created_at,
        ]);

        return view('admin.analytics.business', compact(
            'business', 'programs', 'partners', 'orders', 'commissions', 'payouts', 'customers', 'stats', 'recentActivity'
        ));
    }

    public function partner(Request $request, User $business, ProgramPartner $programPartner)
    {
        abort_unless($business->hasRole('program_manager'), 404);
        abort_unless($programPartner->program && $programPartner->program->owner_id === $business->id, 404);

        $programPartner->load(['user', 'program', 'parentPartner.user']);
        $recruited = ProgramPartner::where('parent_partner_id', $programPartner->id)->with(['user', 'program'])->limit(100)->get();
        $orders = Order::where('program_id', $programPartner->program_id)->where('partner_id', $programPartner->id)->whereIn('status', AdminAnalyticsService::PAID_STATUSES)->with(['customer', 'program'])->latest()->limit(100)->get();

        return view('admin.analytics.partner', compact('business', 'programPartner', 'recruited', 'orders'));
    }
}
