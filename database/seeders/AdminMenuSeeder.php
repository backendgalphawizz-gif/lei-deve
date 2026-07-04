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
            ['CA Management', 'admin.certificates.index', 'certificate', 4],
            ['Payments', 'admin.payments.index', 'payments', 5],
            ['Advanced Controls', 'admin.controls.index', 'controls', 6],
            ['Environment Management', 'admin.environment.index', 'environment', 7],
            ['Master Data', 'admin.master-data.index', 'database', 8],
            ['Template Management', 'admin.templates.index', 'template', 9],
            ['Registry Services', 'admin.registry.index', 'registry', 10],
            ['Backup', 'admin.backup.index', 'backup', 11],
            ['SLA Metrics', 'admin.sla.index', 'sla', 12],
            ['Security', 'admin.security.index', 'security', 13],
            ['Audit Logs', 'admin.audit.index', 'audit', 14],
            ['Reports & Analytics', 'admin.reports.index', 'reports', 15],
            ['Support', 'admin.support.index', 'support', 16],
            ['Documents', 'admin.documents.index', 'documents', 17],
            ['Notifications', 'admin.notifications.index', 'notifications', 18],
            ['Static Pages', 'admin.static-pages.index', 'pages', 19],
            ['FAQ Management', 'admin.faq.index', 'pages', 20],
            ['Homepage LEI Content', 'admin.home-content.index', 'pages', 21],
            ['Contact Enquiries', 'admin.contact-enquiries.index', 'support', 22],
            ['Subscription Management', 'admin.subscriptions.index', 'payments', 23],
            ['Business Settings', 'admin.business-settings.index', 'settings', 24],
        ];

        AdminMenuItem::query()->where('label', 'Website Management')->delete();
        AdminMenuItem::query()->where('label', 'Certificate Authority')->delete();

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
