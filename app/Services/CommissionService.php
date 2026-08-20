<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\PlatformSetting;
use App\Models\ProgramPartner;
use Illuminate\Support\Facades\DB;
use Exception;

class CommissionService
{
    public function generateCommissionsForOrder(Order $order)
    {
        if ($order->status !== 'paid') {
            throw new Exception('Order must be marked as paid before generating commissions.');
        }

        return DB::transaction(function () use ($order) {
            if ($order->commissions()->exists()) {
                return [
                    'success' => true,
                    'status' => 'already_exists',
                    'message' => 'Commissions already generated for this order',
                    'order_id' => $order->id,
                    'commissions_generated' => 0,
                    'commissions' => [],
                    'total_amount' => 0,
                    'total_commission' => 0,
                ];
            }

            // Snapshot the platform/admin charge at the moment the sale is completed.
            // This keeps the business payout and platform revenue deterministic even if
            // the administrator changes the fee percentage later.
            app(PlatformFeeService::class)->applyToOrder($order);
            $order->refresh();

            if (!$order->program_id || !$order->partner_id) {
                return [
                    'success' => true,
                    'status' => 'no_partner',
                    'message' => 'No attributed partner/program; no partner commissions generated.',
                    'order_id' => $order->id,
                    'commissions_generated' => 0,
                    'commissions' => [],
                    'total_amount' => 0,
                    'total_commission' => 0,
                ];
            }

            $order->loadMissing(['partner.user', 'partner.parentPartner', 'items.product']);

            // A partner may purchase a product, but must never earn commission from their own purchase.
            if ($order->partner && $order->customer_id && (int) $order->partner->user_id === (int) $order->customer_id) {
                return [
                    'success' => true,
                    'status' => 'self_purchase',
                    'message' => 'Partner self-purchase detected; no commissions generated.',
                    'order_id' => $order->id,
                    'commissions_generated' => 0,
                    'commissions' => [],
                    'total_amount' => 0,
                    'total_commission' => 0,
                ];
            }

            $commissions = [];

            $directCommission = $this->generateDirectCommission($order);
            if ($directCommission) {
                $commissions[] = $directCommission;
            }

            $commissions = array_merge($commissions, $this->generateHierarchyCommissions($order));

            $totalAmount = round(
                collect($commissions)->sum(fn ($commission) => (float) $commission->commission_amount),
                2
            );

            return [
                'success' => true,
                'status' => 'success',
                'order_id' => $order->id,
                'commissions_generated' => count($commissions),
                'commissions' => $commissions,
                'total_amount' => $totalAmount,
                'total_commission' => $totalAmount,
                'platform_fee_amount' => (float) $order->platform_fee_amount,
            ];
        });
    }

    private function commissionAvailableAt(Order $order)
    {
        $delayDays = max(0, (int) PlatformSetting::getValue('payout_delay_days', 7));
        $base = $order->paid_at ?: now();

        return $base->copy()->addDays($delayDays);
    }

    private function generateDirectCommission(Order $order)
    {
        $rule = $this->resolveRule($order, 1);
        if (!$rule) return null;

        return Commission::create([
            'program_id' => $order->program_id,
            'order_id' => $order->id,
            'partner_id' => $order->partner_id,
            'source_partner_id' => null,
            'rule_id' => $rule->id,
            'level' => 1,
            'commission_type' => $rule->commission_type,
            'rate' => $rule->value,
            'base_amount' => $order->total,
            'commission_amount' => $this->calculateCommission($order->total, $rule),
            'status' => 'available',
            'available_at' => $this->commissionAvailableAt($order),
        ]);
    }

    private function generateHierarchyCommissions(Order $order)
    {
        $commissions = [];
        $currentPartner = $order->partner;
        $level = 2;

        while ($currentPartner && $currentPartner->parent_partner_id) {
            $parentPartner = $currentPartner->parentPartner;
            if (!$parentPartner || $parentPartner->status !== 'active') break;

            $rule = $this->resolveRule($order, $level);
            if (!$rule) break;

            $commissions[] = Commission::create([
                'program_id' => $order->program_id,
                'order_id' => $order->id,
                'partner_id' => $parentPartner->id,
                'source_partner_id' => $order->partner_id,
                'rule_id' => $rule->id,
                'level' => $level,
                'commission_type' => $rule->commission_type,
                'rate' => $rule->value,
                'base_amount' => $order->total,
                'commission_amount' => $this->calculateCommission($order->total, $rule),
                'status' => 'available',
                'available_at' => $this->commissionAvailableAt($order),
            ]);

            $currentPartner = $parentPartner;
            $level++;
        }

        return $commissions;
    }

    private function resolveRule(Order $order, int $level): ?CommissionRule
    {
        $productId = $order->items->first()?->product_id;

        if ($productId) {
            $productRule = CommissionRule::where('program_id', $order->program_id)
                ->where('product_id', $productId)
                ->where('level', $level)
                ->where('status', true)
                ->where('event', 'sale')
                ->orderByDesc('priority')
                ->first();
            if ($productRule) return $productRule;
        }

        return CommissionRule::where('program_id', $order->program_id)
            ->whereNull('product_id')
            ->where('level', $level)
            ->where('status', true)
            ->where('event', 'sale')
            ->orderByDesc('priority')
            ->first();
    }

    private function calculateCommission($baseAmount, CommissionRule $rule): float
    {
        $commissionAmount = $rule->commission_type === 'fixed'
            ? (float) $rule->value
            : ((float) $baseAmount * (float) $rule->value) / 100;

        if ($rule->maximum_amount !== null) {
            $commissionAmount = min($commissionAmount, (float) $rule->maximum_amount);
        }

        return round(max(0, $commissionAmount), 2);
    }

    public function getPendingCommissionAmount(ProgramPartner $partner)
    {
        return (float) Commission::where('partner_id', $partner->id)
            ->whereIn('status', ['available', 'approved', 'payable'])
            ->sum('commission_amount');
    }

    public function getPaidCommissionAmount(ProgramPartner $partner)
    {
        return (float) Commission::where('partner_id', $partner->id)
            ->where('status', 'paid')
            ->sum('commission_amount');
    }

    public function getTotalCommissionAmount(ProgramPartner $partner)
    {
        return (float) Commission::where('partner_id', $partner->id)
            ->whereIn('status', ['available', 'approved', 'payable', 'paid', 'pending', 'reversed'])
            ->sum('commission_amount');
    }

    public function getCommissionStats(ProgramPartner $partner)
    {
        $pending = $this->getPendingCommissionAmount($partner);
        $paid = $this->getPaidCommissionAmount($partner);
        $reversed = (float) Commission::where('partner_id', $partner->id)->where('status', 'reversed')->sum('commission_amount');
        $total = $this->getTotalCommissionAmount($partner);

        return [
            'pending' => $pending,
            'paid' => $paid,
            'available' => $pending,
            'reversed' => $reversed,
            'total' => $total,
            'pending_count' => Commission::where('partner_id', $partner->id)->whereIn('status', ['available', 'approved', 'payable'])->count(),
            'paid_count' => Commission::where('partner_id', $partner->id)->where('status', 'paid')->count(),
            'reversed_count' => Commission::where('partner_id', $partner->id)->where('status', 'reversed')->count(),
        ];
    }
}
