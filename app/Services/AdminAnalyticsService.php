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
    public const ACTIVE_COMMISSION_STATUSES = ['available', 'approved', 'payable', 'paid'];

    public function dateRange(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from'))->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->string('to'))->endOfDay() : null;

        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    public function orderQuery(?Carbon $from = null, ?Carbon $to = null)
    {
        return Order::query()
            ->whereIn('status', self::PAID_STATUSES)
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to));
    }

    public function commissionQuery(?Carbon $from = null, ?Carbon $to = null)
    {
        return Commission::query()
            ->whereNotIn('status', ['reversed', 'cancelled'])
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to));
    }

    public function summary(?Carbon $from = null, ?Carbon $to = null): array
    {
        $orders = $this->orderQuery($from, $to);
        $commissions = $this->commissionQuery($from, $to);
        $gross = (float) (clone $orders)->sum('total');
        $commissionTotal = (float) (clone $commissions)->sum('commission_amount');

        return [
            'gross_sales' => $gross,
            'commission_total' => $commissionTotal,
            'net_revenue' => max(0, $gross - $commissionTotal),
            'orders' => (clone $orders)->count(),
            'partners' => ProgramPartner::count(),
            'active_partners' => ProgramPartner::where('status', 'active')->count(),
            'pending_partners' => ProgramPartner::where('status', 'pending')->count(),
            'programs' => PartnershipProgram::count(),
            'products' => Product::count(),
            'customers' => User::role('customer')->count(),
            'payable' => (float) Commission::whereIn('status', ['available', 'approved', 'payable'])
                ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
                ->sum('commission_amount'),
            'paid_commissions' => (float) Commission::where('status', 'paid')
                ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
                ->sum('commission_amount'),
            'pending_payouts' => (float) Payout::whereIn('status', ['requested', 'pending', 'approved'])->sum('amount'),
        ];
    }

    public function series(?Carbon $from = null, ?Carbon $to = null): array
    {
        $sales = $this->orderQuery($from, $to)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as orders, SUM(total) as sales')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $commissions = $this->commissionQuery($from, $to)
            ->selectRaw('DATE(created_at) as day, SUM(commission_amount) as commissions')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $start = ($from ?? ($sales->keys()->first() ? Carbon::parse($sales->keys()->first()) : now()->startOfDay()))->copy()->startOfDay();
        $end = ($to ?? ($sales->keys()->last() ? Carbon::parse($sales->keys()->last()) : now()->endOfDay()))->copy()->startOfDay();
        if ($start->gt($end)) [$start, $end] = [$end, $start];

        $labels = $sales->keys()->merge($commissions->keys())->unique()->sort()->values();
        if ($labels->isEmpty() && $start->diffInDays($end) <= 31) {
            for ($day = $start->copy(); $day->lte($end); $day->addDay()) $labels->push($day->toDateString());
        }

        return $labels->map(function ($day) use ($sales, $commissions) {
            return [
                'date' => $day,
                'sales' => (float) ($sales[$day]->sales ?? 0),
                'orders' => (int) ($sales[$day]->orders ?? 0),
                'commissions' => (float) ($commissions[$day]->commissions ?? 0),
            ];
        })->values()->all();
    }
}
