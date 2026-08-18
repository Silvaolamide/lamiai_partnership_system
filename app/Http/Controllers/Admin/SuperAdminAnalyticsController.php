<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessPayout;
use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Models\User;
use Illuminate\Http\Request;

class SuperAdminAnalyticsController extends Controller
{
    public function businesses(Request $request)
    {
        $businesses = User::role('program_manager')->latest()->get()->map(function ($business) {
            $programIds = PartnershipProgram::where('owner_id', $business->id)->pluck('id');
            $partnerIds = ProgramPartner::whereIn('program_id', $programIds)->pluck('user_id')->unique();
            $paid = Order::whereIn('program_id', $programIds)->where('status', 'paid');
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
        // In this application a signed-up business uses the program_manager role.
        abort_unless($business->hasRole('program_manager'), 404);

        $programs = PartnershipProgram::where('owner_id', $business->id)
            ->withCount(['partners', 'products', 'orders'])
            ->with(['commissionRules'])
            ->latest()->get();
        $programIds = $programs->pluck('id');

        $partners = ProgramPartner::whereIn('program_id', $programIds)
            ->with(['user', 'parentPartner.user', 'program'])
            ->withCount('children')
            ->latest()->get();

        $orders = Order::whereIn('program_id', $programIds)
            ->with(['customer', 'partner.user', 'program', 'commissions'])
            ->latest()->get();
        $paidOrders = $orders->where('status', 'paid');
        $commissions = Commission::whereIn('program_id', $programIds)
            ->with(['partner.user', 'program', 'rule'])
            ->latest()->get();
        $payouts = BusinessPayout::where('business_id', $business->id)->latest()->get();

        $customerIds = $orders->pluck('customer_id')->filter()->unique();
        $customerEmails = $orders->pluck('customer_email')->filter()->unique();
        $customers = User::whereIn('id', $customerIds)->orWhereIn('email', $customerEmails)->latest()->get();

        $gross = (float) $paidOrders->sum('total');
        $commissionTotal = (float) $commissions->whereIn('status', ['approved', 'payable', 'paid'])->sum('amount');
        $refunded = (float) $orders->where('status', 'refunded')->sum('total');
        $net = $gross - $commissionTotal;

        $stats = [
            'programs' => $programs->count(),
            'active_programs' => $programs->where('status', 'active')->count(),
            'partners' => $partners->count(),
            'active_partners' => $partners->where('status', 'active')->count(),
            'recruited_partners' => $partners->whereNotNull('parent_partner_id')->count(),
            'customers' => $customers->count(),
            'orders' => $orders->count(),
            'paid_orders' => $paidOrders->count(),
            'gross_sales' => $gross,
            'commissions' => $commissionTotal,
            'net_revenue' => $net,
            'refunded' => $refunded,
            'average_order' => $paidOrders->count() ? $gross / $paidOrders->count() : 0,
            'payouts_requested' => (float) $payouts->sum('amount'),
            'payouts_paid' => (float) $payouts->where('status', 'paid')->sum('amount'),
        ];

        $recentActivity = $orders->take(20)->map(fn ($order) => [
            'type' => 'sale', 'label' => 'Sale', 'description' => ($order->customer?->name ?? $order->customer_name ?? $order->customer_email ?? 'Customer') . ' purchased via ' . ($order->partner?->user?->name ?? 'direct'), 'date' => $order->created_at,
        ]);

        return view('admin.analytics.business', compact(
            'business', 'programs', 'partners', 'orders', 'paidOrders', 'commissions', 'payouts', 'customers', 'stats', 'recentActivity'
        ));
    }

    public function partner(Request $request, User $business, ProgramPartner $programPartner)
    {
        abort_unless($business->hasRole('program_manager'), 404);
        abort_unless($programPartner->program && $programPartner->program->owner_id === $business->id, 404);

        $programPartner->load(['user', 'program', 'parentPartner.user']);
        $recruited = ProgramPartner::where('parent_partner_id', $programPartner->id)->with(['user', 'program'])->get();
        $orders = Order::where('program_id', $programPartner->program_id)->where('partner_id', $programPartner->id)->where('status', 'paid')->with(['customer', 'program'])->latest()->get();

        return view('admin.analytics.partner', compact('business', 'programPartner', 'recruited', 'orders'));
    }
}
