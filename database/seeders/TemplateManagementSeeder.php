<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiWorkflowState;
use App\Models\LeiWorkflowTemplate;
use Illuminate\Database\Seeder;

class TemplateManagementSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Template Management')->update([
            'route_name' => 'admin.templates.index',
        ]);

        LeiWorkflowTemplate::query()->update(['is_active' => false]);

        $template = LeiWorkflowTemplate::create([
            'name' => 'Enterprise Security Clearance',
            'module' => 'registry_services',
            'initial_state' => 'Draft',
            'sla_hours' => 48,
            'total_nodes_label' => '4 States',
            'escalation_depth' => 'L3 Authority',
            'automation_tier' => 'Full-Registry Sync',
            'last_synced_at' => now()->setTime(14, 32),
            'is_active' => true,
        ]);

        $states = [
            [
                'rule_label' => 'INITIAL STATE',
                'title' => 'Draft Submission',
                'description' => 'Admin generates initial record request',
                'accent' => 'core',
                'rule_type' => 'initial',
                'sort_order' => 1,
            ],
            [
                'rule_label' => 'TRANSITION RULE: AUTO',
                'title' => 'Security Screening',
                'description' => 'Automated validation of registry data',
                'accent' => 'auto',
                'rule_type' => 'transition',
                'sort_order' => 2,
            ],
            [
                'rule_label' => 'TRANSITION RULE: MANUAL',
                'title' => 'Peer Review',
                'description' => 'L2 Admin manual sign-off required',
                'accent' => 'approval',
                'rule_type' => 'transition',
                'sort_order' => 3,
            ],
            [
                'rule_label' => null,
                'title' => 'Define Final State',
                'description' => null,
                'accent' => 'core',
                'rule_type' => 'final_placeholder',
                'sort_order' => 99,
            ],
        ];

        foreach ($states as $row) {
            LeiWorkflowState::create(array_merge($row, ['template_id' => $template->id]));
        }
    }
}
