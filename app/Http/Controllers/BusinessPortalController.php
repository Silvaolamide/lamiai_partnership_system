<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Order;
use App\Models\PartnershipProgram;
use App\Models\Product;
use App\Models\ProgramPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BusinessPortalController extends Controller
{
    private function owner(Request $request): int
    {
        return (int) $request->user()->id;
    }

    private function program(Request $request, PartnershipProgram $program): PartnershipProgram
    {
        abort_unless((int) $program->owner_id === $this->owner($request), 403);
        return $program;
    }

    private function product(Request $request, Product $product): Product
    {
        abort_unless((int) $product->owner_id === $this->owner($request), 403);
        return $product;
    }

    public function programs(Request $request): View
    {
        $programs = PartnershipProgram::where('owner_id', $this->owner($request))
            ->withCount(['partners', 'products', 'orders', 'commissions'])
            ->with(['commissionRules' => fn ($q) => $q->where('status', true)->orderBy('level')])
            ->latest()->paginate(12);
        return view('business.programs.index', compact('programs'));
    }

    public function editProgram(Request $request, PartnershipProgram $program): View
    {
        $program = $this->program($request, $program)->load(['products', 'commissionRules']);
        $products = Product::where('owner_id', $this->owner($request))->orderBy('name')->get();
        return view('business.programs.edit', compact('program', 'products'));
    }

    public function updateProgram(Request $request, PartnershipProgram $program)
    {
        $program = $this->program($request, $program);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,paused'],
            'attribution_window_days' => ['required', 'integer', 'min:1'],
            'minimum_payout' => ['required', 'numeric', 'min:0'],
            'products' => ['nullable', 'array'],
            'products.*' => ['integer', 'exists:products,id'],
            'commission_rules' => ['nullable', 'array'],
            'commission_rules.*.id' => ['nullable', 'integer'],
            'commission_rules.*.level' => ['required', 'integer', 'min:1'],
            'commission_rules.*.value' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
        $program->update([
            'name' => $data['name'], 'description' => $data['description'] ?? null,
            'status' => $data['status'], 'attribution_window_days' => $data['attribution_window_days'],
            'minimum_payout' => $data['minimum_payout'],
        ]);
        $ownedProductIds = Product::where('owner_id', $this->owner($request))->pluck('id');
        $program->products()->sync(collect($data['products'] ?? [])->intersect($ownedProductIds)->values());
        $program->commissionRules()->delete();
        foreach ($data['commission_rules'] ?? [] as $rule) {
            $program->commissionRules()->create([
                'product_id' => null, 'event' => 'sale', 'level' => $rule['level'],
                'commission_type' => 'percentage', 'value' => $rule['value'], 'status' => true, 'priority' => 1,
            ]);
        }
        return redirect()->route('business.programs.index')->with('success', 'Affiliate program updated successfully.');
    }

    public function toggleProgram(Request $request, PartnershipProgram $program)
    {
        $program = $this->program($request, $program);
        $program->update(['status' => $program->status === 'active' ? 'paused' : 'active']);
        return back()->with('success', 'Program status updated.');
    }

    public function products(Request $request): View
    {
        $products = Product::where('owner_id', $this->owner($request))
            ->withCount('partnershipPrograms')->with('partnershipPrograms')->latest()->paginate(12);
        return view('business.products.index', compact('products'));
    }

    public function createProduct(): View
    {
        return view('business.products.create');
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products,sku'],
            'price' => ['required', 'numeric', 'min:0'], 'currency' => ['required', 'string', 'max:10'],
            'status' => ['required', 'in:draft,active,inactive'],
        ]);
        $base = Str::slug($data['name']);
        $slug = $base; $i = 1;
        while (Product::where('slug', $slug)->exists()) $slug = $base.'-'.(++$i);
        $data['slug'] = $slug; $data['owner_id'] = $this->owner($request);
        Product::create($data);
        return redirect()->route('business.products.index')->with('success', 'Product created successfully.');
    }

    public function editProduct(Request $request, Product $product): View
    {
        $product = $this->product($request, $product)->load('partnershipPrograms');
        return view('business.products.edit', compact('product'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $product = $this->product($request, $product);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products,sku,'.$product->id],
            'price' => ['required', 'numeric', 'min:0'], 'currency' => ['required', 'string', 'max:10'],
            'status' => ['required', 'in:draft,active,inactive'],
        ]);
        $product->update($data);
        return redirect()->route('business.products.index')->with('success', 'Product updated successfully.');
    }

    public function affiliates(Request $request): View
    {
        $programIds = PartnershipProgram::where('owner_id', $this->owner($request))->pluck('id');
        $affiliates = ProgramPartner::whereIn('program_id', $programIds)
            ->with(['user', 'program'])->withCount(['orders', 'commissions'])
            ->latest()->paginate(20);
        return view('business.affiliates.index', compact('affiliates'));
    }

    public function sales(Request $request): View
    {
        $programIds = PartnershipProgram::where('owner_id', $this->owner($request))->pluck('id');
        $orders = Order::whereIn('program_id', $programIds)
            ->with(['program', 'partner.user', 'customer', 'items.product'])
            ->latest()->paginate(20);
        return view('business.sales.index', compact('orders'));
    }

    public function commissions(Request $request): View
    {
        $programIds = PartnershipProgram::where('owner_id', $this->owner($request))->pluck('id');
        $commissions = Commission::whereIn('program_id', $programIds)
            ->with(['partner.user', 'program', 'order'])->latest()->paginate(20);
        $total = Commission::whereIn('program_id', $programIds)->whereNotIn('status', ['reversed','cancelled'])->sum('commission_amount');
        $paid = Commission::whereIn('program_id', $programIds)->where('status', 'paid')->sum('commission_amount');
        $payable = Commission::whereIn('program_id', $programIds)->whereIn('status', ['available','approved','payable'])->sum('commission_amount');
        return view('business.commissions.index', compact('commissions', 'total', 'paid', 'payable'));
    }
}
