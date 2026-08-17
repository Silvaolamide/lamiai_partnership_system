<?php

namespace App\Http\Controllers;

use App\Models\PartnershipProgram;
use Illuminate\Http\Request;

class PartnershipProgramController extends Controller
{
    public function index()
    {
        $programs = PartnershipProgram::latest()->get();

        return view('programs.index', [
            'programs' => $programs,
        ]);
    }

    public function create()
    {
        return view('programs.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:partnership_programs,slug'],
            'description' => ['nullable', 'string'],
            'attribution_window_days' => ['required', 'integer', 'min:1'],
            'minimum_payout' => ['required', 'numeric', 'min:0'],
        ]);

        PartnershipProgram::create($validated);

        return redirect()
            ->route('admin.programs.index')
            ->with('success', 'Partnership program created successfully.');
    }

    public function edit(PartnershipProgram $program)
    {
        $program->load([
            'products',
            'commissionRules',
        ]);

        $products = \App\Models\Product::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('programs.edit', [
            'program' => $program,
            'products' => $products,
        ]);
    }

    public function update(Request $request, PartnershipProgram $program)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:partnership_programs,slug,' . $program->id,
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string'],
            'attribution_window_days' => ['required', 'integer', 'min:1'],
            'minimum_payout' => ['required', 'numeric', 'min:0'],

            'products' => ['nullable', 'array'],
            'products.*' => ['exists:products,id'],

            'commission_rules' => ['nullable', 'array'],
            'commission_rules.*.level' => ['required', 'integer', 'min:0'],
            'commission_rules.*.value' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $program->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'attribution_window_days' => $validated['attribution_window_days'],
            'minimum_payout' => $validated['minimum_payout'],
        ]);

        $program->products()->sync(
            $validated['products'] ?? []
        );

        $program->commissionRules()->delete();

        foreach ($validated['commission_rules'] ?? [] as $rule) {
            $program->commissionRules()->create([
                'product_id' => null,
                'event' => 'sale',
                'level' => $rule['level'],
                'commission_type' => 'percentage',
                'value' => $rule['value'],
                'status' => true,
                'priority' => 1,
            ]);
        }

        return redirect()
            ->route('admin.programs.index')
            ->with('success', 'Partnership program updated successfully.');
    }
    
    
}