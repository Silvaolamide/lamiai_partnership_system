<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessPayout;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\ProgramPartner;
use App\Models\Payout;
use App\Models\PaymentSubmission;
use App\Models\User;
use App\Services\AdminAnalyticsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly AdminAnalyticsService $analytics) {}

    public function index(Request $request)
    {
        [$from, $to] = $this->analytics->dateRange($request);
        $businessId = $request->filled('business_id') ? (int) $request->input('business_id') : null;
        $programId = $request->filled('program_id') ? (int) $request->input('program_id') : null;
        $base = $this->analytics->summary($from, $to, $businessId, $programId);
        $stats = [
            'businesses' => User::role('program_manager')->count(), 'pending_businesses' => User::role('program_manager')->whereNull('business_super_admin_approved_at')->whereNull('business_rejected_at')->count(),
            'partners' => $base['partners'], 'active_partners' => $base['active_partners'], 'pending_partners' => $base['pending_partners'], 'programs' => $base['programs'],
            'active_programs' => PartnershipProgram::when($businessId, fn ($q) => $q->where('owner_id', $businessId))->when($programId, fn ($q) => $q->whereKey($programId))->where('status', 'active')->count(),
            'products' => $base['products'], 'customers' => $base['customers'], 'orders' => Order::count(), 'paid_orders' => $base['orders'], 'sales' => $base['gross_sales'], 'commissions' => $base['commission_total'],
            'payable_commissions' => $base['payable'], 'paid_commissions' => $base['paid_commissions'], 'pending_payouts' => $base['pending_payouts'], 'net_platform_revenue' => $base['net_revenue'],
        ];

        $recentOrders = $this->analytics->orderQuery($from, $to, $businessId, $programId)->with(['customer', 'partner.user', 'program'])->latest()->limit(10)->get();
        $topPartners = ProgramPartner::whereIn('program_id', PartnershipProgram::select('id')->when($businessId, fn ($q) => $q->where('owner_id', $businessId))->when($programId, fn ($q) => $q->whereKey($programId)))->with(['user', 'program'])->withSum(['commissions as earnings' => fn ($q) => $q->whereNotIn('status', ['reversed', 'cancelled'])->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))], 'commission_amount')->orderByDesc('earnings')->limit(8)->get();
        $programs = PartnershipProgram::when($businessId, fn ($q) => $q->where('owner_id', $businessId))->when($programId, fn ($q) => $q->whereKey($programId))->withCount(['partners', 'orders', 'products'])->withSum(['orders as sales' => fn ($q) => $q->whereIn('status', AdminAnalyticsService::PAID_STATUSES)->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))], 'total')->latest()->limit(8)->get();
        $businesses = User::role('program_manager')->when($businessId, fn ($q) => $q->whereKey($businessId))->latest()->limit(10)->get()->map(function ($business) use ($from, $to, $programId) {
            $programIds = PartnershipProgram::where('owner_id', $business->id)->when($programId, fn ($q) => $q->whereKey($programId))->pluck('id');
            $paid = Order::whereIn('program_id', $programIds)->whereIn('status', AdminAnalyticsService::PAID_STATUSES)->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to));
            $business->dashboard_metrics = ['programs' => $programIds->count(), 'partners' => ProgramPartner::whereIn('program_id', $programIds)->count(), 'orders' => (clone $paid)->count(), 'sales' => (float) (clone $paid)->sum('total')]; return $business;
        });

        $businessPartnerApprovalQuery = ProgramPartner::query()->where('status', 'pending')->whereNull('business_approved_at')->whereHas('program', fn ($q) => $q->where('settings->partner_business_approval_required', true));
        if ($businessId) $businessPartnerApprovalQuery->whereHas('program', fn ($q) => $q->where('owner_id', $businessId));
        if ($programId) $businessPartnerApprovalQuery->where('program_id', $programId);
        $pendingBusinessPartnerApprovals = $businessPartnerApprovalQuery->count();

        $pendingPaymentConfirmationQuery = PaymentSubmission::query()->where('status', 'pending');
        if ($businessId || $programId) {
            $pendingPaymentConfirmationQuery->whereHas('order', function ($q) use ($businessId, $programId) {
                if ($programId) $q->where('program_id', $programId);
                if ($businessId) $q->whereHas('program', fn ($program) => $program->where('owner_id', $businessId));
            });
        }
        $pendingPaymentConfirmations = $pendingPaymentConfirmationQuery->count();

        $incompleteBusinessRegistrations = User::query()->where('registration_type', 'business')->where(function ($q) {
            $q->whereNull('email_verified_at')->orWhereDoesntHave('roles', fn ($role) => $role->where('name', 'program_manager'))->orWhereNull('business_super_admin_approved_at');
        })->count();

        $pendingPartnerPayouts = Payout::whereIn('status', ['requested', 'pending'])->count();
        $pendingBusinessPayouts = BusinessPayout::whereIn('status', ['requested', 'pending'])->count();

        // Keep every actionable item as a separate card. Multiple urgent items must coexist.
        $pendingActions = [
            ['label' => 'URGENT: Payments awaiting confirmation', 'count' => $pendingPaymentConfirmations, 'route' => 'admin.payments.index', 'urgent' => true, 'priority' => 100],
            ['label' => 'URGENT: Business payout requests', 'count' => $pendingBusinessPayouts, 'route' => 'admin.business-payouts.index', 'urgent' => true, 'priority' => 95],
            ['label' => 'URGENT: Partner payout requests', 'count' => $pendingPartnerPayouts, 'route' => 'admin.payouts.index', 'urgent' => true, 'priority' => 90],
            ['label' => 'URGENT: Business registration recovery', 'count' => $incompleteBusinessRegistrations, 'route' => 'admin.registrations.index', 'urgent' => true, 'priority' => 85],
            ['label' => 'URGENT: Business partner approvals', 'count' => $pendingBusinessPartnerApprovals, 'route' => 'admin.partners.index', 'urgent' => true, 'priority' => 80],
            ['label' => 'Business approvals', 'count' => $stats['pending_businesses'], 'route' => 'admin.businesses.index', 'urgent' => false, 'priority' => 50],
            ['label' => 'Partner approvals', 'count' => $stats['pending_partners'], 'route' => 'admin.partners.index', 'urgent' => false, 'priority' => 40],
        ];
        $pendingActions = collect($pendingActions)->filter(fn ($action) => $action['count'] > 0)->sortByDesc('priority')->values()->all();

        $series = $this->analytics->series($from, $to, $businessId, $programId);
        $businessOptions = User::role('program_manager')->orderBy('name')->get(['id','name','business_name']);
        $programOptions = PartnershipProgram::when($businessId, fn ($q) => $q->where('owner_id', $businessId))->orderBy('name')->get(['id','name','owner_id']);

        $viewData = compact('stats', 'recentOrders', 'topPartners', 'programs', 'businesses', 'pendingActions', 'series', 'from', 'to', 'businessOptions', 'programOptions', 'businessId', 'programId');
        $view = view('admin.dashboard', $viewData);

        // Render a dedicated high-visibility urgent area above the executive overview.
        // Each actionable item gets its own banner, so multiple urgent items are shown simultaneously.
        $urgentActions = collect($pendingActions)->where('urgent', true)->values();
        if ($urgentActions->isNotEmpty()) {
            $urgentBanners = $urgentActions->map(function (array $action) {
                $isBusinessPayout = $action['route'] === 'admin.business-payouts.index';
                $isPayment = $action['route'] === 'admin.payments.index';
                $title = e($action['label']);
                $count = number_format((int) $action['count']);
                $tone = $isBusinessPayout || $isPayment ? 'rose' : 'amber';
                $icon = $isBusinessPayout ? '₦' : ($isPayment ? '!' : '⚠');

                return '<a href="' . route($action['route']) . '" class="group relative flex min-h-[92px] items-center justify-between gap-4 overflow-hidden rounded-2xl border border-' . $tone . '-200 bg-gradient-to-r from-' . $tone . '-50 via-white to-white px-5 py-4 shadow-sm ring-1 ring-' . $tone . '-100 transition hover:-translate-y-0.5 hover:shadow-lg">'
                    . '<div class="absolute inset-y-0 left-0 w-1.5 bg-' . $tone . '-500"></div>'
                    . '<div class="flex min-w-0 items-center gap-4">'
                    . '<span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-' . $tone . '-100 text-lg font-black text-' . $tone . '-700 ring-1 ring-' . $tone . '-200">' . $icon . '</span>'
                    . '<div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><span class="text-[10px] font-black uppercase tracking-[0.18em] text-' . $tone . '-700">URGENT ACTION</span><span class="rounded-full bg-' . $tone . '-100 px-2 py-0.5 text-[10px] font-black text-' . $tone . '-700">' . $count . ' pending</span></div><div class="mt-1 truncate text-base font-black text-slate-950">' . $title . '</div><div class="mt-1 text-xs font-medium text-slate-500">Open the queue and take action now →</div></div>'
                    . '</div><span class="hidden shrink-0 rounded-xl bg-slate-950 px-4 py-2.5 text-xs font-black text-white sm:inline-flex">Review now →</span>'
                    . '</a>';
            })->implode('');

            $urgentSection = '<section class="mb-6 space-y-3" aria-label="Urgent admin actions">'
                . '<div class="flex items-end justify-between gap-3"><div><div class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-600">Immediate attention required</div><h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Urgent operations</h3><p class="mt-1 text-xs text-slate-500">These queues require admin action. Multiple urgent items remain visible at the same time.</p></div><span class="rounded-full bg-rose-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-rose-700 ring-1 ring-rose-100">' . number_format($urgentActions->sum('count')) . ' total pending</span></div>'
                . '<div class="grid grid-cols-1 gap-3 lg:grid-cols-2">' . $urgentBanners . '</div>'
                . '</section>';

            $html = $view->render();
            $marker = '        {{-- Hero --}}';
            $html = str_replace($marker, $urgentSection . "\n\n" . $marker, $html, $markerCount);
            if ($markerCount === 0) {
                $html = $html . $urgentSection;
            }

            return response($html);
        }

        return $view;
    }

    public function realtimeSales(Request $request)
    {
        $limit = min(max((int) $request->input('limit', 10), 1), 25);
        $orders = Order::query()->with(['customer', 'partner.user', 'program'])->whereIn('status', AdminAnalyticsService::PAID_STATUSES)->latest('paid_at')->latest('id')->limit($limit)->get();
        return response()->json(['data' => $orders->map(fn (Order $order) => ['id' => $order->id, 'order_number' => $order->order_number, 'amount' => (float) $order->total, 'status' => $order->status, 'paid_at' => optional($order->paid_at)->toIso8601String(), 'customer' => $order->customer?->name ?? $order->customer?->email ?? 'Customer', 'customer_email' => $order->customer?->email, 'partner' => $order->partner?->user?->name, 'program' => $order->program?->name, 'url' => route('admin.orders.show', $order)])->values(), 'timestamp' => now()->toIso8601String()]);
    }
}
