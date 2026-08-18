<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Models\User;
use Illuminate\Http\Request;

class SuperAdminAnalyticsController extends Controller
{
    public function businesses(Request $request)
    {
        $businesses = User::role('business')
            ->withCount(['ownedPrograms as programs_count'])
            ->with(['ownedPrograms' => function ($q) {
                $q->withCount('programPartners');
            }])
            ->get()
            ->map(function ($business) {
                $programs = $business->ownedPrograms;
                $partnerIds = $programs->flatMap(fn ($program) => $program->programPartners->pluck('user_id'))->unique();
                $business->analytics = [
                    'programs' => $programs->count(),
                    'partners' => $partnerIds->count(),
                    'sales' => Order::whereIn('program_id', $programs->pluck('id'))->where('status', 'paid')->sum('total'),
                    'orders' => Order::whereIn('program_id', $programs->pluck('id'))->where('status', 'paid')->count(),
                ];
                return $business;
            });

        return view('admin.analytics.businesses', compact('businesses'));
    }

    public function business(Request $request, User $business)
    {
        abort_unless($business->hasRole('business'), 404);

        $programs = $business->ownedPrograms()->withCount('programPartners')->get();
        $programIds = $programs->pluck('id');
        $programPartners = ProgramPartner::whereIn('program_id', $programIds)->with(['user', 'parentPartner.user', 'program'])->get();
        $orders = Order::whereIn('program_id', $programIds)->where('status', 'paid')->with(['customer', 'partner', 'program'])->latest()->get();

        $stats = [
            'programs' => $programs->count(),
            'partners' => $programPartners->count(),
            'orders' => $orders->count(),
            'gross_sales' => $orders->sum('total'),
            'net_revenue' => $orders->sum(function ($order) {
                return $order->total - ($order->commissions?->sum('amount') ?? 0);
            }),
        ];

        return view('admin.analytics.business', compact('business', 'programs', 'programPartners', 'orders', 'stats'));
    }

    public function partner(Request $request, User $business, ProgramPartner $programPartner)
    {
        abort_unless($business->hasRole('business'), 404);
        abort_unless($programPartner->program && $programPartner->program->owner_id === $business->id, 404);

        $programPartner->load(['user', 'program', 'parentPartner.user']);
        $recruited = ProgramPartner::where('parent_partner_id', $programPartner->id)->with(['user', 'program'])->get();
        $orders = Order::where('program_id', $programPartner->program_id)->where('partner_id', $programPartner->id)->where('status', 'paid')->with(['customer', 'program'])->latest()->get();

        return view('admin.analytics.partner', compact('business', 'programPartner', 'recruited', 'orders'));
    }
}
