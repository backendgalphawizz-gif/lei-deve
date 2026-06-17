<?php

namespace Database\Seeders;

use App\Models\LeiStaticPage;
use Illuminate\Database\Seeder;

class StaticPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'page_type' => 'legal',
                'status' => 'published',
                'sort_order' => 1,
                'is_in_footer' => true,
                'meta_title' => 'Privacy Policy | LEI Registry',
                'content' => '<h2>Privacy Policy</h2><p>We collect and process LEI registration data in accordance with global regulatory standards. Personal identifiers are encrypted at rest and access is restricted to authorized registry operators.</p>',
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'page_type' => 'legal',
                'status' => 'published',
                'sort_order' => 2,
                'is_in_footer' => true,
                'meta_title' => 'Terms of Service | LEI Registry',
                'content' => '<h2>Terms of Service</h2><p>By using the LEI Super Admin portal you agree to comply with operational policies, audit requirements, and acceptable use guidelines defined by the registry authority.</p>',
            ],
            [
                'title' => 'FAQ — LEI Registration',
                'slug' => 'faq-lei-registration',
                'page_type' => 'help',
                'status' => 'published',
                'sort_order' => 3,
                'is_in_footer' => true,
                'meta_title' => 'FAQ | LEI Registration',
                'content' => '<h2>Frequently Asked Questions</h2><p><strong>What is an LEI?</strong> A Legal Entity Identifier is a 20-character code that uniquely identifies legal entities participating in financial transactions.</p>',
            ],
            [
                'title' => 'Maintenance Notice',
                'slug' => 'maintenance-notice',
                'page_type' => 'system',
                'status' => 'draft',
                'sort_order' => 4,
                'is_in_footer' => false,
                'meta_title' => null,
                'content' => '<h2>Scheduled Maintenance</h2><p>The registry portal may be unavailable during planned maintenance windows. Updates will be posted here before deployment.</p>',
            ],
            [
                'title' => 'Partner Program Overview',
                'slug' => 'partner-program',
                'page_type' => 'marketing',
                'status' => 'archived',
                'sort_order' => 5,
                'is_in_footer' => false,
                'meta_title' => 'Partner Program',
                'content' => '<h2>Partner Program</h2><p>Legacy marketing content for the partner API onboarding initiative. Archived for reference.</p>',
            ],
        ];

        foreach ($pages as $row) {
            $publishedAt = $row['status'] === 'published' ? now()->subDays(30) : null;

            LeiStaticPage::updateOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, [
                    'meta_description' => 'LEI Registry static content.',
                    'published_at' => $publishedAt,
                ])
            );
        }
    }
}
