<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiPushNotification;
use Illuminate\Database\Seeder;

class PushNotificationSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Notifications')->update(['route_name' => 'admin.notifications.index']);

        LeiPushNotification::query()->delete();
        $img = 'https://images.unsplash.com/photo-1611162616475-46b635cb6868?w=80&h=80&fit=crop';
        $rows = [
            ['Summer Sale Alert', 'werefwe', 'KYC_JohnDoe_Passport.pdf', 'pdf', $img, 'user', 1, true, 1],
            ['New LEI Policy', 'Important registry update for all entities.', 'Corp_Articles_Rev3.docx', 'docx', $img, 'vendor', 3, true, 2],
            ['Maintenance Window', 'Scheduled downtime this weekend.', 'Proof_of_Address_Utility.jpg', 'jpg', $img, 'all', 2, true, 3],
            ['Verification Reminder', 'Complete your KYC before expiry.', 'Document_4.pdf', 'pdf', $img, 'user', 1, false, 4],
            ['Payment Received', 'Your renewal payment was confirmed.', 'Document_5.pdf', 'pdf', $img, 'user', 5, true, 5],
            ['Welcome Message', 'Welcome to LEI Registry Services.', 'Document_6.pdf', 'pdf', $img, 'all', 12, true, 6],
            ['Security Alert', 'New login detected on your account.', 'Document_7.pdf', 'pdf', $img, 'user', 1, true, 7],
            ['Vendor Onboarding', 'Complete your vendor profile setup.', 'Document_8.docx', 'docx', $img, 'vendor', 2, true, 8],
        ];
        foreach ($rows as $r) {
            LeiPushNotification::create([
                'title' => $r[0],
                'description' => $r[1],
                'file_name' => $r[2],
                'file_type' => $r[3],
                'image_url' => $r[4],
                'user_type' => $r[5],
                'notification_count' => $r[6],
                'is_active' => $r[7],
                'sort_order' => $r[8],
            ]);
        }
    }
}
