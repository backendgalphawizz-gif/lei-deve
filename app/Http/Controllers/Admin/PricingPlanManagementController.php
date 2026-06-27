<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiPricingPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PricingPlanManagementController extends Controller
{
    public function create()
    {
        return view('admin.subscriptions.plans.create', [
            'plan' => new LeiPricingPlan,
        ]);
    }

    public function edit(LeiPricingPlan $plan)
    {
        return view('admin.subscriptions.plans.edit', compact('plan'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedPlan($request);

        LeiPricingPlan::create([
            ...$data,
            'sort_order' => (int) LeiPricingPlan::max('sort_order') + 1,
        ]);

        return redirect()
            ->route('admin.subscriptions.index', ['tab' => 'plans'])
            ->with('success', 'Pricing plan created. It will appear on the public pricing page when active.');
    }

    public function update(Request $request, LeiPricingPlan $plan)
    {
        $plan->update($this->validatedPlan($request));

        return redirect()
            ->route('admin.pricing-plans.edit', $plan)
            ->with('success', 'Pricing plan updated.');
    }

    public function destroy(LeiPricingPlan $plan)
    {
        $plan->delete();

        return redirect()
            ->route('admin.subscriptions.index', ['tab' => 'plans'])
            ->with('success', 'Pricing plan removed.');
    }

    private function validatedPlan(Request $request): array
    {
        $data = $request->validate([
            'section' => ['required', Rule::in(['registration', 'renewal'])],
            'label' => ['nullable', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_years' => ['required', 'integer', 'min:1', 'max:10'],
            'price_suffix' => ['nullable', 'string', 'max:32'],
            'savings_label' => ['nullable', 'string', 'max:120'],
            'features' => ['nullable', 'string', 'max:5000'],
            'button_text' => ['nullable', 'string', 'max:64'],
            'button_style' => ['nullable', Rule::in(['solid', 'outline'])],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $features = collect(preg_split('/\r\n|\r|\n/', $data['features'] ?? ''))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->map(fn ($text) => ['text' => $text, 'included' => true])
            ->all();

        return [
            'section' => $data['section'],
            'label' => $data['label'] ?? null,
            'name' => $data['name'],
            'price' => $data['price'],
            'duration_years' => $data['duration_years'],
            'price_suffix' => $data['price_suffix'] ?: '/ entity',
            'savings_label' => $data['savings_label'] ?? null,
            'features' => $features,
            'button_text' => $data['button_text'] ?: 'Select Plan',
            'button_style' => $data['button_style'] ?: 'outline',
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
