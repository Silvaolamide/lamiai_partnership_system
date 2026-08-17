<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessPayout;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessPayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = BusinessPayout::with('business')->withCount('orders')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $payouts = $query->paginate(30)->withQueryString();

        $stats = [
            'requested' => BusinessPayout::whereIn('status', ['requested', 'approved', 'processing'])->sum('amount'),
            'paid' => BusinessPayout::whereIn('status', ['processed', 'paid'])->sum('amount'),
            'count_pending' => BusinessPayout::whereIn('status', ['requested', 'approved', 'processing'])->count(),
        ];

        return view('admin.business-payouts.index', compact('payouts', 'stats'));
    }

    public function show(BusinessPayout $businessPayout)
    {
        $businessPayout->load(['business', 'orders.program', 'orders.partner.user', 'orders.commissions']);
        return view('admin.business-payouts.show', compact('businessPayout'));
    }

    public function approve(BusinessPayout $businessPayout)
    {
        if ($businessPayout->status !== 'requested') {
            return back()->with('error', 'Only requested business payouts can be approved.');
        }

        $businessPayout->update(['status' => 'approved', 'approved_at' => now()]);
        return back()->with('success', 'Business payout approved.');
    }

    public function reject(BusinessPayout $businessPayout)
    {
        if (!in_array($businessPayout->status, ['requested', 'approved'], true)) {
            return back()->with('error', 'This business payout cannot be rejected.');
        }

        DB::transaction(function () use ($businessPayout) {
            $businessPayout->orders()->update(['business_payout_id' => null]);
            $businessPayout->update(['status' => 'rejected']);
        });

        return back()->with('success', 'Business payout rejected and its sales returned to the available balance.');
    }

    public function process(BusinessPayout $businessPayout)
    {
        if (!in_array($businessPayout->status, ['approved', 'processing'], true)) {
            return back()->with('error', 'Only approved business payouts can be processed.');
        }

        $businessPayout->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Business payout marked as processed.');
    }
}
