<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiDocument;
use App\Models\LeiDocumentAuditEvent;
use App\Models\LeiDocumentConfig;
use App\Models\LeiDocumentStatCard;
use Illuminate\Database\Seeder;

class DocumentManagementSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Documents')->update(['route_name' => 'admin.documents.index']);

        LeiDocumentStatCard::query()->delete();
        foreach ([
            ['pending_verification', '0', 'Pending Verification', 'blue', 1],
            ['malware_detected', '00', 'Malware Detected', 'red', 2],
            ['avg_sla', '4.2m', 'Avg. SLA Time', 'slate', 3],
            ['verified_today', '0', 'Verified Today', 'green', 4],
        ] as $r) {
            LeiDocumentStatCard::create([
                'stat_key' => $r[0], 'value' => $r[1], 'label' => $r[2], 'icon_tone' => $r[3], 'sort_order' => $r[4],
            ]);
        }

        LeiDocumentConfig::query()->delete();
        LeiDocumentConfig::create([
            'version_label' => 'v4.0.2',
            'ledger_node' => '12.0',
            'ledger_text' => 'Encrypted Node: 12.0. All actions are recorded in immutable ledger.',
        ]);

        LeiDocument::query()->delete();
        $preview = 'https://images.unsplash.com/photo-1586281380349-632531db7ed4?w=800&q=80';
        $rows = [
            ['#VX-7701', 'KYC_JohnDoe_Passport.pdf', 'pdf', 'Clean', 'clean', 'Pending', 'pending', null, 1],
            ['#VX-7702', 'Corp_Articles_Rev3.docx', 'docx', 'Scanning', 'scanning', 'Processing', 'processing', null, 2],
            ['#VX-7703', 'Proof_of_Address_Utility.jpg', 'jpg', 'Clean', 'clean', 'Active Review', 'review', $preview, 3],
        ];
        foreach ($rows as $r) {
            $doc = LeiDocument::create([
                'document_code' => $r[0],
                'file_name' => $r[1],
                'file_type' => $r[2],
                'security_label' => $r[3],
                'security_tone' => $r[4],
                'status' => $r[5],
                'status_tone' => $r[6],
                'preview_url' => $r[7],
                'sort_order' => $r[8],
            ]);

            if ($r[0] === '#VX-7703') {
                LeiDocumentAuditEvent::create([
                    'lei_document_id' => $doc->id,
                    'title' => 'Document Uploaded',
                    'description' => 'Initial portal submission via client gateway : 902.',
                    'event_label' => 'Oct 24, 2023 - 09:12 AM',
                    'indicator_tone' => 'yellow',
                    'sort_order' => 1,
                ]);
                LeiDocumentAuditEvent::create([
                    'lei_document_id' => $doc->id,
                    'title' => 'Malware Sweep Complete',
                    'description' => '0 threats found.',
                    'event_label' => 'Oct 24, 2023 - 09:14 AM',
                    'indicator_tone' => 'green',
                    'sort_order' => 2,
                ]);
                LeiDocumentAuditEvent::create([
                    'lei_document_id' => $doc->id,
                    'title' => 'Manual Review Initiated',
                    'description' => null,
                    'event_label' => 'In Progress...',
                    'indicator_tone' => 'grey',
                    'is_in_progress' => true,
                    'sort_order' => 3,
                ]);
            }
        }

        for ($i = 4; $i <= 25; $i++) {
            LeiDocument::create([
                'document_code' => '#VX-' . (7700 + $i),
                'file_name' => 'Document_' . $i . '.pdf',
                'file_type' => 'pdf',
                'security_label' => 'Clean',
                'security_tone' => 'clean',
                'status' => 'Pending',
                'status_tone' => 'pending',
                'sort_order' => $i,
            ]);
        }
    }
}
