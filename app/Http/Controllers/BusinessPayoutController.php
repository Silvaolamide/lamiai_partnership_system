<?php

namespace App\Http\Controllers;

use App\Models\BusinessPayout;
use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessPayoutController extends Controller
{
    private function ownerId(Request $request): int
    {
        return (int) $request->user()->id;
    }

    private function eligibleOrders(Request $request)
    {
        $delayDays = max(0, (int) PlatformSetting::getValue('payout_delay_days', 7));
        $adminChargePercent = min(100, max(0, (float) PlatformSetting::getValue('admin_charge_percent', 0)));
        $cutoff = now()->subDays($delayDays);
        $programIds = PartnershipProgram::where('owner_id', $this->ownerId($request))->pluck('id');

        return Order::whereIn('program_id', $programIds)
            ->where('status', 'paid')
            ->whereNull('business_payout_id')
            ->whereNotNull('paid_at')
            ->where('paid_at', '<=', $cutoff)
            ->where(function ($query) {
                $query->whereNull('refunded_at')->orWhereNull('refunded_at');
            })
            ->with(['program', 'partner.user', 'commissions'])
            ->latest('paid_at')
            ->get()
            ->filter(function (Order $order) {
                return !$order->partner_id || $order->commissions->isNotEmpty();
            })
            ->map(function (Order $order) use ($adminChargePercent) {
                $grossAmount = (float) $order->total;
                $commissionTotal = (float) $order->commissions
                    ->whereNotIn('status', ['reversed', 'cancelled'])
                    ->sum('commission_amount');
                $adminCharge = round($grossAmount * ($adminChargePercent / 100), 2);
                $order->admin_charge_percent = $adminChargePercent;
                $order->admin_charge_amount = $adminCharge;
                $order->gross_amount = $grossAmount;
                $order->business_net_amount = max(0, round($grossAmount - $commissionTotal - $adminCharge, 2));
                $order->commission_total = $commissionTotal;
                return $order;
            })
            ->filter(fn (Order $order) => $order->business_net_amount > 0)
            ->values();
    }

    public function index(Request $request)
    {
        $eligibleOrders = $this->eligibleOrders($request);
        $businessPayouts = BusinessPayout::where('business_id', $this->ownerId($request))
            ->withCount('orders')
            ->latest()
            ->paginate(20);

        $eligibleByCurrency = $eligibleOrders->groupBy('currency')->map(fn ($orders) => [
            'total' => $orders->sum('business_net_amount'),
            'orders' => $orders,
        ]);

        return view('business.payouts.index', compact('eligibleOrders', 'eligibleByCurrency', 'businessPayouts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'distinct'],
            'method' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $orders = $this->eligibleOrders($request)
                ->whereIn('id', $data['order_ids'])
                ->values();

            if ($orders->count() !== count($data['order_ids'])) {
                abort(422, 'One or more selected sales are not yet eligible for payout or have already been allocated.');
            }

            if ($orders->pluck('currency')->unique()->count() !== 1) {
                abort(422, 'A business payout can only contain sales in one currency.');
            }

            $amount = round($orders->sum('business_net_amount'), 2);
            if ($amount <= 0) {
                abort(422, 'There is no positive business balance to request.');
            }

            $payout = BusinessPayout::create([
                'business_id' => $this->ownerId($request),
                'amount' => $amount,
                'currency' => $orders->first()->currency ?? 'NGN',
                'method' => $data['method'],
                'status' => 'requested',
                'reference' => 'BPAYOUT-' . Str::upper(Str::random(12)),
                'notes' => $data['notes'] ?? null,
                'requested_at' => now(),
            ]);

            Order::whereIn('id', $orders->pluck('id'))
                ->whereNull('business_payout_id')
                ->update(['business_payout_id' => $payout->id]);
        });

        return redirect()->route('business.payouts.index')
            ->with('success', 'Business payout request submitted successfully.');
    }
}
