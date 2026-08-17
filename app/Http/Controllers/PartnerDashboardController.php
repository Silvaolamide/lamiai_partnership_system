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
        
        // Get all active programs for this user
        $partners = ProgramPartner::with('program')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        // Collect statistics for each partner/program
        $programStats = [];
        foreach ($partners as $partner) {
            $stats = $this->commissionService->getCommissionStats($partner);
            
            $programStats[] = [
                'partner' => $partner,
                'program' => $partner->program,
                'stats' => $stats,
                'recruited_partners_count' => $partner->childPartners()->count(),
                'total_orders' => $partner->orders()->count(),
                'paid_orders' => $partner->orders()->where('status', 'paid')->count(),
            ];
        }

        return view('partner.dashboard', [
            'programStats' => $programStats,
            'totalPending' => array_sum(array_map(fn($p) => $p['stats']['pending'], $programStats)) ?? 0,
            'totalPaid' => array_sum(array_map(fn($p) => $p['stats']['paid'], $programStats)) ?? 0,
        ]);
    }
}
