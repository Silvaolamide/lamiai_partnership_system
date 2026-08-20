<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PlatformSetting;

class PlatformFeeService
{
    public function applyToOrder(Order $order): Order
    {
        $percent = $this->rate();
        $amount = $this->calculate((float) $order->total, $percent);

        $order->update([
            'platform_fee_percent' => $percent,
            'platform_fee_amount' => $amount,
            'business_net_amount' => max(0, round((float) $order->total - $amount, 2)),
        ]);

        return $order->fresh();
    }

    public function rate(): float
    {
        return min(100, max(0, (float) PlatformSetting::getValue('admin_charge_percent', 0)));
    }

    public function calculate(float $grossAmount, ?float $percent = null): float
    {
        $percent ??= $this->rate();

        return round(max(0, $grossAmount) * ($percent / 100), 2);
    }
}
