<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiNmConfig;
use App\Models\LeiNmDeliveryLog;
use App\Models\LeiNmPlaceholder;
use App\Models\LeiNmStatCard;
use App\Models\LeiNmTemplate;
use App\Models\LeiNmTrigger;
use Illuminate\Database\Seeder;

class NotificationManagementSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Notifications')->update(['route_name' => 'admin.notifications.index']);

        LeiNmStatCard::query()->delete();
        foreach ([
            ['emails_sent', '142,892', 'Total Emails Sent', 'blue', 1],
            ['sms_rate', '98.2%', 'SMS Delivery Rate', 'orange', 2],
            ['active_triggers', '24 Triggers', 'Active Triggers', 'purple', 3],
            ['system_alerts', '0 Active', 'System Alerts', 'red', 4],
        ] as $r) {
            LeiNmStatCard::create([
                'stat_key' => $r[0], 'value' => $r[1], 'label' => $r[2], 'icon_tone' => $r[3], 'sort_order' => $r[4],
            ]);
        }

        LeiNmConfig::query()->delete();
        LeiNmConfig::create([
            'broadcast_channel' => 'System-wide In-app',
            'broadcast_audience' => 'All Users',
            'broadcast_message' => '',
            'otp_length' => 6,
            'otp_expiry_min' => 5,
            'otp_retry_limit' => 3,
            'template_channel' => 'email',
        ]);

        LeiNmTemplate::query()->delete();
        foreach ([
            ['Welcome Email', 'Onboarding sequence for new entities', 'Onboarding', 'email', 'active', 'Oct 12, 2023', 1],
            ['LEI Expiry Reminder', 'Automated compliance notification', 'Compliance', 'email', 'active', 'Oct 08, 2023', 2],
            ['OTP Verification', 'High-security session authentication', 'Security', 'email', 'active', 'Sep 24, 2023', 3],
            ['SLA Breach Alert', 'Internal admin notification', 'Operational', 'email', 'draft', 'Oct 14, 2023', 4],
            ['Payment SMS', 'Transaction confirmation text', 'Payments', 'sms', 'active', 'Oct 10, 2023', 5],
        ] as $r) {
            LeiNmTemplate::create([
                'name' => $r[0], 'subtitle' => $r[1], 'category' => $r[2], 'channel' => $r[3],
                'status' => $r[4], 'last_updated_label' => $r[5], 'sort_order' => $r[6],
            ]);
        }

        LeiNmTrigger::query()->delete();
        foreach ([
            ['On Registration', true, 1],
            ['On SLA Breach', true, 2],
            ['Payment Success', false, 3],
        ] as $r) {
            LeiNmTrigger::create(['name' => $r[0], 'is_enabled' => $r[1], 'sort_order' => $r[2]]);
        }

        LeiNmPlaceholder::query()->delete();
        foreach (['user_name', 'lei_id', 'expiry_date', 'otp_code', 'last_login'] as $i => $key) {
            LeiNmPlaceholder::create(['placeholder_key' => $key, 'sort_order' => $i + 1]);
        }

        LeiNmDeliveryLog::query()->delete();
        foreach ([
            ['email', 'john.doe@globaltrade.com', 'Welcome Email', 'delivered', '2m ago', 1],
            ['sms', '+1 555-019-2834', 'OTP Code', 'pending', '4m ago', 2],
            ['email', 'admin@lei-finance.org', 'System Alert', 'failed', '12m ago', 3],
            ['email', 'support@nordic-registry.as', 'Template Update', 'delivered', '15m ago', 4],
        ] as $r) {
            LeiNmDeliveryLog::create([
                'delivery_type' => $r[0], 'recipient' => $r[1], 'template_label' => $r[2],
                'status' => $r[3], 'time_label' => $r[4], 'sort_order' => $r[5],
            ]);
        }
    }
}
