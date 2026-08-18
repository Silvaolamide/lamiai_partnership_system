<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\Product;
use App\Models\ProgramPartner;
use App\Models\Payout;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function show(Request $request, string $metric)
    {
        $allowed = ['partners', 'programs', 'products', 'orders', 'sales', 'commissions', 'payable', 'paid-commissions'];
        abort_unless(in_array($metric, $allowed, true), 404);

        $title = match ($metric) {
            'partners' => 'Partner Analytics',
            'programs' => 'Program Analytics',
            'products' => 'Product Analytics',
            'orders' => 'Order Analytics',
            'sales' => 'Sales & Net Revenue Analytics',
            'commissions' => 'Commission Analytics',
            'payable' => 'Payable Commission Analytics',
            'paid-commissions' => 'Paid Commission Analytics',
        };

        $paidStatuses = ['paid', 'completed', 'processing', 'fulfilled'];
        $completedOrders = Order::whereIn('status', $paidStatuses)
            ->with(['customer', 'partner.user', 'program', 'items.product', 'commissions.partner.user', 'commissions.rule'])
            ->latest()
            ->get();

        $commissions = Commission::with(['partner.user', 'program', 'order', 'rule'])
            ->whereNotIn('status', ['reversed', 'cancelled'])
            ->latest()
            ->get();

        $data = [
            'orders' => $completedOrders,
            'commissions' => $commissions,
            'stats' => [
                'gross_sales' => (float) $completedOrders->sum('total'),
                'commission_total' => (float) $commissions->sum('commission_amount'),
                'net_revenue' => max(0, (float) $completedOrders->sum('total') - (float) $commissions->sum('commission_amount')),
                'orders' => $completedOrders->count(),
                'partners' => ProgramPartner::count(),
                'active_partners' => ProgramPartner::where('status', 'active')->count(),
                'pending_partners' => ProgramPartner::where('status', 'pending')->count(),
                'programs' => PartnershipProgram::count(),
                'products' => Product::count(),
                'payable' => (float) Commission::whereIn('status', ['available', 'approved', 'payable'])->sum('commission_amount'),
                'paid_commissions' => (float) Commission::where('status', 'paid')->sum('commission_amount'),
            ],
        ];

        if ($metric === 'partners') {
            $data['partnersList'] = ProgramPartner::with(['user', 'program', 'parentPartner.user'])
                ->withSum(['commissions as earnings' => fn ($q) => $q->whereNotIn('status', ['reversed', 'cancelled'])], 'commission_amount')
                ->withCount('childPartners')
                ->latest()
                ->get();
            $data['recruits'] = ProgramPartner::whereNotNull('parent_partner_id')->with(['user', 'parentPartner.user', 'program'])->latest()->get();
        }

        if ($metric === 'programs') {
            $data['programList'] = PartnershipProgram::withCount(['partners', 'orders', 'products'])
                ->withSum(['orders as sales' => fn ($q) => $q->whereIn('status', $paidStatuses)], 'total')
                ->latest()->get();
        }

        if ($metric === 'products') {
            $data['productList'] = Product::with('owner')->latest()->get()->map(function ($product) use ($completedOrders) {
                $items = $completedOrders->flatMap->items->filter(fn ($item) => $item->product_id === $product->id);
                return [
                    'product' => $product,
                    'units' => $items->sum('quantity'),
                    'sales' => (float) $items->sum(fn ($item) => (float) $item->total),
                    'orders' => $items->pluck('order_id')->unique()->count(),
                ];
            })->sortByDesc('sales')->values();
        }

        if (in_array($metric, ['commissions', 'payable', 'paid-commissions'], true)) {
            $filtered = match ($metric) {
                'payable' => $commissions->whereIn('status', ['available', 'approved', 'payable']),
                'paid-commissions' => $commissions->where('status', 'paid'),
                default => $commissions,
            };
            $data['commissionList'] = $filtered->values();
            $data['byLevel'] = $filtered->groupBy('level')->map(fn ($rows) => (float) $rows->sum('commission_amount'))->sortKeys();
            $data['byPartner'] = $filtered->groupBy('partner_id')->map(function ($rows) {
                $first = $rows->first();
                return ['partner' => $first?->partner, 'amount' => (float) $rows->sum('commission_amount'), 'count' => $rows->count()];
            })->sortByDesc('amount')->values();
        }

        return view('admin.analytics.show', array_merge($data, compact('metric', 'title')));
    }
}
