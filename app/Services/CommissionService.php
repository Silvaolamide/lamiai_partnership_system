<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Commission;
use App\Models\CommissionRule;
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
                    'status' => 'already_exists',
                    'message' => 'Commissions already generated for this order',
                    'commissions' => $order->commissions()->get(),
                ];
            }

            if (!$order->program_id || !$order->partner_id) {
                return [
                    'status' => 'no_partner',
                    'message' => 'No attributed partner/program; no partner commissions generated.',
                    'commissions_generated' => 0,
                    'commissions' => [],
                    'total_commission' => 0,
                ];
            }

            $order->loadMissing(['partner.parentPartner', 'items.product']);
            $commissions = [];

            $directCommission = $this->generateDirectCommission($order);
            if ($directCommission) {
                $commissions[] = $directCommission;
            }

            $commissions = array_merge(
                $commissions,
                $this->generateHierarchyCommissions($order)
            );

            return [
                'status' => 'success',
                'order_id' => $order->id,
                'commissions_generated' => count($commissions),
                'commissions' => $commissions,
                'total_commission' => collect($commissions)->sum(fn ($commission) => (float) $commission->commission_amount),
            ];
        });
    }

    private function generateDirectCommission(Order $order)
    {
        $rule = $this->resolveRule($order, 1);

        if (!$rule) {
            return null;
        }

        $commissionAmount = $this->calculateCommission($order->total, $rule);

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
            'commission_amount' => $commissionAmount,
            'status' => 'available',
            'available_at' => now(),
        ]);
    }

    private function generateHierarchyCommissions(Order $order)
    {
        $commissions = [];
        $currentPartner = $order->partner;
        $level = 2;

        while ($currentPartner && $currentPartner->parent_partner_id) {
            $parentPartner = $currentPartner->parentPartner;

            if (!$parentPartner || $parentPartner->status !== 'active') {
                break;
            }

            $rule = $this->resolveRule($order, $level);

            if (!$rule) {
                break;
            }

            $commissionAmount = $this->calculateCommission($order->total, $rule);

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
                'commission_amount' => $commissionAmount,
                'status' => 'available',
                'available_at' => now(),
            ]);

            $currentPartner = $parentPartner;
            $level++;
        }

        return $commissions;
    }

    /**
     * Prefer a product-specific rule. Fall back to the program-wide rule.
     * Higher priority wins within either scope.
     */
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

            if ($productRule) {
                return $productRule;
            }
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
            ->where('status', 'available')
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
            ->whereIn('status', ['available', 'paid', 'pending'])
            ->sum('commission_amount');
    }

    public function getCommissionStats(ProgramPartner $partner)
    {
        $pending = $this->getPendingCommissionAmount($partner);
        $paid = $this->getPaidCommissionAmount($partner);
        $total = $this->getTotalCommissionAmount($partner);

        return [
            'pending' => $pending,
            'paid' => $paid,
            'available' => $pending,
            'total' => $total,
            'pending_count' => Commission::where('partner_id', $partner->id)
                ->where('status', 'available')
                ->count(),
            'paid_count' => Commission::where('partner_id', $partner->id)
                ->where('status', 'paid')
                ->count(),
        ];
    }
}
