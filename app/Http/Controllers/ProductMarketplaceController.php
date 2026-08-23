<?php

namespace App\Http\Controllers;

use App\Models\PartnershipProgram;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductMarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $request->string('sort')->toString() ?: 'newest';
        $allowedSorts = ['newest', 'oldest', 'price_low', 'price_high', 'name', 'popular'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'newest';
        }

        $query = Product::query()
            ->where('status', 'active')
            ->with([
                'owner',
                'partnershipPrograms' => fn ($q) => $q->where('status', 'active')->orderBy('name'),
            ])
            ->withCount([
                'orderItems as paid_units_sold' => fn ($q) => $q->whereHas('order', fn ($order) => $order->where('status', 'paid')),
            ]);

        if ($search = trim($request->string('q')->toString())) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($programId = $request->integer('program')) {
            $query->whereHas('partnershipPrograms', fn ($q) => $q->whereKey($programId)->where('status', 'active'));
        }

        if ($currency = strtoupper(trim($request->string('currency')->toString()))) {
            $query->where('currency', $currency);
        }

        if ($request->filled('min_price') && is_numeric($request->input('min_price'))) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price') && is_numeric($request->input('max_price'))) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        match ($sort) {
            'oldest' => $query->oldest(),
            'price_low' => $query->orderBy('price')->orderByDesc('created_at'),
            'price_high' => $query->orderByDesc('price')->orderByDesc('created_at'),
            'name' => $query->orderBy('name'),
            'popular' => $query->orderByDesc('paid_units_sold')->orderByDesc('created_at'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $programs = PartnershipProgram::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        $currencies = Product::query()
            ->where('status', 'active')
            ->whereNotNull('currency')
            ->distinct()
            ->orderBy('currency')
            ->pluck('currency');

        return view('marketplace.products', compact('products', 'programs', 'currencies', 'sort'));
    }
}
