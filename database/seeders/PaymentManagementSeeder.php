<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiFinancialAuditLog;
use App\Models\LeiFinancialTransaction;
use App\Models\LeiPaymentGateway;
use App\Models\LeiRefundRequest;
use App\Models\LeiTaxReport;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PaymentManagementSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Payments')->update([
            'route_name' => 'admin.payments.index',
        ]);

        LeiPaymentGateway::query()->delete();
        LeiPaymentGateway::insert([
            ['name' => 'Stripe API', 'gateway_key' => 'stripe', 'latency_ms' => 142, 'health_percent' => 92, 'status' => 'healthy', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PayPal Gateway', 'gateway_key' => 'paypal', 'latency_ms' => 385, 'health_percent' => 58, 'status' => 'warning', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        if (LeiFinancialTransaction::count() < 100) {
            $this->seedTransactions();
        } else {
            $this->refreshTransactionTimestamps();
        }

        $this->ensurePendingRefunds();
        $this->seedTaxReports();
        $this->seedAuditLogsIfEmpty();
    }

    private function seedTransactions(): void
    {
        LeiFinancialTransaction::query()->delete();

        $featured = [
            ['TRX-94821-229', 'Global Tech Corp', 1200.00, 'success', 'stripe', 2],
            ['TRX-94821-230', 'Aether Logistix', 450.00, 'failed', 'paypal', 5],
            ['TRX-94821-231', 'Summit Bio-Industries', 8900.00, 'pending', 'stripe', 8],
        ];

        foreach ($featured as [$code, $entity, $amount, $status, $gateway, $hoursAgo]) {
            LeiFinancialTransaction::create([
                'transaction_code' => $code,
                'entity_name' => $entity,
                'amount' => $amount,
                'currency' => 'INR',
                'status' => $status,
                'gateway' => $gateway,
                'transacted_at' => now()->subHours($hoursAgo),
            ]);
        }

        $entities = [
            'Nova Dynamics', 'Zen Flow', 'Global Tech Corp', 'Aether Logistix', 'Summit Bio-Industries',
            'Pacific Trade Holdings', 'Nordic Energy AS', 'Americas Logistics LLC', 'Vertex Capital',
            'Helix Manufacturing', 'Orion Payments Ltd', 'Blue Harbor Finance',
        ];
        $statuses = ['success', 'success', 'success', 'success', 'pending', 'failed'];
        $gateways = ['stripe', 'paypal', 'stripe'];

        for ($i = 232; $i <= 710; $i++) {
            LeiFinancialTransaction::create([
                'transaction_code' => 'TRX-94821-'.$i,
                'entity_name' => $entities[$i % count($entities)],
                'amount' => round(200 + ($i % 97) * 47.5, 2),
                'currency' => 'INR',
                'status' => $statuses[$i % count($statuses)],
                'gateway' => $gateways[$i % count($gateways)],
                'transacted_at' => now(),
            ]);
        }

        $this->refreshTransactionTimestamps();
    }

    private function refreshTransactionTimestamps(): void
    {
        $rows = LeiFinancialTransaction::orderByDesc('id')->get();

        foreach ($rows as $index => $row) {
            $hours = match (true) {
                $index < 150 => $index % 23,
                $index < 350 => 24 + ($index % 144),
                default => 168 + ($index % 360),
            };

            $row->update([
                'transacted_at' => now()->subHours($hours)->subMinutes($index % 55),
            ]);
        }

        foreach ([
            'TRX-94821-229' => 2,
            'TRX-94821-230' => 5,
            'TRX-94821-231' => 8,
        ] as $code => $hours) {
            LeiFinancialTransaction::where('transaction_code', $code)->update([
                'transacted_at' => now()->subHours($hours),
            ]);
        }
    }

    private function ensurePendingRefunds(): void
    {
        $refunds = [
            ['REF-2023-019', 'Nova Dynamics', 850.00, 'Duplicate charge occurred due to gateway timeout during multi-year LEI renewal.', 'high', 4.2],
            ['REF-2023-020', 'Zen Flow', 120.00, 'Customer requested partial refund after duplicate submission.', 'high', 3.8],
            ['REF-2023-021', 'Vertex Capital', 500.00, 'Billing mismatch on annual registry fee.', 'normal', 5.1],
            ['REF-2023-022', 'Helix Manufacturing', 2400.00, 'Entity dissolved — full renewal fee reversal requested.', 'high', 6.0],
        ];

        foreach ($refunds as [$code, $entity, $amount, $reason, $priority, $hours]) {
            LeiRefundRequest::updateOrCreate(
                ['refund_code' => $code],
                [
                    'entity_name' => $entity,
                    'amount' => $amount,
                    'reason' => $reason,
                    'status' => 'pending',
                    'priority' => $priority,
                    'avg_response_hours' => $hours,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ]
            );
        }

        LeiRefundRequest::updateOrCreate(
            ['refund_code' => 'REF-2023-012'],
            [
                'entity_name' => 'Entity X-Labs',
                'amount' => 500.00,
                'reason' => 'Lack of documentation for chargeback.',
                'status' => 'rejected',
                'priority' => 'normal',
                'reviewed_at' => Carbon::now()->subHours(2),
            ]
        );
    }

    private function seedTaxReports(): void
    {
        LeiTaxReport::updateOrCreate(
            ['filename' => 'VAT_REPORT_2023_Q3.pdf'],
            [
                'title' => 'VAT Report Q3',
                'file_size_display' => '1.2 MB',
                'file_type' => 'pdf',
                'quarter_label' => 'Q3 2023',
                'generated_at' => Carbon::parse('2023-10-30'),
            ]
        );

        LeiTaxReport::updateOrCreate(
            ['filename' => 'TAX_LIABILITY_SUMMARY.xlsx'],
            [
                'title' => 'Tax Liability Summary',
                'file_size_display' => '4.5 MB',
                'file_type' => 'xlsx',
                'quarter_label' => 'Q3 2023',
                'generated_at' => Carbon::parse('2023-11-01'),
            ]
        );
    }

    private function seedAuditLogsIfEmpty(): void
    {
        if (LeiFinancialAuditLog::count() > 0) {
            return;
        }

        $logs = [
            ['Admin Sarah W.', 'approved refund of ₹500 to Entity X-Labs', Carbon::now()->subMinutes(2)],
            ['System', 'automatically reconciled 142 daily transactions', Carbon::now()->subMinutes(45)],
            ['Admin Mike R.', 'rejected refund #REF-012 due to lack of documentation', Carbon::now()->subHours(2)],
            ['Admin Sarah W.', 'exported financial report for Q3 2023', Carbon::now()->subHours(5)],
            ['System', 'Stripe API latency spike detected (385ms threshold)', Carbon::now()->subHours(8)],
        ];

        foreach ($logs as [$actor, $desc, $at]) {
            LeiFinancialAuditLog::create([
                'actor_name' => $actor,
                'description' => $desc,
                'occurred_at' => $at,
            ]);
        }
    }
}
