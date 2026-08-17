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
    /**
     * Generate commissions for a paid order.
     * 
     * This method:
     * 1. Loads commission rules for the order's program
     * 2. Calculates commissions for all eligible partners (direct + parent hierarchy)
     * 3. Creates commission records in database
     * 4. Prevents duplicate commission generation (idempotent)
     * 
     * Must be called only after order payment is confirmed.
     * 
     * @param Order $order The paid order
     * @return array Returns array with commission details
     * @throws Exception
     */
    public function generateCommissionsForOrder(Order $order)
    {
        // Prevent processing if order is not paid
        if ($order->status !== 'paid') {
            throw new Exception('Order must be marked as paid before generating commissions.');
        }

        // Use database transaction for atomicity
        return DB::transaction(function () use ($order) {
            // Check if commissions already exist for this order (idempotency check)
            if ($order->commissions()->exists()) {
                return [
                    'status' => 'already_exists',
                    'message' => 'Commissions already generated for this order',
                    'commissions' => $order->commissions()->get(),
                ];
            }

            $commissions = [];

            // Step 1: Generate commission for direct seller (if partner exists)
            if ($order->partner_id) {
                $directCommission = $this->generateDirectCommission($order);
                if ($directCommission) {
                    $commissions[] = $directCommission;
                }
            }

            // Step 2: Generate commissions for parent hierarchy
            if ($order->partner_id) {
                $hierarchyCommissions = $this->generateHierarchyCommissions($order);
                $commissions = array_merge($commissions, $hierarchyCommissions);
            }

            return [
                'status' => 'success',
                'order_id' => $order->id,
                'commissions_generated' => count($commissions),
                'commissions' => $commissions,
                'total_commission' => array_sum(array_column($commissions, 'commission_amount')),
            ];
        });
    }

    /**
     * Generate commission for direct seller (Level 1).
     * 
     * @param Order $order
     * @return Commission|null
     */
    private function generateDirectCommission(Order $order)
    {
        // Get Level 1 commission rule for this program
        $rule = CommissionRule::where('program_id', $order->program_id)
            ->where('level', 1)
            ->where('status', true)
            ->where('event', 'sale')
            ->first();

        if (!$rule) {
            return null;
        }

        // Calculate commission amount
        $baseAmount = $order->total;
        $rate = $rule->value; // e.g., 20
        $commissionAmount = ($baseAmount * $rate) / 100;

        // Cap commission if maximum_amount is set
        if ($rule->maximum_amount && $commissionAmount > $rule->maximum_amount) {
            $commissionAmount = $rule->maximum_amount;
        }

        // Create commission record
        $commission = Commission::create([
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
            'status' => 'available', // Direct sales commission is immediately available
            'available_at' => now(),
        ]);

        return $commission;
    }

    /**
     * Generate commissions for parent hierarchy (Level 2+).
     * 
     * Walks up the partner hierarchy and creates commissions for each recruiter
     * based on the configured rules for their level.
     * 
     * @param Order $order
     * @return array Array of Commission models
     */
    private function generateHierarchyCommissions(Order $order)
    {
        $commissions = [];
        $currentPartner = $order->partner;
        $level = 2;

        // Walk up the hierarchy
        while ($currentPartner->parent_partner_id) {
            $parentPartner = $currentPartner->parentPartner;

            if (!$parentPartner) {
                break;
            }

            // Get commission rule for this level
            $rule = CommissionRule::where('program_id', $order->program_id)
                ->where('level', $level)
                ->where('status', true)
                ->where('event', 'sale')
                ->first();

            // If no rule for this level, stop hierarchy traversal
            if (!$rule) {
                break;
            }

            // Calculate commission amount
            $baseAmount = $order->total;
            $rate = $rule->value;
            $commissionAmount = ($baseAmount * $rate) / 100;

            // Cap commission if maximum_amount is set
            if ($rule->maximum_amount && $commissionAmount > $rule->maximum_amount) {
                $commissionAmount = $rule->maximum_amount;
            }

            // Create commission record for this level
            $commission = Commission::create([
                'program_id' => $order->program_id,
                'order_id' => $order->id,
                'partner_id' => $parentPartner->id,
                'source_partner_id' => $order->partner_id, // Track the original seller
                'rule_id' => $rule->id,
                'level' => $level,
                'commission_type' => $rule->commission_type,
                'rate' => $rate,
                'base_amount' => $baseAmount,
                'commission_amount' => $commissionAmount,
                'status' => 'available',
                'available_at' => now(),
            ]);

            $commissions[] = $commission;

            // Move up one level in hierarchy
            $currentPartner = $parentPartner;
            $level++;
        }

        return $commissions;
    }

    /**
     * Get pending commission amount for a partner.
     * 
     * @param ProgramPartner $partner
     * @return float
     */
    public function getPendingCommissionAmount(ProgramPartner $partner)
    {
        return (float) Commission::where('partner_id', $partner->id)
            ->where('status', 'available')
            ->sum('commission_amount');
    }

    /**
     * Get paid commission amount for a partner.
     * 
     * @param ProgramPartner $partner
     * @return float
     */
    public function getPaidCommissionAmount(ProgramPartner $partner)
    {
        return (float) Commission::where('partner_id', $partner->id)
            ->where('status', 'paid')
            ->sum('commission_amount');
    }

    /**
     * Get total commission amount for a partner (all statuses).
     * 
     * @param ProgramPartner $partner
     * @return float
     */
    public function getTotalCommissionAmount(ProgramPartner $partner)
    {
        return (float) Commission::where('partner_id', $partner->id)
            ->whereIn('status', ['available', 'paid', 'pending'])
            ->sum('commission_amount');
    }

    /**
     * Get commission stats for a partner in a specific program.
     * 
     * @param ProgramPartner $partner
     * @return array
     */
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
