<?php

namespace Database\Seeders;

use App\Models\LeiBusinessSetting;
use App\Models\LeiFaq;
use App\Models\LeiFaqCategory;
use App\Models\LeiPricingMatrixRow;
use App\Models\LeiPricingPlan;
use App\Models\LeiSiteSection;
use Illuminate\Database\Seeder;

class WebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBusinessSettings();
        $this->seedSections();
        $this->seedFaq();
        $this->seedPricing();
    }

    protected function seedBusinessSettings(): void
    {
        $settings = LeiBusinessSetting::current();
        $settings->fill([
            'company_name' => 'LEI',
            'legal_name' => 'LEI System & Registry Services Private Limited',
            'tagline' => 'System & Registry Services',
            'support_email' => 'registry@leisystem.com',
            'support_phone' => '+44 (0) 20 7946 0888',
            'address_line' => '15 Financial Square, Level 42',
            'city' => 'London',
            'state' => '',
            'country' => 'United Kingdom',
            'postal_code' => 'EC2V 6EB',
            'website_url' => 'https://lei.developmentalphawizz.com',
            'currency_code' => 'INR',
            'currency_symbol' => '₹',
            'meta_description' => 'Secure your Legal Entity Identifier with fixed-fee administrative services and high-trust global registry oversight.',
            'copyright_text' => '© ' . date('Y') . ' LEI System & Registry Services Private Limited. All rights reserved.',
        ]);
        $settings->save();
    }

    protected function seedSections(): void
    {
        $sections = [
            [
                'page' => 'home',
                'section_key' => 'hero',
                'title' => 'What is an LEI?',
                'subtitle' => 'THE GLOBAL STANDARD',
                'content' => [
                    'description' => 'A Legal Entity Identifier (LEI) is a 20-character, alpha-numeric code based on the ISO 17442 standard. It connects to key reference information that enables clear and unique identification of legal entities participating in financial transactions.',
                    'cards' => [
                        ['icon' => 'globe', 'title' => 'Universal Identity', 'description' => 'Recognized across global financial markets and regulatory frameworks.'],
                        ['icon' => 'shield', 'title' => 'Verified Data', 'description' => 'Cross-verified against primary source registries for accuracy.'],
                    ],
                    'quote_card' => [
                        'title' => 'Financial Transparency',
                        'quote' => 'The implementation of the LEI is a cornerstone of the global effort to enhance transparency in financial markets.',
                        'attribution' => 'Regulatory Council',
                        'role' => 'Executive Board Member',
                    ],
                ],
            ],
            [
                'page' => 'home',
                'section_key' => 'services',
                'title' => 'Precision Registry Services',
                'subtitle' => 'Comprehensive management of the LEI lifecycle from initial registration to ongoing maintenance.',
                'content' => [
                    'items' => [
                        ['icon' => 'register', 'title' => 'LEI Registration', 'description' => 'Secure issuance for new legal entities with priority GLEIF integration.'],
                        ['icon' => 'renew', 'title' => 'Renewal & Maintenance', 'description' => 'Automated renewal cycles and annual data validation services.'],
                        ['icon' => 'transfer', 'title' => 'Transfer Services', 'description' => 'Seamlessly migrate existing LEIs to our accredited registry.'],
                    ],
                ],
            ],
            [
                'page' => 'home',
                'section_key' => 'workflow',
                'title' => 'Process Workflow',
                'content' => [
                    'steps' => [
                        ['num' => 1, 'title' => 'Application', 'description' => 'Submit entity details through our secure digital portal in less than 5 minutes.'],
                        ['num' => 2, 'title' => 'Verification', 'description' => 'Our agents cross-verify data against primary source registries for 100% accuracy.'],
                        ['num' => 3, 'title' => 'Issuance', 'description' => 'The LEI is issued and instantly published to the Global LEI Index (GLEIS).'],
                    ],
                ],
            ],
            [
                'page' => 'home',
                'section_key' => 'features',
                'title' => 'Engineered for Operational Excellence',
                'subtitle' => 'Our platform is built for professional administrators managing thousands of identifiers from a single dashboard.',
                'content' => [
                    'checklist' => [
                        'API Integration for large-scale data synchronization',
                        'Automatic expiration forecasting and alerting',
                        'Custom reporting for internal audit teams',
                    ],
                    'link_text' => 'Explore Enterprise Features',
                    'link_url' => '/pricing',
                    'items' => [
                        ['icon' => 'compliance', 'title' => 'Compliance', 'description' => 'Aligned with ESMA, MiFID II, and global regulatory standards.'],
                        ['icon' => 'security', 'title' => 'Security', 'description' => 'Military-grade encryption and strict access controls.'],
                        ['icon' => 'support', 'title' => '24/7 Support', 'description' => 'Dedicated account managers for institutional clients.'],
                        ['icon' => 'scale', 'title' => 'Global Scale', 'description' => 'Management across 180+ jurisdictions worldwide.'],
                    ],
                ],
            ],
            [
                'page' => 'about',
                'section_key' => 'hero',
                'title' => 'Commitment to Global Financial Transparency',
                'subtitle' => 'ESTABLISHED AUTHORITY',
                'content' => [
                    'description' => 'LEI System & Registry Services Private Limited serves as a cornerstone of the global identity ecosystem, facilitating secure, verified, and standardized identification for legal entities worldwide.',
                ],
            ],
            [
                'page' => 'about',
                'section_key' => 'integrity',
                'title' => 'Pioneering Financial Integrity',
                'content' => [
                    'paragraphs' => [
                        'As a trusted Local Operating Unit (LOU) partner, we provide the infrastructure that powers Legal Entity Identifiers across international markets.',
                        'Our commitment to data accuracy, security, and regulatory compliance ensures that every identifier issued through our platform meets the highest global standards.',
                    ],
                    'stats' => [
                        ['value' => '180+', 'label' => 'JURISDICTIONS', 'style' => 'light'],
                        ['value' => 'ISO 17442', 'label' => 'COMPLIANT', 'style' => 'dark'],
                        ['value' => '99.9%', 'label' => 'DATA VERIFICATION ACCURACY', 'style' => 'gold'],
                    ],
                ],
            ],
            [
                'page' => 'about',
                'section_key' => 'mission',
                'title' => 'Our Mission',
                'content' => [
                    'description' => 'To provide seamless, secure, and accessible LEI registration services that empower organizations to participate confidently in global financial markets.',
                ],
            ],
            [
                'page' => 'about',
                'section_key' => 'vision',
                'title' => 'Our Vision',
                'content' => [
                    'description' => 'To be the most trusted and authoritative node in the global identity verification network, setting the standard for registry excellence.',
                ],
            ],
            [
                'page' => 'about',
                'section_key' => 'governance',
                'title' => 'Governance Structure',
                'content' => [
                    'items' => [
                        ['title' => 'Regulatory Council', 'description' => 'Independent oversight ensuring ethical standards and regulatory alignment.'],
                        ['title' => 'Governance Board', 'description' => 'Strategic leadership team guiding registry operations and policy.'],
                        ['title' => 'Technical Oversight', 'description' => 'Expert committee managing the data pipeline and system integrity.'],
                    ],
                ],
            ],
            [
                'page' => 'about',
                'section_key' => 'cta',
                'title' => 'Ready to Secure Your Global Identity?',
                'subtitle' => 'Join thousands of legal entities already leveraging our platform for high-trust financial operations.',
                'content' => [
                    'primary_text' => 'Register Now',
                    'primary_url' => '/register',
                    'secondary_text' => 'Contact Specialist',
                    'secondary_url' => '/contact',
                ],
            ],
            [
                'page' => 'faq',
                'section_key' => 'hero',
                'title' => 'Knowledge Center',
                'subtitle' => 'Find comprehensive answers for Legal Entity Identifier management and global regulatory compliance.',
            ],
            [
                'page' => 'faq',
                'section_key' => 'sidebar',
                'title' => 'Regulatory Guidelines',
                'content' => [
                    'badge' => 'ISO 17442 STANDARD',
                    'description' => 'The global standard for LEI data ensures that the "Who is who" and "Who owns whom" of financial transactions is transparent and accessible to regulators worldwide.',
                ],
            ],
            [
                'page' => 'faq',
                'section_key' => 'support',
                'title' => 'Still need help?',
                'subtitle' => 'Our specialists are available to assist with complex registration or compliance queries.',
                'content' => [
                    'items' => [
                        ['icon' => 'headset', 'title' => 'Speak with a Specialist', 'description' => 'Direct consultation for institutional and bulk clients.', 'button' => 'Schedule Call', 'url' => '/contact'],
                        ['icon' => 'chat', 'title' => 'Live Registry Support', 'description' => 'Real-time status updates and submission assistance.', 'button' => 'Start Chat', 'url' => '/contact', 'primary' => true],
                        ['icon' => 'book', 'title' => 'Regulatory Archive', 'description' => 'Access full GLEIF documentation and ISO standards.', 'button' => 'Visit Library', 'url' => '/faq'],
                    ],
                ],
            ],
            [
                'page' => 'pricing',
                'section_key' => 'hero',
                'title' => 'Transparent Pricing for Global Compliance',
                'subtitle' => 'GLOBAL COMPLIANCE STANDARDS',
                'content' => [
                    'description' => 'Secure your Legal Entity Identifier with fixed-fee administrative services. We provide high-trust oversight and streamlined registration for systemic global stability.',
                ],
            ],
            [
                'page' => 'contact',
                'section_key' => 'info',
                'title' => 'Get in Touch',
                'content' => [
                    'headquarters_label' => 'Global Headquarters',
                    'email_label' => 'General Inquiries',
                    'phone_label' => '24/7 Hotline',
                ],
            ],
        ];

        foreach ($sections as $i => $row) {
            LeiSiteSection::updateOrCreate(
                ['page' => $row['page'], 'section_key' => $row['section_key']],
                array_merge($row, ['sort_order' => $i + 1, 'is_active' => true])
            );
        }
    }

    protected function seedFaq(): void
    {
        $categories = [
            ['title' => 'Registration Basics', 'slug' => 'registration-basics', 'icon' => 'grid', 'description' => 'Essential information about obtaining your first LEI.', 'sort_order' => 1],
            ['title' => 'Renewal Process', 'slug' => 'renewal-process', 'icon' => 'renew', 'description' => 'Keeping your identifier active and compliant.', 'sort_order' => 2],
            ['title' => 'Transfer & Management', 'slug' => 'transfer-management', 'icon' => 'transfer', 'description' => 'Moving and managing existing LEI records.', 'sort_order' => 3],
            ['title' => 'Compliance & Regulation', 'slug' => 'compliance-regulation', 'icon' => 'compliance', 'description' => 'Regulatory requirements and ISO standards.', 'sort_order' => 4],
        ];

        foreach ($categories as $cat) {
            $category = LeiFaqCategory::updateOrCreate(['slug' => $cat['slug']], array_merge($cat, ['is_active' => true]));

            if ($cat['slug'] === 'registration-basics') {
                LeiFaq::updateOrCreate(
                    ['question' => 'What is an LEI?'],
                    [
                        'category_id' => $category->id,
                        'answer' => 'A Legal Entity Identifier is a 20-character code that uniquely identifies legal entities participating in financial transactions worldwide.',
                        'sort_order' => 1,
                        'is_published' => true,
                    ]
                );
            }
        }

        $pricingFaqs = [
            ['Are there any hidden fees?', 'No. All fees are transparent and published on our pricing page. There are no hidden charges beyond the stated plan price.'],
            ['What payment methods are accepted?', 'We accept major credit cards, bank transfers, and enterprise invoicing for bulk registrations.'],
            ['Can I upgrade to a multi-year plan later?', 'Yes. You can upgrade your plan at any time. Remaining value from your current plan will be credited toward the upgrade.'],
        ];

        foreach ($pricingFaqs as $i => [$question, $answer]) {
            LeiFaq::updateOrCreate(
                ['question' => $question],
                [
                    'category_id' => null,
                    'answer' => $answer,
                    'sort_order' => $i + 1,
                    'is_published' => true,
                    'show_on_pricing' => true,
                ]
            );
        }
    }

    protected function seedPricing(): void
    {
        $registration = [
            ['label' => null, 'name' => '1 Year Plan', 'price' => 4350, 'duration_years' => 1, 'price_suffix' => '/ year', 'features' => [
                ['text' => 'Free LEI certificate', 'included' => true],
                ['text' => '24-hour priority processing', 'included' => true],
                ['text' => 'GLEIF system integration', 'included' => true],
            ], 'is_featured' => false, 'sort_order' => 1],
            ['label' => 'Most popular', 'name' => '3 Year Plan', 'price' => 11970, 'duration_years' => 3, 'price_suffix' => '/ year', 'savings_label' => 'SAVE ₹1080 vs 1-year plan', 'features' => [
                ['text' => 'Free LEI certificate', 'included' => true],
                ['text' => 'Everything in 1 Year', 'included' => true],
                ['text' => 'Dedicated account support', 'included' => true],
            ], 'is_featured' => true, 'button_style' => 'solid', 'sort_order' => 2],
            ['label' => null, 'name' => '5 Year Plan', 'price' => 16900, 'duration_years' => 5, 'price_suffix' => '/ year', 'savings_label' => 'SAVE ₹4850 vs 1-year plan', 'features' => [
                ['text' => 'Free LEI certificate', 'included' => true],
                ['text' => 'Max multi-year savings', 'included' => true],
                ['text' => 'API access for monitoring', 'included' => true],
            ], 'is_featured' => false, 'sort_order' => 3],
        ];

        foreach ($registration as $plan) {
            LeiPricingPlan::updateOrCreate(
                ['section' => 'registration', 'name' => $plan['name']],
                array_merge($plan, ['section' => 'registration', 'is_active' => true, 'button_text' => 'Select Plan'])
            );
        }

        $renewal = [
            ['name' => 'Annual Renewal', 'price' => 79, 'price_suffix' => '/ year', 'features' => [
                ['text' => 'Standard maintenance of existing LEI records with GLEIF validation.', 'included' => true],
            ], 'button_text' => 'Renew Now', 'sort_order' => 1],
            ['name' => 'Bulk Portfolio', 'price' => 0, 'price_suffix' => '', 'features' => [
                ['text' => 'Centralized management for portfolios with 10+ entities.', 'included' => true],
            ], 'button_text' => 'Request Quote', 'sort_order' => 2],
            ['name' => 'Transfer & Renew', 'price' => 0, 'price_suffix' => '', 'features' => [
                ['text' => 'Seamlessly migrate your LEI to our registry and extend validity.', 'included' => true],
            ], 'button_text' => 'Start Transfer', 'sort_order' => 3],
        ];

        foreach ($renewal as $plan) {
            LeiPricingPlan::updateOrCreate(
                ['section' => 'renewal', 'name' => $plan['name']],
                array_merge($plan, ['section' => 'renewal', 'is_active' => true, 'label' => null])
            );
        }

        $matrix = [
            ['Initial Issuance', '✓', '✓'],
            ['Annual Renewal', 'Per Occurrence', 'Automated Inclusion'],
            ['Data Challenges', 'Standard Support', 'Priority Handling'],
        ];

        foreach ($matrix as $i => [$component, $standard, $bundle]) {
            LeiPricingMatrixRow::updateOrCreate(
                ['component' => $component],
                ['standard_value' => $standard, 'bundle_value' => $bundle, 'sort_order' => $i + 1, 'is_active' => true]
            );
        }
    }
}
