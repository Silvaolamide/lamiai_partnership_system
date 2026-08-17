<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * List all orders with filtering options.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'partner.user', 'program', 'items']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by program
        if ($request->has('program_id') && $request->program_id) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by partner
        if ($request->has('partner_id') && $request->partner_id) {
            $query->where('partner_id', $request->partner_id);
        }

        // Search by order number or customer email
        if ($request->has('search') && $request->search) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', $search)
                  ->orWhereHas('customer', function ($subQ) use ($search) {
                      $subQ->where('email', 'like', $search);
                  });
            });
        }

        $orders = $query->latest()->paginate(25);

        // Get programs and statuses for filters
        $programs = \App\Models\PartnershipProgram::select('id', 'name')->get();
        $statuses = ['pending', 'paid', 'failed', 'cancelled', 'refunded'];

        return view('admin.orders.index', compact('orders', 'programs', 'statuses'));
    }

    /**
     * Show order details.
     * 
     * @param Order $order
     * @return \Illuminate\View\View
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'partner.user', 'program', 'items.product', 'commissions.partner.user']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Mark order as paid and trigger commission generation.
     * 
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markPaid(Order $order)
    {
        // Prevent re-processing already paid orders
        if ($order->status !== 'pending') {
            return redirect()->back()->with('error', 'This order has already been processed.');
        }

        try {
            // Mark order as paid
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_reference' => 'MANUAL-' . now()->timestamp,
                'payment_provider' => 'admin_manual',
            ]);

            // Generate commissions
            $result = $this->commissionService->generateCommissionsForOrder($order);

            $message = "Order marked as paid.";
            if ($result['commissions_generated'] > 0) {
                $message .= " Generated " . $result['commissions_generated'] . " commission records.";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Failed to mark order as paid: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to process order: ' . $e->getMessage());
        }
    }

    /**
     * Mark order as failed/cancelled.
     * 
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:failed,cancelled'],
            'reason' => ['nullable', 'string'],
        ]);

        if ($order->status === 'paid') {
            return redirect()->back()->with('error', 'Cannot cancel a paid order. Refund instead.');
        }

        $order->update([
            'status' => $validated['status'],
        ]);

        return redirect()->back()->with('success', 'Order ' . $validated['status'] . ' successfully.');
    }

    /**
     * Refund a paid order and reverse commissions.
     * 
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refund(Request $request, Order $order)
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string'],
        ]);

        if ($order->status !== 'paid') {
            return redirect()->back()->with('error', 'Only paid orders can be refunded.');
        }

        try {
            $order->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);

            // Mark all commissions as reversed
            $order->commissions()->update([
                'status' => 'reversed',
                'reversed_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Order refunded and commissions reversed.');

        } catch (\Exception $e) {
            \Log::error('Failed to refund order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to refund order: ' . $e->getMessage());
        }
    }
}
