<?php

namespace App\Http\Controllers;

use App\Models\PartnershipProgram;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BusinessOnboardingController extends Controller
{
    private const STEPS = ['profile' => 'Business profile', 'product' => 'First product', 'commission' => 'Commission', 'publish' => 'Publish'];

    public function start(Request $request): RedirectResponse|View
    {
        if (!$request->user()) {
            $request->session()->put('business_onboarding_intent', true);
            return redirect()->route('register');
        }
        return redirect()->route('business.onboarding', ['step' => 'profile']);
    }

    public function show(Request $request, string $step): View|RedirectResponse
    {
        abort_unless(array_key_exists($step, self::STEPS), 404);
        $data = $request->session()->get('business_onboarding', []);
        $user = $request->user();

        if ($step === 'profile' && empty($data['profile'])) {
            $data['profile'] = [
                'business_name' => $user->business_name,
                'business_website' => $user->business_website,
                'business_industry' => $user->business_industry,
                'business_phone' => $user->business_phone,
            ];
        }
        if ($step !== 'profile' && empty($data['profile']['business_name'])) return redirect()->route('business.onboarding', ['step' => 'profile']);
        if ($step === 'commission' && empty($data['commission']['level_1'])) {
            $data['commission']['level_1'] = 20;
            $data['commission']['level_2'] = 5;
            $data['commission']['level_3'] = 0;
        }
        if ($step === 'publish' && empty($data['product']['name'])) return redirect()->route('business.onboarding', ['step' => 'product']);

        return view('business.onboarding', ['step' => $step, 'steps' => self::STEPS, 'data' => $data]);
    }

    public function store(Request $request, string $step): RedirectResponse
    {
        abort_unless(array_key_exists($step, self::STEPS), 404);
        $data = $request->session()->get('business_onboarding', []);

        if ($step === 'profile') {
            $validated = $request->validate([
                'business_name' => ['required', 'string', 'max:255'],
                'business_website' => ['nullable', 'url', 'max:255'],
                'business_industry' => ['required', 'string', 'max:100'],
                'business_phone' => ['nullable', 'string', 'max:40'],
            ]);
            $request->user()->update($validated);
            $data['profile'] = $validated;
            $request->session()->put('business_onboarding', $data);
            return redirect()->route('business.onboarding', ['step' => 'product']);
        }

        if ($step === 'product') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:5000'],
                'price' => ['required', 'numeric', 'min:0'],
                'currency' => ['required', 'string', 'size:3'],
            ]);
            $validated['slug'] = Str::slug($validated['name']);
            $base = $validated['slug'];
            $counter = 1;
            while (Product::where('slug', $validated['slug'])->exists()) $validated['slug'] = $base . '-' . $counter++;
            $data['product'] = $validated;
            $request->session()->put('business_onboarding', $data);
            return redirect()->route('business.onboarding', ['step' => 'commission']);
        }

        if ($step === 'commission') {
            $validated = $request->validate([
                'level_1' => ['required', 'numeric', 'gt:0', 'max:100'],
                'level_2' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'level_3' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'attribution_window_days' => ['required', 'integer', 'min:1', 'max:365'],
                'minimum_payout' => ['required', 'numeric', 'min:0'],
            ]);
            $data['commission'] = $validated;
            $request->session()->put('business_onboarding', $data);
            return redirect()->route('business.onboarding', ['step' => 'publish']);
        }

        $request->validate(['publish' => ['accepted']]);
        if (empty($data['profile']['business_name']) || empty($data['product']['name']) || empty($data['commission']['level_1'])) {
            return redirect()->route('business.onboarding', ['step' => 'profile']);
        }

        DB::transaction(function () use ($data, $request) {
            $product = Product::create([
                'owner_id' => $request->user()->id,
                'name' => $data['product']['name'],
                'slug' => $data['product']['slug'],
                'description' => $data['product']['description'] ?? null,
                'price' => $data['product']['price'],
                'currency' => strtoupper($data['product']['currency']),
                'status' => 'active',
            ]);

            $baseSlug = Str::slug($data['profile']['business_name'] . '-affiliate-program');
            $slug = $baseSlug;
            $counter = 1;
            while (PartnershipProgram::where('slug', $slug)->exists()) $slug = $baseSlug . '-' . $counter++;

            $program = PartnershipProgram::create([
                'owner_id' => $request->user()->id,
                'name' => $data['profile']['business_name'] . ' Affiliate Program',
                'slug' => $slug,
                'description' => 'Affiliate program for ' . $data['profile']['business_name'],
                'status' => 'active',
                'attribution_window_days' => $data['commission']['attribution_window_days'],
                'minimum_payout' => $data['commission']['minimum_payout'],
            ]);

            $program->products()->sync([$product->id]);

            foreach ([1 => $data['commission']['level_1'], 2 => $data['commission']['level_2'] ?? 0, 3 => $data['commission']['level_3'] ?? 0] as $level => $value) {
                if ((float) $value <= 0) continue;
                $program->commissionRules()->create([
                    'product_id' => $product->id,
                    'event' => 'sale',
                    'level' => $level,
                    'commission_type' => 'percentage',
                    'value' => $value,
                    'status' => true,
                    'priority' => 1,
                ]);
            }
        });

        $request->session()->forget(['business_onboarding', 'business_onboarding_intent']);
        return redirect()->route('business.dashboard')->with('success', 'Your affiliate program is live.');
    }
}
