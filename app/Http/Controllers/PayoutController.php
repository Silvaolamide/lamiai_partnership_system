<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Payout;
use App\Models\ProgramPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayoutController extends Controller
{
    public function index()
    {
        $partners = ProgramPartner::where('user_id', Auth::id())->with('program')->get();
        $partnerIds = $partners->pluck('id');

        $payable = Commission::whereIn('partner_id', $partnerIds)
            ->where('status', 'payable')
            ->with(['program', 'order'])
            ->orderBy('created_at')
            ->get();

        $payouts = Payout::whereIn('partner_id', $partnerIds)
            ->with('program')
            ->latest()
            ->paginate(20);

        $payableByProgram = $payable->groupBy('program_id')->map(function ($commissions) {
            return [
                'program' => $commissions->first()->program,
                'total' => $commissions->sum('commission_amount'),
                'commissions' => $commissions,
            ];
        });

        return view('partner.payouts.index', compact('partners', 'payable', 'payableByProgram', 'payouts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'commission_ids' => ['required', 'array', 'min:1'],
            'commission_ids.*' => ['integer', 'distinct'],
            'method' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $partnerIds = ProgramPartner::where('user_id', Auth::id())->pluck('id');

        DB::transaction(function () use ($validated, $partnerIds) {
            $commissions = Commission::whereIn('id', $validated['commission_ids'])
                ->whereIn('partner_id', $partnerIds)
                ->where('status', 'payable')
                ->with('order')
                ->lockForUpdate()
                ->get();

            if ($commissions->count() !== count($validated['commission_ids'])) {
                abort(422, 'One or more selected commissions are no longer payable.');
            }

            if ($commissions->pluck('program_id')->unique()->count() !== 1) {
                abort(422, 'A payout request can only contain commissions from one partnership program.');
            }

            if ($commissions->pluck('partner_id')->unique()->count() !== 1) {
                abort(422, 'A payout request can only contain commissions for one partner.');
            }

            $first = $commissions->first();
            $amount = $commissions->sum('commission_amount');

            $payout = Payout::create([
                'partner_id' => $first->partner_id,
                'program_id' => $first->program_id,
                'amount' => $amount,
                'currency' => $first->order?->currency ?? 'NGN',
                'method' => $validated['method'],
                'status' => 'requested',
                'reference' => 'PAYOUT-' . Str::upper(Str::random(12)),
                'notes' => $validated['notes'] ?? null,
                'requested_at' => now(),
            ]);

            $payout->commissions()->attach($commissions->pluck('id'));
        });

        return redirect()->route('partner.payouts.index')
            ->with('success', 'Payout request submitted successfully.');
    }
}
