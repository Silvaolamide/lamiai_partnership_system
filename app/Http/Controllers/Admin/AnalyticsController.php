<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\Product;
use App\Models\ProgramPartner;
use App\Services\AdminAnalyticsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AdminAnalyticsService $analytics) {}

    public function show(Request $request, string $metric)
    {
        $allowed = ['partners', 'programs', 'products', 'orders', 'sales', 'commissions', 'payable', 'paid-commissions'];
        abort_unless(in_array($metric, $allowed, true), 404);

        [$from, $to] = $this->analytics->dateRange($request);
        $title = match ($metric) {
            'partners' => 'Partner Analytics', 'programs' => 'Program Analytics', 'products' => 'Product Analytics',
            'orders' => 'Order Analytics', 'sales' => 'Sales & Net Revenue Analytics', 'commissions' => 'Commission Analytics',
            'payable' => 'Payable Commission Analytics', 'paid-commissions' => 'Paid Commission Analytics',
        };

        $stats = $this->analytics->summary($from, $to);
        $series = $this->analytics->series($from, $to);
        $orders = $this->analytics->orderQuery($from, $to)
            ->with(['customer', 'partner.user', 'program', 'items.product', 'commissions.partner.user', 'commissions.rule'])
            ->latest()->paginate(50)->withQueryString();
        $commissions = $this->analytics->commissionQuery($from, $to)
            ->with(['partner.user', 'program', 'order', 'rule'])->latest()->paginate(50, ['*'], 'commission_page')->withQueryString();

        $data = compact('orders', 'commissions');

        if ($metric === 'partners') {
            $data['partnersList'] = ProgramPartner::with(['user', 'program', 'parentPartner.user'])
                ->withSum(['commissions as earnings' => fn ($q) => $q->whereNotIn('status', ['reversed', 'cancelled'])], 'commission_amount')
                ->withCount('childPartners')->latest()->paginate(50)->withQueryString();
            $data['recruits'] = ProgramPartner::whereNotNull('parent_partner_id')->with(['user', 'parentPartner.user', 'program'])->latest()->limit(100)->get();
        }

        if ($metric === 'programs') {
            $data['programList'] = PartnershipProgram::withCount(['partners', 'orders', 'products'])
                ->withSum(['orders as sales' => fn ($q) => $q->whereIn('status', AdminAnalyticsService::PAID_STATUSES)
                    ->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))], 'total')
                ->latest()->paginate(50)->withQueryString();
        }

        if ($metric === 'products') {
            $data['productList'] = Product::with('owner')->latest()->paginate(50)->withQueryString();
        }

        if (in_array($metric, ['commissions', 'payable', 'paid-commissions'], true)) {
            $query = $this->analytics->commissionQuery($from, $to);
            if ($metric === 'payable') $query->whereIn('status', ['available', 'approved', 'payable']);
            if ($metric === 'paid-commissions') $query->where('status', 'paid');
            $filtered = $query->with(['partner.user', 'program', 'order', 'rule'])->latest()->paginate(50, ['*'], 'commission_page')->withQueryString();
            $data['commissionList'] = $filtered;
            $data['byLevel'] = $this->analytics->commissionQuery($from, $to)->when($metric === 'payable', fn ($q) => $q->whereIn('status', ['available', 'approved', 'payable']))->when($metric === 'paid-commissions', fn ($q) => $q->where('status', 'paid'))->selectRaw('level, SUM(commission_amount) as amount')->groupBy('level')->orderBy('level')->get();
            $data['byPartner'] = $this->analytics->commissionQuery($from, $to)->when($metric === 'payable', fn ($q) => $q->whereIn('status', ['available', 'approved', 'payable']))->when($metric === 'paid-commissions', fn ($q) => $q->where('status', 'paid'))->selectRaw('partner_id, SUM(commission_amount) as amount, COUNT(*) as count')->groupBy('partner_id')->orderByDesc('amount')->limit(10)->with('partner.user')->get();
        }

        return view('admin.analytics.show', array_merge($data, compact('metric', 'title', 'stats', 'series', 'from', 'to')));
    }

    public function export(Request $request, string $metric): StreamedResponse
    {
        abort_unless(in_array($metric, ['orders', 'sales', 'commissions', 'payable', 'paid-commissions'], true), 404);
        [$from, $to] = $this->analytics->dateRange($request);
        $filename = 'lami-ai-'.$metric.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($metric, $from, $to) {
            $out = fopen('php://output', 'w');
            if ($metric === 'orders' || $metric === 'sales') {
                fputcsv($out, ['Order', 'Customer', 'Program', 'Partner', 'Status', 'Currency', 'Total', 'Paid At', 'Created At']);
                $this->analytics->orderQuery($from, $to)->with(['customer', 'partner.user', 'program'])->orderBy('id')->chunkById(500, function ($rows) use ($out) {
                    foreach ($rows as $order) fputcsv($out, [$order->order_number, $order->customer?->name ?? $order->customer_name, $order->program?->name, $order->partner?->user?->name, $order->status, $order->currency, $order->total, $order->paid_at, $order->created_at]);
                });
            } else {
                $query = $this->analytics->commissionQuery($from, $to);
                if ($metric === 'payable') $query->whereIn('status', ['available', 'approved', 'payable']);
                if ($metric === 'paid-commissions') $query->where('status', 'paid');
                fputcsv($out, ['Order', 'Partner', 'Program', 'Level', 'Status', 'Base Amount', 'Rate', 'Commission Amount', 'Created At', 'Paid At']);
                $query->with(['order', 'partner.user', 'program'])->orderBy('id')->chunkById(500, function ($rows) use ($out) {
                    foreach ($rows as $commission) fputcsv($out, [$commission->order?->order_number, $commission->partner?->user?->name, $commission->program?->name, $commission->level, $commission->status, $commission->base_amount, $commission->rate, $commission->commission_amount, $commission->created_at, $commission->paid_at]);
                });
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
