<?php

namespace App\Http\Controllers\Admin;

use App\Models\Commission;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommissionController extends Controller
{
    /**
     * List all commissions with filtering options.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Commission::with(['order.customer', 'partner.user', 'rule']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by level
        if ($request->has('level') && $request->level) {
            $query->where('level', $request->level);
        }

        // Filter by program
        if ($request->has('program_id') && $request->program_id) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by partner
        if ($request->has('partner_id') && $request->partner_id) {
            $query->where('partner_id', $request->partner_id);
        }

        // Filter by minimum amount
        if ($request->has('min_amount') && $request->min_amount) {
            $query->where('commission_amount', '>=', $request->min_amount);
        }

        // Filter by maximum amount
        if ($request->has('max_amount') && $request->max_amount) {
            $query->where('commission_amount', '<=', $request->max_amount);
        }

        $commissions = $query->latest()->paginate(50);

        // Get programs and partners for filters
        $programs = \App\Models\PartnershipProgram::select('id', 'name')->get();
        $partners = \App\Models\ProgramPartner::with('user')->get();
        $statuses = ['available', 'approved', 'payable', 'paid', 'reversed'];

        // Calculate stats
        $stats = [
            'total_available' => Commission::where('status', 'available')->sum('commission_amount'),
            'total_payable' => Commission::where('status', 'payable')->sum('commission_amount'),
            'total_paid' => Commission::where('status', 'paid')->sum('commission_amount'),
            'total_reversed' => Commission::where('status', 'reversed')->sum('commission_amount'),
            'count_available' => Commission::where('status', 'available')->count(),
            'count_payable' => Commission::where('status', 'payable')->count(),
        ];

        return view('admin.commissions.index', compact('commissions', 'programs', 'partners', 'statuses', 'stats'));
    }

    /**
     * Show commission details.
     * 
     * @param Commission $commission
     * @return \Illuminate\View\View
     */
    public function show(Commission $commission)
    {
        $commission->load(['order.customer', 'order.partner.user', 'partner.user', 'rule']);

        return view('admin.commissions.show', compact('commission'));
    }

    /**
     * Approve a commission for payout.
     * 
     * @param Commission $commission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Commission $commission)
    {
        if ($commission->status !== 'available') {
            return redirect()->back()->with('error', 'Only available commissions can be approved.');
        }

        $commission->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'Commission approved.');
    }

    /**
     * Mark commission as payable (ready for payout).
     * 
     * @param Commission $commission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markPayable(Commission $commission)
    {
        if (!in_array($commission->status, ['available', 'approved'])) {
            return redirect()->back()->with('error', 'Commission cannot be marked as payable.');
        }

        $commission->update(['status' => 'payable']);

        return redirect()->back()->with('success', 'Commission marked as payable.');
    }

    /**
     * Bulk approve commissions.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'commission_ids' => ['required', 'array'],
            'commission_ids.*' => ['required', 'integer'],
        ]);

        $count = Commission::whereIn('id', $validated['commission_ids'])
            ->where('status', 'available')
            ->update(['status' => 'approved']);

        return redirect()->back()->with('success', "Approved {$count} commissions.");
    }

    /**
     * Bulk mark commissions as payable.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkMarkPayable(Request $request)
    {
        $validated = $request->validate([
            'commission_ids' => ['required', 'array'],
            'commission_ids.*' => ['required', 'integer'],
        ]);

        $count = Commission::whereIn('id', $validated['commission_ids'])
            ->whereIn('status', ['available', 'approved'])
            ->update(['status' => 'payable']);

        return redirect()->back()->with('success', "Marked {$count} commissions as payable.");
    }

    /**
     * Reverse a commission.
     * 
     * @param Commission $commission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reverse(Commission $commission)
    {
        if ($commission->status === 'reversed') {
            return redirect()->back()->with('error', 'Commission is already reversed.');
        }

        if ($commission->status === 'paid') {
            return redirect()->back()->with('error', 'Cannot reverse a paid commission. Process a refund on the order instead.');
        }

        $commission->update([
            'status' => 'reversed',
            'reversed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Commission reversed.');
    }
}
