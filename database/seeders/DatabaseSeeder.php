<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// UserManagementSeeder loaded via call array below

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LeiAdminSeeder::class,
            AdminMenuSeeder::class,
            UserManagementSeeder::class,
            ApplicationManagementSeeder::class,
            PaymentManagementSeeder::class,
            AdvancedControlsSeeder::class,
            EnvironmentManagementSeeder::class,
            MasterDataSeeder::class,
            TemplateManagementSeeder::class,
            RegistryManagementSeeder::class,
            BackupManagementSeeder::class,
            SlaMetricsSeeder::class,
            SecurityManagementSeeder::class,
            AuditLogsSeeder::class,
            ReportsAnalyticsSeeder::class,
            SupportManagementSeeder::class,
            DocumentManagementSeeder::class,
            NotificationManagementSeeder::class,
            StaticPageSeeder::class,
            BusinessSettingsSeeder::class,
            WebsiteSeeder::class,
            HomeLeiContentSeeder::class,
        ]);
    }
}
