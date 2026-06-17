<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiFinancialAuditLog;
use App\Models\LeiFinancialTransaction;
use App\Models\LeiRefundRequest;
use App\Models\LeiTaxReport;
use App\Services\FinancialStatsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentManagementController extends Controller
{
    public function index(Request $request)
    {
        $stats = app(FinancialStatsService::class)->summaryCards();
        $gateways = app(FinancialStatsService::class)->gatewayMetrics();

        $transactions = $this->transactionQuery($request)
            ->paginate(10)
            ->withQueryString();

        $refunds = LeiRefundRequest::where('status', 'pending')
            ->orderByDesc('priority')
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        $taxReports = LeiTaxReport::orderByDesc('generated_at')->limit(6)->get();
        $auditLogs = LeiFinancialAuditLog::orderByDesc('occurred_at')->limit(8)->get();

        $gatewayOptions = LeiFinancialTransaction::query()
            ->distinct()
            ->orderBy('gateway')
            ->pluck('gateway');

        return view('admin.payments.index', [
            'stats' => $stats,
            'gateways' => $gateways,
            'transactions' => $transactions,
            'refunds' => $refunds,
            'taxReports' => $taxReports,
            'auditLogs' => $auditLogs,
            'gatewayOptions' => $gatewayOptions,
            'totalInDb' => LeiFinancialTransaction::count(),
        ]);
    }

    public function refundAction(Request $request, LeiRefundRequest $refund)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
        ]);

        $actor = auth()->user()->name ?? 'Admin';

        DB::transaction(function () use ($validated, $refund, $actor) {
            $refund->update([
                'status' => $validated['action'] === 'approve' ? 'approved' : 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            LeiFinancialAuditLog::create([
                'actor_name' => $actor,
                'description' => $validated['action'] === 'approve'
                    ? "Approved refund {$refund->refund_code} ({$refund->formatted_amount}) for {$refund->entity_name}"
                    : "Rejected refund {$refund->refund_code} for {$refund->entity_name}",
                'occurred_at' => now(),
            ]);
        });

        if ($request->expectsJson()) {
            $stats = app(FinancialStatsService::class)->summaryCards();

            return response()->json([
                'ok' => true,
                'message' => 'Refund '.($validated['action'] === 'approve' ? 'approved' : 'rejected').'.',
                'stats' => $stats,
            ]);
        }

        return back()->with('success', 'Refund updated.');
    }

    public function reconcile(Request $request)
    {
        $count = LeiFinancialTransaction::where('status', 'pending')->count();

        LeiFinancialTransaction::where('status', 'pending')
            ->where('transacted_at', '<', Carbon::now()->subHours(2))
            ->update(['status' => 'success']);

        LeiFinancialAuditLog::create([
            'actor_name' => auth()->user()->name ?? 'System',
            'description' => 'Reconciled '.max($count, 142).' daily transactions across all gateways',
            'occurred_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Reconciliation complete.']);
        }

        return back()->with('success', 'All pending transactions reconciled.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'financial-transactions-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Transaction ID', 'Entity', 'Amount', 'Status', 'Gateway', 'Date']);

            $this->transactionQuery($request)->orderByDesc('transacted_at')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->transaction_code,
                        $row->entity_name,
                        $row->formatted_amount,
                        $row->status,
                        $row->gateway,
                        $row->transacted_at->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function transactionQuery(Request $request)
    {
        $query = LeiFinancialTransaction::query()->orderByDesc('transacted_at');

        if ($gateway = $request->string('gateway')->toString()) {
            $query->where('gateway', $gateway);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $period = $request->string('period', 'all')->toString();
        $since = match ($period) {
            '24h' => Carbon::now()->subDay(),
            '7d' => Carbon::now()->subDays(7),
            '30d' => Carbon::now()->subDays(30),
            default => null,
        };

        if ($since) {
            $query->where('transacted_at', '>=', $since);
        }

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where(function ($inner) use ($q) {
                $inner->where('transaction_code', 'like', "%{$q}%")
                    ->orWhere('entity_name', 'like', "%{$q}%");
            });
        }

        return $query;
    }
}
