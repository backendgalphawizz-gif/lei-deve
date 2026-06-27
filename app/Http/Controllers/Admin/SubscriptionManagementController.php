<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiPricingPlan;
use App\Models\LeiSubscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionManagementController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions) {}

    public function index(Request $request)
    {
        $tab = $request->string('tab')->trim()->toString() ?: 'plans';

        $pricingPlans = LeiPricingPlan::query()->orderBy('section')->orderBy('sort_order')->paginate(15, ['*'], 'plans_page')->withQueryString();

        $query = LeiSubscription::query()->with(['user', 'pricingPlan'])->latest();

        if ($status = $request->string('status')->trim()->toString()) {
            if (array_key_exists($status, LeiSubscription::statuses())) {
                $query->where('status', $status);
            }
        }

        if ($payment = $request->string('payment')->trim()->toString()) {
            if (array_key_exists($payment, LeiSubscription::paymentStatuses())) {
                $query->where('payment_status', $payment);
            }
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('plan_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $subscriptions = $query->paginate(15)->withQueryString();

        return view('admin.subscriptions.index', [
            'tab' => $tab,
            'subscriptions' => $subscriptions,
            'pricingPlans' => $pricingPlans,
            'stats' => [
                'total' => LeiSubscription::count(),
                'active' => LeiSubscription::where('status', 'active')->count(),
                'pending' => LeiSubscription::where('status', 'pending')->count(),
                'revenue' => LeiSubscription::where('payment_status', 'paid')->sum('amount'),
                'plans' => LeiPricingPlan::where('is_active', true)->count(),
            ],
            'statuses' => LeiSubscription::statuses(),
            'paymentStatuses' => LeiSubscription::paymentStatuses(),
        ]);
    }

    public function create()
    {
        return view('admin.subscriptions.create', [
            'subscription' => new LeiSubscription,
            'applicants' => User::query()
                ->where('role', 'applicant')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'pricingPlans' => LeiPricingPlan::query()->orderBy('sort_order')->get(),
            'statuses' => LeiSubscription::statuses(),
            'paymentStatuses' => LeiSubscription::paymentStatuses(),
        ]);
    }

    public function edit(LeiSubscription $subscription)
    {
        $subscription->load(['user', 'pricingPlan']);

        return view('admin.subscriptions.edit', [
            'subscription' => $subscription,
            'statuses' => LeiSubscription::statuses(),
            'paymentStatuses' => LeiSubscription::paymentStatuses(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'pricing_plan_id' => ['nullable', 'exists:lei_pricing_plans,id'],
            'plan_name' => ['nullable', 'string', 'max:120'],
            'plan_section' => ['nullable', Rule::in(['registration', 'renewal'])],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'duration_years' => ['nullable', 'integer', 'min:1', 'max:10'],
            'status' => ['required', Rule::in(array_keys(LeiSubscription::statuses()))],
            'payment_status' => ['required', Rule::in(array_keys(LeiSubscription::paymentStatuses()))],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $user = User::query()
            ->where('id', $data['user_id'])
            ->where('role', 'applicant')
            ->firstOrFail();

        $subscription = $this->subscriptions->createFromAdmin($user, $data);

        return redirect()
            ->route('admin.subscriptions.edit', $subscription)
            ->with('success', 'Subscription created. It is now visible on the applicant portal.');
    }

    public function update(Request $request, LeiSubscription $subscription)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(LeiSubscription::statuses()))],
            'payment_status' => ['required', Rule::in(array_keys(LeiSubscription::paymentStatuses()))],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $subscription->update($data);

        return redirect()
            ->route('admin.subscriptions.edit', $subscription)
            ->with('success', 'Subscription updated.');
    }
}
