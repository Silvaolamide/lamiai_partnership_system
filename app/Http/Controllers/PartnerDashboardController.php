<?php

namespace App\Http\Controllers;

use App\Models\ProgramPartner;
use Illuminate\Support\Facades\Auth;

class PartnerDashboardController extends Controller
{
    public function index()
    {
        $partners = ProgramPartner::with('program')
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->get();

        return view('partner.dashboard', compact('partners'));
    }
}