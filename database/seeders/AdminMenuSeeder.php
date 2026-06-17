<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use Illuminate\Database\Seeder;

class AdminMenuSeeder extends Seeder
{
    public function run(): void
    {
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
            ['Business Settings', 'admin.business-settings.index', 'settings', 19],
        ];

        foreach ($menus as [$label, $route, $icon, $order]) {
            AdminMenuItem::updateOrCreate(
                ['label' => $label],
                [
                    'route_name' => $route,
                    'icon' => $icon,
                    'sort_order' => $order,
                    'is_active' => true,
                ]
            );
        }
    }
}
