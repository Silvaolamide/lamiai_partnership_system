<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\Product;
use App\Models\ProgramPartner;
use App\Models\Payout;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAnalyticsService
{
    public const PAID_STATUSES = ['paid', 'completed', 'processing', 'fulfilled'];

    public function dateRange(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from'))->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->string('to'))->endOfDay() : null;
        if ($from && $to && $from->gt($to)) [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        return [$from, $to];
    }

    private function constrainProgramScope($query, ?int $businessId = null, ?int $programId = null)
    {
        return $query
            ->when($businessId, fn ($q) => $q->whereIn('program_id', PartnershipProgram::select('id')->where('owner_id', $businessId)))
            ->when($programId, fn ($q) => $q->where('program_id', $programId));
    }

    public function orderQuery(?Carbon $from = null, ?Carbon $to = null, ?int $businessId = null, ?int $programId = null)
    {
        return $this->constrainProgramScope(Order::query()->whereIn('status', self::PAID_STATUSES), $businessId, $programId)
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to));
    }

    public function commissionQuery(?Carbon $from = null, ?Carbon $to = null, ?int $businessId = null, ?int $programId = null)
    {
        return $this->constrainProgramScope(Commission::query()->whereNotIn('status', ['reversed', 'cancelled']), $businessId, $programId)
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to));
    }

    public function summary(?Carbon $from = null, ?Carbon $to = null, ?int $businessId = null, ?int $programId = null): array
    {
        $orders = $this->orderQuery($from, $to, $businessId, $programId);
        $commissions = $this->commissionQuery($from, $to, $businessId, $programId);
        $gross = (float) (clone $orders)->sum('total');
        $commissionTotal = (float) (clone $commissions)->sum('commission_amount');
        $programScope = PartnershipProgram::query()->when($businessId, fn ($q) => $q->where('owner_id', $businessId))->when($programId, fn ($q) => $q->whereKey($programId));
        $programIds = $programScope->select('id');

        return [
            'gross_sales' => $gross, 'commission_total' => $commissionTotal, 'net_revenue' => max(0, $gross - $commissionTotal), 'orders' => (clone $orders)->count(),
            'partners' => ProgramPartner::whereIn('program_id', $programIds)->count(), 'active_partners' => ProgramPartner::whereIn('program_id', $programIds)->where('status', 'active')->count(),
            'pending_partners' => ProgramPartner::whereIn('program_id', $programIds)->where('status', 'pending')->count(), 'programs' => (clone $programScope)->count(),
            'products' => Product::whereIn('id', function ($q) use ($programIds) { $q->select('product_id')->from('partnership_program_product')->whereIn('partnership_program_id', $programIds); })->count(),
            'customers' => (clone $orders)->whereNotNull('customer_id')->distinct()->count('customer_id'),
            'payable' => (float) $this->commissionQuery($from, $to, $businessId, $programId)->whereIn('status', ['available', 'approved', 'payable'])->sum('commission_amount'),
            'paid_commissions' => (float) $this->commissionQuery($from, $to, $businessId, $programId)->where('status', 'paid')->sum('commission_amount'),
            'pending_payouts' => (float) Payout::whereIn('status', ['requested', 'pending', 'approved'])->sum('amount'),
        ];
    }

    public function series(?Carbon $from = null, ?Carbon $to = null, ?int $businessId = null, ?int $programId = null): array
    {
        $sales = $this->orderQuery($from, $to, $businessId, $programId)->selectRaw('DATE(created_at) as day, COUNT(*) as orders, SUM(total) as sales')->groupByRaw('DATE(created_at)')->orderBy('day')->get()->keyBy('day');
        $commissions = $this->commissionQuery($from, $to, $businessId, $programId)->selectRaw('DATE(created_at) as day, SUM(commission_amount) as commissions')->groupByRaw('DATE(created_at)')->orderBy('day')->get()->keyBy('day');
        $labels = $sales->keys()->merge($commissions->keys())->unique()->sort()->values();
        return $labels->map(fn ($day) => ['date' => $day, 'sales' => (float) ($sales[$day]->sales ?? 0), 'orders' => (int) ($sales[$day]->orders ?? 0), 'commissions' => (float) ($commissions[$day]->commissions ?? 0)])->values()->all();
    }
}
