<?php

namespace App\Http\Controllers;

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

        $partners = ProgramPartner::with([
            'program.products',
            'program.commissionRules',
        ])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        $programStats = $partners->map(function ($partner) {
            $stats = $this->commissionService->getCommissionStats($partner);
            $orders = $partner->orders();
            $paidOrders = (clone $orders)->where('status', 'paid')->get();

            $directCommission = $partner->commissions()
                ->where('level', 1)
                ->whereIn('status', ['available', 'paid', 'pending'])
                ->sum('commission_amount');

            $recruiterCommission = $partner->commissions()
                ->where('level', '>', 1)
                ->whereIn('status', ['available', 'paid', 'pending'])
                ->sum('commission_amount');

            return [
                'partner' => $partner,
                'program' => $partner->program,
                'stats' => $stats,
                'recruited_partners_count' => $partner->childPartners()->count(),
                'total_orders' => $orders->count(),
                'paid_orders' => $paidOrders->count(),
                'paid_sales_amount' => (float) $paidOrders->sum('total'),
                'direct_commission' => (float) $directCommission,
                'recruiter_commission' => (float) $recruiterCommission,
            ];
        });

        return view('partner.dashboard', [
            'programStats' => $programStats,
            'totalPending' => (float) $programStats->sum(fn ($p) => $p['stats']['pending']),
            'totalPaid' => (float) $programStats->sum(fn ($p) => $p['stats']['paid']),
            'totalSales' => (int) $programStats->sum('paid_orders'),
            'totalSalesAmount' => (float) $programStats->sum('paid_sales_amount'),
            'totalRecruited' => (int) $programStats->sum('recruited_partners_count'),
        ]);
    }
}
