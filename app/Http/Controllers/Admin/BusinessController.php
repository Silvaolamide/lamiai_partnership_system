<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class BusinessController extends Controller
{
    public function index()
    {
        $businesses = User::role('program_manager')
            ->latest()
            ->paginate(20);

        return view('admin.businesses.index', compact('businesses'));
    }

    public function approve(User $business)
    {
        abort_unless($business->hasRole('program_manager'), 404);

        $business->update([
            'business_super_admin_approved_at' => now(),
            'business_rejected_at' => null,
        ]);

        return back()->with('success', ($business->business_name ?: $business->name) . ' has been approved to onboard as a business.');
    }

    public function reject(User $business)
    {
        abort_unless($business->hasRole('program_manager'), 404);

        $business->update([
            'business_super_admin_approved_at' => null,
            'business_rejected_at' => now(),
        ]);

        return back()->with('success', ($business->business_name ?: $business->name) . ' has been rejected.');
    }
}
