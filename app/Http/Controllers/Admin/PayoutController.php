<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = Payout::with(['partner.user', 'program'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payouts = $query->paginate(30)->withQueryString();

        $stats = [
            'requested' => Payout::where('status', 'requested')->sum('amount'),
            'approved' => Payout::where('status', 'approved')->sum('amount'),
            'paid' => Payout::where('status', 'paid')->sum('amount'),
            'rejected' => Payout::where('status', 'rejected')->sum('amount'),
        ];

        return view('admin.payouts.index', compact('payouts', 'stats'));
    }

    public function show(Payout $payout)
    {
        $payout->load(['partner.user', 'program', 'commissions.order']);
        return view('admin.payouts.show', compact('payout'));
    }

    public function approve(Payout $payout)
    {
        if ($payout->status !== 'requested') {
            return back()->with('error', 'Only requested payouts can be approved.');
        }

        $payout->update(['status' => 'approved', 'approved_at' => now()]);
        return back()->with('success', 'Payout approved.');
    }

    public function reject(Request $request, Payout $payout)
    {
        if (!in_array($payout->status, ['requested', 'approved'], true)) {
            return back()->with('error', 'This payout cannot be rejected.');
        }

        DB::transaction(function () use ($request, $payout) {
            $payout->commissions()->detach();
            $payout->update([
                'status' => 'rejected',
                'notes' => $request->input('notes') ?: $payout->notes,
            ]);
        });

        return back()->with('success', 'Payout rejected. The commissions are available for another payout request.');
    }

    public function process(Payout $payout)
    {
        if ($payout->status !== 'approved') {
            return back()->with('error', 'Only approved payouts can be processed.');
        }

        DB::transaction(function () use ($payout) {
            $lockedPayout = Payout::with('commissions')->lockForUpdate()->findOrFail($payout->id);

            if ($lockedPayout->status !== 'approved') {
                abort(422, 'Payout is no longer awaiting processing.');
            }

            foreach ($lockedPayout->commissions as $commission) {
                if ($commission->status !== 'payable') {
                    abort(422, 'A commission in this payout is no longer payable.');
                }
            }

            $lockedPayout->commissions()->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $lockedPayout->update([
                'status' => 'paid',
                'processed_at' => now(),
            ]);
        });

        return back()->with('success', 'Payout marked as paid and its commissions were settled.');
    }
}
