<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiBusinessSetting;
use App\Models\LeiSubscription;
use App\Models\User;
use App\Services\FinancialStatsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentManagementController extends Controller
{
    public function index(Request $request)
    {
        $stats = app(FinancialStatsService::class)->summaryCards();

        $subscriptions = $this->subscriptionQuery($request)
            ->paginate(10)
            ->withQueryString();

        $invoices = LeiSubscription::query()
            ->with('user')
            ->where('payment_status', 'paid')
            ->orderByDesc('starts_at')
            ->limit(12)
            ->get();

        $pendingPayments = LeiSubscription::query()
            ->with('user')
            ->where('payment_status', 'pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.payments.index', [
            'stats' => $stats,
            'subscriptions' => $subscriptions,
            'invoices' => $invoices,
            'pendingPayments' => $pendingPayments,
            'totalInDb' => LeiSubscription::count(),
        ]);
    }

    public function invoice(LeiSubscription $subscription)
    {
        $subscription->load('user');
        $user = $subscription->user ?? new User(['name' => 'Customer', 'email' => '']);
        $businessSettings = LeiBusinessSetting::current();

        $baseAmount = (float) ($subscription->amount ?? 0);
        $gstAmount = round($baseAmount * 0.18, 2);
        $totalAmount = $baseAmount + $gstAmount;

        $pdf = Pdf::loadView('applicant.payments.invoice', compact(
            'subscription', 'user', 'businessSettings',
            'baseAmount', 'gstAmount', 'totalAmount',
        ))
            ->setPaper('A4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false]);

        return $pdf->download('Invoice-'.$subscription->reference.'.pdf');
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'subscriptions-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Reference', 'Applicant', 'Plan', 'Amount', 'Payment Status', 'Subscription Status', 'Date']);

            $this->subscriptionQuery($request)->orderByDesc('starts_at')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->reference,
                        $row->user?->name ?? '—',
                        $row->plan_name,
                        $row->formattedAmount(),
                        $row->payment_status,
                        $row->status,
                        $row->starts_at?->format('Y-m-d H:i') ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function reconcile(Request $request)
    {
        return back()->with('info', 'Reconciliation is not required — payments are recorded directly from applicant subscriptions.');
    }

    public function refundAction(Request $request, \App\Models\LeiRefundRequest $refund)
    {
        return back()->with('info', 'Refund workflow is managed through subscription status updates.');
    }

    private function subscriptionQuery(Request $request)
    {
        $query = LeiSubscription::query()
            ->with(['user', 'pricingPlan'])
            ->orderByDesc('starts_at');

        if ($status = $request->string('status')->toString()) {
            $query->where('payment_status', $status);
        }

        $period = $request->string('period', 'all')->toString();
        $since = match ($period) {
            '24h' => Carbon::now()->subDay(),
            '7d' => Carbon::now()->subDays(7),
            '30d' => Carbon::now()->subDays(30),
            default => null,
        };

        if ($since) {
            $query->where('starts_at', '>=', $since);
        }

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where(function ($inner) use ($q) {
                $inner->where('reference', 'like', "%{$q}%")
                    ->orWhere('plan_name', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
            });
        }

        return $query;
    }
}
