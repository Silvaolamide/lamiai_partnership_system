<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $businessId = $request->filled('business_id') ? (int) $request->input('business_id') : null;
        $programId = $request->filled('program_id') ? (int) $request->input('program_id') : null;
        $title = match ($metric) {
            'partners' => 'Partner Analytics', 'programs' => 'Program Analytics', 'products' => 'Product Analytics', 'orders' => 'Order Analytics',
            'sales' => 'Sales & Net Revenue Analytics', 'commissions' => 'Commission Analytics', 'payable' => 'Payable Commission Analytics', 'paid-commissions' => 'Paid Commission Analytics',
        };
        $stats = $this->analytics->summary($from, $to, $businessId, $programId);
        $series = $this->analytics->series($from, $to, $businessId, $programId);
        $orders = $this->analytics->orderQuery($from, $to, $businessId, $programId)->with(['customer', 'partner.user', 'program', 'items.product', 'commissions.partner.user', 'commissions.rule'])->latest()->paginate(50)->withQueryString();
        $commissions = $this->analytics->commissionQuery($from, $to, $businessId, $programId)->with(['partner.user', 'program', 'order', 'rule'])->latest()->paginate(50, ['*'], 'commission_page')->withQueryString();
        $data = compact('orders', 'commissions');

        if ($metric === 'partners') {
            $partnerQuery = ProgramPartner::whereIn('program_id', PartnershipProgram::select('id')->when($businessId, fn ($q) => $q->where('owner_id', $businessId))->when($programId, fn ($q) => $q->whereKey($programId)));
            $data['partnersList'] = $partnerQuery->with(['user', 'program', 'parentPartner.user'])->withSum(['commissions as earnings' => fn ($q) => $q->whereNotIn('status', ['reversed', 'cancelled'])->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))], 'commission_amount')->withCount('childPartners')->latest()->paginate(50)->withQueryString();
            $data['recruits'] = (clone $partnerQuery)->whereNotNull('parent_partner_id')->with(['user', 'parentPartner.user', 'program'])->latest()->limit(100)->get();
        }
        if ($metric === 'programs') {
            $data['programList'] = PartnershipProgram::when($businessId, fn ($q) => $q->where('owner_id', $businessId))->when($programId, fn ($q) => $q->whereKey($programId))->withCount(['partners', 'orders', 'products'])->withSum(['orders as sales' => fn ($q) => $q->whereIn('status', AdminAnalyticsService::PAID_STATUSES)->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))], 'total')->latest()->paginate(50)->withQueryString();
        }
        if ($metric === 'products') {
            $data['productList'] = Product::when($businessId, fn ($q) => $q->where('owner_id', $businessId))->with('owner')->latest()->paginate(50)->withQueryString();
        }
        if (in_array($metric, ['commissions', 'payable', 'paid-commissions'], true)) {
            $query = $this->analytics->commissionQuery($from, $to, $businessId, $programId);
            if ($metric === 'payable') $query->whereIn('status', ['available', 'approved', 'payable']);
            if ($metric === 'paid-commissions') $query->where('status', 'paid');
            $data['commissionList'] = $query->with(['partner.user', 'program', 'order', 'rule'])->latest()->paginate(50, ['*'], 'commission_page')->withQueryString();
            $data['byLevel'] = (clone $query)->selectRaw('level, SUM(commission_amount) as amount')->groupBy('level')->orderBy('level')->get();
            $data['byPartner'] = (clone $query)->selectRaw('partner_id, SUM(commission_amount) as amount, COUNT(*) as count')->groupBy('partner_id')->orderByDesc('amount')->limit(10)->with('partner.user')->get();
        }
        $businessOptions = \App\Models\User::role('program_manager')->orderBy('name')->get(['id','name','business_name']);
        $programOptions = PartnershipProgram::when($businessId, fn ($q) => $q->where('owner_id', $businessId))->orderBy('name')->get(['id','name','owner_id']);
        return view('admin.analytics.show', array_merge($data, compact('metric', 'title', 'stats', 'series', 'from', 'to', 'businessId', 'programId', 'businessOptions', 'programOptions')));
    }

    public function export(Request $request, string $metric): StreamedResponse
    {
        abort_unless(in_array($metric, ['orders', 'sales', 'commissions', 'payable', 'paid-commissions'], true), 404);
        [$from, $to] = $this->analytics->dateRange($request);
        $businessId = $request->filled('business_id') ? (int) $request->input('business_id') : null;
        $programId = $request->filled('program_id') ? (int) $request->input('program_id') : null;
        $filename = 'lami-ai-'.$metric.'-'.now()->format('Ymd-His').'.csv';
        return response()->streamDownload(function () use ($metric, $from, $to, $businessId, $programId) {
            $out = fopen('php://output', 'w');
            if ($metric === 'orders' || $metric === 'sales') {
                fputcsv($out, ['Order','Customer','Program','Partner','Status','Currency','Total','Paid At','Created At']);
                $this->analytics->orderQuery($from, $to, $businessId, $programId)->with(['customer','partner.user','program'])->orderBy('id')->chunkById(500, function ($rows) use ($out) { foreach ($rows as $order) fputcsv($out, [$order->order_number,$order->customer?->name ?? $order->customer_name,$order->program?->name,$order->partner?->user?->name,$order->status,$order->currency,$order->total,$order->paid_at,$order->created_at]); });
            } else {
                $query = $this->analytics->commissionQuery($from, $to, $businessId, $programId);
                if ($metric === 'payable') $query->whereIn('status', ['available','approved','payable']);
                if ($metric === 'paid-commissions') $query->where('status','paid');
                fputcsv($out, ['Order','Partner','Program','Level','Status','Base Amount','Rate','Commission Amount','Created At','Paid At']);
                $query->with(['order','partner.user','program'])->orderBy('id')->chunkById(500, function ($rows) use ($out) { foreach ($rows as $commission) fputcsv($out, [$commission->order?->order_number,$commission->partner?->user?->name,$commission->program?->name,$commission->level,$commission->status,$commission->base_amount,$commission->rate,$commission->commission_amount,$commission->created_at,$commission->paid_at]); });
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
