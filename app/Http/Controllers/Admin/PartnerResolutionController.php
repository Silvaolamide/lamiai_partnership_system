<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\ProgramPartner;
use App\Models\User;

class PartnerResolutionController extends Controller
{
    public function show(User $user)
    {
        abort_if($user->hasRole('super_admin'), 403, 'Super Admin accounts are protected from partner-management operations.');

        $partners = ProgramPartner::with(['program', 'user', 'parentPartner.user'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $partnerIds = $partners->pluck('id');
        $commissions = Commission::with(['program', 'order.customer', 'partner.user', 'rule'])
            ->whereIn('partner_id', $partnerIds)
            ->latest()
            ->paginate(25);

        $stats = [
            'programs' => $partners->count(),
            'active' => $partners->where('status', 'active')->count(),
            'pending' => $partners->where('status', 'pending')->count(),
            'available' => Commission::whereIn('partner_id', $partnerIds)->where('status', 'available')->sum('commission_amount'),
            'approved' => Commission::whereIn('partner_id', $partnerIds)->where('status', 'approved')->sum('commission_amount'),
            'payable' => Commission::whereIn('partner_id', $partnerIds)->where('status', 'payable')->sum('commission_amount'),
            'paid' => Commission::whereIn('partner_id', $partnerIds)->where('status', 'paid')->sum('commission_amount'),
            'reversed' => Commission::whereIn('partner_id', $partnerIds)->where('status', 'reversed')->sum('commission_amount'),
        ];

        return view('admin.users.partner-resolution', compact('user', 'partners', 'commissions', 'stats'));
    }
}
