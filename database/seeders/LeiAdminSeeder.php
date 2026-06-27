<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\ApplicationTrendMetric;
use App\Models\DashboardSnapshot;
use App\Models\ServiceHealthCheck;
use App\Models\SystemAlert;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LeiAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'system_id' => 'admin@gmail.com',
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'role' => 'super_admin',
                'tier' => 'tier_1',
                'is_active' => true,
            ]
        );

        $menus = [
            ['Dashboard', 'admin.dashboard', 'dashboard', 1],
            ['User Management', 'admin.users.index', 'users', 2],
            ['Application Management', 'admin.applications.index', 'applications', 3],
            ['Payments', 'admin.payments.index', 'payments', 4],
            ['Advanced Controls', 'admin.controls.index', 'controls', 5],
            ['Environment Management', 'admin.environment.index', 'environment', 6],
            ['Master Data', 'admin.master-data.index', 'database', 7],
            ['Template Management', 'admin.templates.index', 'template', 8],
            ['Registry Services', 'admin.registry.index', 'registry', 9],
            ['Backup', 'admin.backup.index', 'backup', 10],
            ['SLA Metrics', 'admin.sla.index', 'sla', 11],
            ['Security', 'admin.security.index', 'security', 12],
            ['Audit Logs', 'admin.audit.index', 'audit', 13],
            ['Reports & Analytics', 'admin.reports.index', 'reports', 14],
            ['Support', 'admin.support.index', 'support', 15],
            ['Documents', 'admin.documents.index', 'documents', 16],
            ['Notifications', 'admin.notifications.index', 'notifications', 17],
            ['Static Pages', 'admin.static-pages.index', 'pages', 18],
            ['FAQ Management', 'admin.faq.index', 'pages', 19],
            ['Contact Enquiries', 'admin.contact-enquiries.index', 'support', 20],
            ['Subscription Management', 'admin.subscriptions.index', 'payments', 21],
            ['Business Settings', 'admin.business-settings.index', 'settings', 22],
        ];

        AdminMenuItem::query()->where('label', 'Website Management')->delete();

        foreach ($menus as [$label, $route, $icon, $order]) {
            AdminMenuItem::updateOrCreate(
                ['label' => $label],
                ['route_name' => $route, 'icon' => $icon, 'sort_order' => $order, 'is_active' => true]
            );
        }

        SystemAlert::query()->delete();
        SystemAlert::create([
                'type' => 'sla_breach',
                'title' => 'Active SLA Breach',
                'message' => 'Registry response time in Asia-Pacific region exceeded 500ms threshold for 12 minutes. Automated rerouting initiated.',
                'region' => 'Asia-Pacific',
                'severity' => 'high',
                'is_active' => true,
        ]);
        SystemAlert::create([
                'type' => 'security',
                'title' => 'Security Event',
                'message' => 'Unusual login attempt pattern detected from blocked IP range (7.12.x.x). Multi-factor authentication forced globally for Tier 1 Admins.',
                'region' => null,
                'severity' => 'critical',
                'is_active' => true,
        ]);

        $snapshots = [
            ['total_applications', 'Total Applications', '24,892', 24892, '^ 12.5%', 12.5, null, ['sparkline' => [35, 50, 42, 65, 58, 78, 72, 85, 68, 90]]],
            ['pending_approvals', 'Pending Approvals', '142', 142, null, null, 'URGENT', ['subtitle' => 'Requires manual validation']],
            ['active_users', 'Active Users', '1,204', 1204, null, null, null, ['subtitle' => 'Peak load', 'progress' => 84]],
            ['payments_24h', 'Payments (24h)', '₹1,42,500', 142500, null, null, null, [
                'revenue' => '+₹8.2k',
                'refunds' => '-₹1.1k',
            ]],
        ];

        foreach ($snapshots as $row) {
            DashboardSnapshot::updateOrCreate(
                ['metric_key' => $row[0]],
                [
                    'label' => $row[1],
                    'value_display' => $row[2],
                    'value_numeric' => $row[3],
                    'trend_label' => $row[4],
                    'trend_percent' => $row[5],
                    'badge' => $row[6],
                    'meta' => $row[7],
                ]
            );
        }

        ServiceHealthCheck::query()->delete();
        $healthServices = [
            ['service_name' => 'Main Server Cluster', 'service_key' => 'main_server', 'uptime_percent' => 99.98, 'status' => 'healthy', 'load_percent' => 42, 'sort_order' => 1],
            ['service_name' => 'Global SQL Database', 'service_key' => 'global_sql', 'uptime_percent' => 99.99, 'status' => 'healthy', 'load_percent' => 38, 'sort_order' => 2],
            ['service_name' => 'Public API Gateway', 'service_key' => 'public_api', 'uptime_percent' => 98.42, 'status' => 'warning', 'load_percent' => 55, 'sort_order' => 3],
            ['service_name' => 'Identity Service (OIDC)', 'service_key' => 'identity_oidc', 'uptime_percent' => 100.00, 'status' => 'healthy', 'load_percent' => 30, 'sort_order' => 4],
        ];
        foreach ($healthServices as $service) {
            ServiceHealthCheck::create($service);
        }

        ApplicationTrendMetric::query()->delete();
        $months = [
            ['Jan', 1, 4200, 1800],
            ['Feb', 2, 5100, 2100],
            ['Mar', 3, 4800, 2400],
            ['Apr', 4, 6200, 2800],
            ['May', 5, 5800, 3100],
            ['Jun', 6, 7100, 3400],
            ['Jul', 7, 6900, 3600],
            ['Aug', 8, 7800, 3900],
            ['Sep', 9, 8200, 4100],
            ['Oct', 10, 8600, 4300],
        ];

        foreach ($months as [, $month, $main, $partner]) {
            ApplicationTrendMetric::create([
                'year' => 2026,
                'month' => $month,
                'main_registry_count' => $main,
                'partner_api_count' => $partner,
            ]);
        }
    }
}
