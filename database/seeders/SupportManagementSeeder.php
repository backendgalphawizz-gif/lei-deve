<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiSupportCategory;
use App\Models\LeiSupportMessage;
use App\Models\LeiSupportNote;
use App\Models\LeiSupportStatCard;
use App\Models\LeiSupportTicket;
use App\Services\SupportManagementService;
use Illuminate\Database\Seeder;
class SupportManagementSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Support')->update(['route_name' => 'admin.support.index']);

        $svc = app(SupportManagementService::class);

        LeiSupportStatCard::query()->delete();
        foreach ([
            ['total_active', '—', 'Total Active Tickets', 'blue', null, null, 1],
            ['sla_health', '—', 'SLA Health', 'gold', null, null, 2],
            ['avg_resolution', '—', 'Avg. Resolution Time', 'slate', null, null, 3],
            ['critical_escalations', '—', 'Critical Escalations', 'red', null, null, 4],
        ] as $r) {
            LeiSupportStatCard::create([
                'stat_key' => $r[0], 'value' => $r[1], 'label' => $r[2], 'icon_tone' => $r[3],
                'badge_text' => $r[4], 'badge_tone' => $r[5], 'sort_order' => $r[6],
            ]);
        }

        LeiSupportCategory::query()->delete();
        foreach (['Verification', 'Payment', 'Technical', 'API Access'] as $i => $name) {
            LeiSupportCategory::create([
                'name' => $name,
                'ticket_count_label' => '0 Tickets',
                'sort_order' => $i + 1,
            ]);
        }

        LeiSupportTicket::query()->delete();
        $rows = [
            ['#TK-88921', 'Global Trade Corp', 'Verification', 'high', 'escalated', "Verification failure for 'Global Trade Corp'", true, 1],
            ['#TK-88918', 'Acme Holdings Ltd', 'Payment', 'medium', 'progress', 'Payment gateway timeout on renewal', false, 2],
            ['#TK-88912', 'Nordic Registry AS', 'Technical', 'low', 'open', 'API rate limit clarification', false, 3],
            ['#TK-88905', 'Pacific LEI Group', 'Verification', 'high', 'progress', 'Document upload rejected', false, 4],
            ['#TK-88898', 'Euro Finance SA', 'Payment', 'medium', 'open', 'Invoice mismatch Q3', false, 5],
            ['#TK-88890', 'Atlas Corp GmbH', 'API Access', 'low', 'open', 'Account access reset request', false, 6],
            ['#TK-88882', 'Summit LEI LLC', 'Verification', 'medium', 'progress', 'Parent entity mismatch', false, 7],
            ['#TK-88875', 'Horizon Payments', 'Payment', 'high', 'escalated', 'Double charge on renewal', true, 8],
            ['#TK-88868', 'Blue Ocean AS', 'Technical', 'medium', 'open', 'Webhook delivery failures', false, 9],
            ['#TK-88860', 'Metro Registry Inc', 'Verification', 'low', 'open', 'LEI transfer status inquiry', false, 10],
        ];

        foreach ($rows as $r) {
            $this->createTicket($svc, $r);
        }

        $entities = ['Alpha LEI', 'Beta Registry', 'Gamma Corp', 'Delta Holdings', 'Echo Finance', 'Foxtrot AS', 'Gulf Trade', 'Helix Pay', 'Ion Tech', 'Jade LEI'];
        $cats = ['Verification', 'Payment', 'Technical', 'API Access'];
        $statuses = [['open', 'Open'], ['progress', 'In Progress'], ['escalated', 'Escalated']];
        for ($i = 11; $i <= 55; $i++) {
            $st = $statuses[array_rand($statuses)];
            $this->createTicket($svc, [
                '#TK-' . (88000 + $i),
                $entities[array_rand($entities)] . ' ' . $i,
                $cats[array_rand($cats)],
                ['high', 'medium', 'low'][array_rand(['high', 'medium', 'low'])],
                $st[0],
                'Support request #' . $i,
                $st[0] === 'escalated',
                $i,
            ]);
        }

        $ticket = LeiSupportTicket::where('ticket_code', '#TK-88921')->first();
        if ($ticket) {
            $m1 = LeiSupportMessage::create([
                'lei_support_ticket_id' => $ticket->id,
                'sender_initials' => 'GT', 'sender_name' => 'Global Trade', 'sender_tone' => 'client',
                'body' => 'Our LEI renewal keeps bouncing after we upload the parent registry extract.',
                'time_label' => '10:45 AM', 'is_outgoing' => false, 'sort_order' => 1,
            ]);
            $m1->update(['created_at' => now()->subMinutes(28)]);
            $m2 = LeiSupportMessage::create([
                'lei_support_ticket_id' => $ticket->id,
                'sender_initials' => 'SM', 'sender_name' => 'Sarah', 'sender_role' => 'Level 2 Support', 'sender_tone' => 'support',
                'body' => 'Checking your certificate chain and GLEIF parent linkage now.',
                'time_label' => '10:52 AM', 'is_outgoing' => true, 'sort_order' => 2,
            ]);
            $m2->update(['created_at' => now()->subMinutes(12)]);
            LeiSupportNote::create([
                'lei_support_ticket_id' => $ticket->id,
                'author_initials' => 'JR', 'author_name' => 'John', 'author_tone' => 'admin',
                'body' => 'Likely a database sync issue. Escalated to DevOps.',
                'time_label' => 'Today, 09:15', 'sort_order' => 1,
            ]);
        }

        $svc->syncCategoryCounts();
    }

    private function createTicket(SupportManagementService $svc, array $r): void
    {
        $closed = $r[4] === 'closed';
        LeiSupportTicket::create([
            'ticket_code' => $r[0],
            'user_entity' => $r[1],
            'contact_email' => $svc->emailFromEntity($r[1]),
            'category' => $r[2],
            'priority' => match ($r[3]) {
                'high' => 'High',
                'medium' => 'Med',
                default => 'Low',
            },
            'priority_tone' => $r[3],
            'status' => match ($r[4]) {
                'escalated' => 'Escalated',
                'progress' => 'In Progress',
                'closed' => 'Closed',
                default => 'Open',
            },
            'status_tone' => $r[4],
            'title' => $r[5],
            'is_urgent' => $r[6],
            'sort_order' => $r[7],
            'closed_at' => $closed ? now()->subHours(rand(1, 20)) : null,
            'assigned_at' => $r[4] === 'progress' ? now()->subHours(2) : null,
        ]);
    }
}
