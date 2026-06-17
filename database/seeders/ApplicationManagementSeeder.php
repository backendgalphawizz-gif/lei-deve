<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiApplication;
use App\Models\LeiApplicationAuditEvent;
use Illuminate\Database\Seeder;

class ApplicationManagementSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Application Management')->update([
            'route_name' => 'admin.applications.index',
        ]);

        $samples = [
            [
                'LEI-98234-A',
                'Global FinTech Corp',
                'United Kingdom',
                'Direct Issuance',
                'under_review',
                'high',
                'Tier 1 Review',
                '2023-10-26',
                [
                    ['2023-10-26 14:22:00', "Application moved to 'Under Review' by System Admin", true],
                    ['2023-10-25 09:10:00', 'Documents validated by Compliance Bot', false],
                    ['2023-10-24 16:45:00', 'Application submitted by entity portal', false],
                ],
            ],
            [
                'LEI-98201-B',
                'Pacific Trade Holdings',
                'Singapore',
                'Renewal',
                'clarification',
                'med',
                'APAC Desk',
                '2023-10-25',
                [
                    ['2023-10-25 11:00:00', 'Clarification requested for ownership structure', true],
                    ['2023-10-24 08:30:00', 'Assigned to APAC Desk', false],
                ],
            ],
            [
                'LEI-98188-C',
                'Nordic Energy AS',
                'Norway',
                'Direct Issuance',
                'new',
                'low',
                'EU Registry',
                '2023-10-24',
                [
                    ['2023-10-24 10:15:00', 'New application received', true],
                ],
            ],
            [
                'LEI-98155-D',
                'Americas Logistics LLC',
                'United States',
                'Transfer',
                'under_review',
                'high',
                'Americas Hub',
                '2023-10-23',
                [
                    ['2023-10-23 15:40:00', 'Transfer request under compliance review', true],
                    ['2023-10-22 12:00:00', 'Prior LEI record linked', false],
                ],
            ],
        ];

        $extraStatuses = [
            ['pending', 'med', 'Global Ops'],
            ['approved', 'low', 'EU Registry'],
            ['rejected', 'high', 'Risk Team'],
        ];

        foreach ($samples as $i => $row) {
            [$code, $entity, $country, $type, $status, $priority, $team, $date, $events] = $row;

            $app = LeiApplication::updateOrCreate(
                ['application_code' => $code],
                [
                    'entity_name' => $entity,
                    'country' => $country,
                    'issuance_type' => $type,
                    'status' => $status,
                    'priority' => $priority,
                    'assigned_team' => $team,
                    'submitted_on' => $date,
                ]
            );

            LeiApplicationAuditEvent::where('lei_application_id', $app->id)->delete();
            foreach ($events as $j => [$at, $desc, $highlight]) {
                LeiApplicationAuditEvent::create([
                    'lei_application_id' => $app->id,
                    'occurred_at' => $at,
                    'description' => $desc,
                    'actor' => 'System Admin',
                    'is_highlight' => $highlight,
                    'sort_order' => $j,
                ]);
            }
        }

        for ($n = 5; $n <= 89; $n++) {
            $statusPick = ['new', 'pending', 'under_review', 'clarification', 'approved', 'rejected'][$n % 6];
            [$st, $pr, $tm] = $extraStatuses[$n % 3];
            $code = 'LEI-'.(98000 + $n).'-X';

            LeiApplication::updateOrCreate(
                ['application_code' => $code],
                [
                    'entity_name' => 'Registry Entity '.$n,
                    'country' => 'Germany',
                    'issuance_type' => 'Direct Issuance',
                    'status' => $n % 4 === 0 ? $st : $statusPick,
                    'priority' => $pr,
                    'assigned_team' => $tm,
                    'submitted_on' => now()->subDays($n)->format('Y-m-d'),
                ]
            );
        }
    }
}
