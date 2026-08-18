<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Models\User;

class BusinessController extends Controller
{
    public function index()
    {
        $businesses = User::role('program_manager')->latest()->paginate(20);
        $businesses->getCollection()->transform(function ($business) {
            $programIds = PartnershipProgram::where('owner_id', $business->id)->pluck('id');
            $business->admin_metrics = [
                'programs' => $programIds->count(),
                'partners' => ProgramPartner::whereIn('program_id', $programIds)->count(),
                'active_partners' => ProgramPartner::whereIn('program_id', $programIds)->where('status', 'active')->count(),
                'orders' => Order::whereIn('program_id', $programIds)->count(),
                'sales' => (float) Order::whereIn('program_id', $programIds)->where('status', 'paid')->sum('total'),
            ];
            return $business;
        });

        return view('admin.businesses.index', compact('businesses'));
    }

    public function approve(User $business)
    {
        abort_unless($business->hasRole('program_manager'), 404);
        $business->update(['business_super_admin_approved_at' => now(), 'business_rejected_at' => null]);
        return back()->with('success', ($business->business_name ?: $business->name) . ' has been approved to onboard as a business.');
    }

    public function reject(User $business)
    {
        abort_unless($business->hasRole('program_manager'), 404);
        $business->update(['business_super_admin_approved_at' => null, 'business_rejected_at' => now()]);
        return back()->with('success', ($business->business_name ?: $business->name) . ' has been rejected.');
    }
}
