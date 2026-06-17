<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiActivePipeline;
use App\Models\LeiDeploymentArtifact;
use App\Models\LeiDeploymentRecord;
use App\Models\LeiPendingRelease;
use App\Models\LeiRegistryEnvironment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EnvironmentManagementSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Environment Management')->update([
            'route_name' => 'admin.environment.index',
        ]);

        $envs = [
            ['prod', 'PRODUCTION', '100% Uptime', 'green', 'v2.4.1-rc3', 'Deployed 14h ago by @admin_sys', 'HEALTH STATUS:', 'OPTIMAL', 'green', 1],
            ['uat', 'UAT', '99.9% Uptime', 'green', 'v2.4.2-beta', 'Deployed 2h ago by @ci_bot', 'STAKEHOLDER SIGN-OFF:', 'PENDING', 'yellow', 2],
            ['qa', 'QA', '92.4% Uptime', 'orange', 'v2.4.2-alpha', 'Deployed 5m ago by @dev_lead', 'LOAD TESTS:', 'RUNNING', 'orange', 3],
            ['dev', 'DEV', 'Active', 'blue', 'v2.5.0-dev', 'Deployed 45m ago by @auto_merger', 'ACTIVE BRANCHES:', '12 ACTIVE', 'blue', 4],
        ];

        foreach ($envs as $row) {
            LeiRegistryEnvironment::updateOrCreate(
                ['env_key' => $row[0]],
                [
                    'label' => $row[1],
                    'uptime_display' => $row[2],
                    'status_tone' => $row[3],
                    'version' => $row[4],
                    'deployed_meta' => $row[5],
                    'footer_label' => $row[6],
                    'footer_value' => $row[7],
                    'footer_tone' => $row[8],
                    'sort_order' => $row[9],
                ]
            );
        }

        LeiActivePipeline::updateOrCreate(
            ['build_number' => 'Build #8824'],
            [
                'target_environment' => 'UAT Environment',
                'steps' => [
                    ['name' => 'Build', 'status' => 'passed', 'detail' => 'Passed (2m)'],
                    ['name' => 'Unit Test', 'status' => 'passed', 'detail' => 'Passed (5m)'],
                    ['name' => 'Security', 'status' => 'active', 'detail' => 'Scanning...'],
                    ['name' => 'Deploy', 'status' => 'waiting', 'detail' => 'Waiting'],
                ],
                'progress_percent' => 64,
                'progress_label' => 'Step progress: Security Vulnerability Scan (Static Analysis)',
                'is_active' => true,
            ]
        );

        if (LeiDeploymentRecord::count() === 0) {
            LeiDeploymentRecord::insert([
                ['environment' => 'prod', 'environment_tone' => 'gray', 'version' => 'v2.4.1-rc3', 'administrator' => 'S. Admin', 'auth_id' => '992', 'deployed_at' => Carbon::parse('2023-10-24 08:30:12'), 'status' => 'success', 'status_detail' => null, 'created_at' => now(), 'updated_at' => now()],
                ['environment' => 'uat', 'environment_tone' => 'orange', 'version' => 'v2.4.2-beta', 'administrator' => 'CI/CD Pipeline System', 'auth_id' => null, 'deployed_at' => Carbon::parse('2023-10-24 06:15:45'), 'status' => 'success', 'status_detail' => null, 'created_at' => now(), 'updated_at' => now()],
                ['environment' => 'prod', 'environment_tone' => 'gray', 'version' => 'v2.4.1-rc2', 'administrator' => 'S. Admin', 'auth_id' => '992', 'deployed_at' => Carbon::parse('2023-10-23 23:44:02'), 'status' => 'failed', 'status_detail' => 'SLA-X', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        LeiPendingRelease::updateOrCreate(
            ['title' => 'Registry v2.4.2'],
            ['badge' => 'critical', 'description' => 'Security patch for API handshakes. Requires manual approval.', 'approval_status' => 'pending', 'sort_order' => 1]
        );
        LeiPendingRelease::updateOrCreate(
            ['title' => 'Metrics v1.0.8'],
            ['badge' => 'feature', 'description' => 'Enhanced dashboarding for throughput monitoring.', 'approval_status' => 'pending', 'sort_order' => 2]
        );

        LeiDeploymentArtifact::updateOrCreate(
            ['filename' => 'core-engine-8824.zip'],
            ['version_label' => 'v2.4.2-beta', 'size_display' => '142MB', 'sort_order' => 1]
        );
        LeiDeploymentArtifact::updateOrCreate(
            ['filename' => 'ui-bundle-8821.zip'],
            ['version_label' => 'v2.4.1-rc3', 'size_display' => '89MB', 'sort_order' => 2]
        );
    }
}
