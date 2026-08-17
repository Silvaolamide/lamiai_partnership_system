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
                    'success' => true,
                    'message' => 'Commissions already generated for this order',
                    'order_id' => $order->id,
                    'commissions_generated' => 0,
                    'total_amount' => 0,
                    'total_commission' => 0,
                    'commissions' => $order->commissions()->get(),
                ];
            }

            $commissions = [];

            if ($order->partner_id) {
                $directCommission = $this->generateDirectCommission($order);
                if ($directCommission) {
                    $commissions[] = $directCommission;
                }

                $commissions = array_merge(
                    $commissions,
                    $this->generateHierarchyCommissions($order)
                );
            }

            $totalAmount = array_sum(array_map(
                fn (Commission $commission) => (float) $commission->commission_amount,
                $commissions
            ));

            return [
                'status' => 'success',
                'success' => true,
                'order_id' => $order->id,
                'commissions_generated' => count($commissions),
                'total_amount' => $totalAmount,
                'total_commission' => $totalAmount,
                'commissions' => $commissions,
            ];
        });
    }

    private function generateDirectCommission(Order $order)
    {
        $rule = CommissionRule::where('program_id', $order->program_id)
            ->where('level', 1)
            ->where('status', true)
            ->where('event', 'sale')
            ->first();

        if (!$rule) {
            return null;
        }

        $baseAmount = $order->total;
        $rate = $rule->value;
        $commissionAmount = ($baseAmount * $rate) / 100;

        if ($rule->maximum_amount && $commissionAmount > $rule->maximum_amount) {
            $commissionAmount = $rule->maximum_amount;
        }

        return Commission::create([
            'program_id' => $order->program_id,
            'order_id' => $order->id,
            'partner_id' => $order->partner_id,
            'source_partner_id' => null,
            'rule_id' => $rule->id,
            'level' => 1,
            'commission_type' => $rule->commission_type,
            'rate' => $rate,
            'base_amount' => $baseAmount,
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

            if (!$parentPartner) {
                break;
            }

            $rule = CommissionRule::where('program_id', $order->program_id)
                ->where('level', $level)
                ->where('status', true)
                ->where('event', 'sale')
                ->first();

            if (!$rule) {
                break;
            }

            $baseAmount = $order->total;
            $rate = $rule->value;
            $commissionAmount = ($baseAmount * $rate) / 100;

            if ($rule->maximum_amount && $commissionAmount > $rule->maximum_amount) {
                $commissionAmount = $rule->maximum_amount;
            }

            $commissions[] = Commission::create([
                'program_id' => $order->program_id,
                'order_id' => $order->id,
                'partner_id' => $parentPartner->id,
                'source_partner_id' => $order->partner_id,
                'rule_id' => $rule->id,
                'level' => $level,
                'commission_type' => $rule->commission_type,
                'rate' => $rate,
                'base_amount' => $baseAmount,
                'commission_amount' => $commissionAmount,
                'status' => 'available',
                'available_at' => now(),
            ]);

            $currentPartner = $parentPartner;
            $level++;
        }

        return $commissions;
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
            ->whereIn('status', ['available', 'paid', 'pending', 'reversed'])
            ->sum('commission_amount');
    }

    public function getCommissionStats(ProgramPartner $partner)
    {
        $pending = $this->getPendingCommissionAmount($partner);
        $paid = $this->getPaidCommissionAmount($partner);
        $reversed = (float) Commission::where('partner_id', $partner->id)
            ->where('status', 'reversed')
            ->sum('commission_amount');
        $total = $this->getTotalCommissionAmount($partner);

        return [
            'pending' => $pending,
            'paid' => $paid,
            'reversed' => $reversed,
            'available' => $pending,
            'total' => $total,
            'pending_count' => Commission::where('partner_id', $partner->id)
                ->where('status', 'available')
                ->count(),
            'paid_count' => Commission::where('partner_id', $partner->id)
                ->where('status', 'paid')
                ->count(),
            'reversed_count' => Commission::where('partner_id', $partner->id)
                ->where('status', 'reversed')
                ->count(),
        ];
    }
}
