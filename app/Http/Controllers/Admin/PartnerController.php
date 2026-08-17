<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramPartner;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = ProgramPartner::with([
            'user',
            'program',
            'parentPartner.user',
        ])
        ->latest()
        ->paginate(20);

        return view('admin.partners.index', compact('partners'));
    }

    public function approve(ProgramPartner $partner)
    {
        if ($partner->status !== 'pending') {
            return back()->with('error', 'This application has already been processed.');
        }

        $partner->update([
            'status' => 'active',
            'partner_code' => $this->generatePartnerCode(),
            'approved_at' => now(),
        ]);

        return back()->with(
            'success',
            $partner->user->name . ' has been approved as a partner.'
        );
    }

    public function reject(ProgramPartner $partner)
    {
        if ($partner->status !== 'pending') {
            return back()->with('error', 'This application has already been processed.');
        }

        $partner->update([
            'status' => 'rejected',
        ]);

        return back()->with(
            'success',
            $partner->user->name . ' has been rejected.'
        );
    }

    private function generatePartnerCode(): string
    {
        do {
            $code = 'LAMI-' . Str::upper(Str::random(8));
        } while (
            ProgramPartner::where('partner_code', $code)->exists()
        );

        return $code;
    }
}