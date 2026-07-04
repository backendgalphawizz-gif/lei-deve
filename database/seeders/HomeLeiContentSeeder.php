<?php

namespace Database\Seeders;

use App\Models\LeiHomeLeiBlock;
use Illuminate\Database\Seeder;

class HomeLeiContentSeeder extends Seeder
{
    public function run(): void
    {
        $blocks = [
            [
                'block_type' => 'intro',
                'sort_order' => 1,
                'title' => 'Who Needs an LEI?',
                'body' => 'A Legal Entity Identifier (LEI) is a globally recognized 20-character identification code for legal entities participating in financial transactions. It enhances transparency, simplifies entity identification, and supports regulatory compliance worldwide.',
                'subtitle' => 'The following organizations typically need an LEI:',
            ],
            [
                'block_type' => 'category',
                'sort_order' => 2,
                'category_number' => 1,
                'title' => 'Companies Participating in Financial Markets',
                'items' => [
                    'Public Limited Companies',
                    'Private Limited Companies',
                    'Limited Liability Partnerships (LLPs)',
                    'Partnerships',
                    'Corporations',
                    'Government-owned entities',
                ],
            ],
            [
                'block_type' => 'category',
                'sort_order' => 3,
                'category_number' => 2,
                'title' => 'Banks and Financial Institutions',
                'items' => [
                    'Commercial Banks',
                    'Cooperative Banks',
                    'Investment Banks',
                    'Non-Banking Financial Companies (NBFCs)',
                    'Payment Banks',
                    'Small Finance Banks',
                ],
            ],
            [
                'block_type' => 'category',
                'sort_order' => 4,
                'category_number' => 3,
                'title' => 'Investment Entities',
                'items' => [
                    'Mutual Funds',
                    'Alternative Investment Funds (AIFs)',
                    'Hedge Funds',
                    'Pension Funds',
                    'Private Equity Funds',
                    'Venture Capital Funds',
                    'Family Offices',
                ],
            ],
            [
                'block_type' => 'category',
                'sort_order' => 5,
                'category_number' => 4,
                'title' => 'Insurance Sector',
                'items' => [
                    'Insurance Companies',
                    'Reinsurance Companies',
                    'Insurance Brokers',
                    'Insurance Intermediaries',
                ],
            ],
            [
                'block_type' => 'category',
                'sort_order' => 6,
                'category_number' => 5,
                'title' => 'Capital Market Participants',
                'items' => [
                    'Stock Brokers',
                    'Depository Participants',
                    'Clearing Members',
                    'Portfolio Managers',
                    'Investment Advisers',
                    'Merchant Bankers',
                ],
            ],
            [
                'block_type' => 'category',
                'sort_order' => 7,
                'category_number' => 6,
                'title' => 'Corporate Borrowers',
                'subtitle' => 'Organizations that:',
                'items' => [
                    'Obtain loans from banks or financial institutions',
                    'Issue bonds or debt securities',
                    'Raise capital through financial markets',
                ],
            ],
            [
                'block_type' => 'category',
                'sort_order' => 8,
                'category_number' => 7,
                'title' => 'Entities Trading in Financial Instruments',
                'subtitle' => 'Organizations dealing in:',
                'items' => [
                    'Stocks and Shares',
                    'Bonds and Debentures',
                    'Derivatives',
                    'Foreign Exchange (Forex)',
                    'Commodities',
                    'Structured Financial Products',
                ],
            ],
            [
                'block_type' => 'category',
                'sort_order' => 9,
                'category_number' => 8,
                'title' => 'Trusts and Non-Profit Organizations',
                'items' => [
                    'Charitable Trusts',
                    'Public Trusts',
                    'Foundations',
                    'Societies',
                    'Associations (where required for financial transactions)',
                ],
            ],
            [
                'block_type' => 'category',
                'sort_order' => 10,
                'category_number' => 9,
                'title' => 'Government and Public Sector Organizations',
                'items' => [
                    'Government Departments',
                    'Public Sector Undertakings (PSUs)',
                    'Municipal Bodies',
                    'Statutory Authorities',
                ],
            ],
            [
                'block_type' => 'category',
                'sort_order' => 11,
                'category_number' => 10,
                'title' => 'International Business Entities',
                'subtitle' => 'Organizations involved in:',
                'items' => [
                    'Cross-border trade',
                    'International banking',
                    'Global supply chain finance',
                    'Overseas investments',
                    'International securities trading',
                ],
            ],
            [
                'block_type' => 'reasons',
                'sort_order' => 12,
                'title' => 'Common Reasons to Obtain an LEI',
                'subtitle' => 'An LEI may be required for:',
                'items' => [
                    'Opening institutional investment accounts',
                    'Trading securities and derivatives',
                    'Cross-border financial transactions',
                    'Regulatory reporting',
                    'Bank loan documentation (where applicable)',
                    'Corporate bond issuance',
                    'Foreign exchange transactions',
                    'Treasury operations',
                    'Financial risk management',
                    'KYC and AML compliance',
                ],
            ],
            [
                'block_type' => 'benefits',
                'sort_order' => 13,
                'title' => 'Benefits of Having an LEI',
                'items' => [
                    ['title' => 'Global Recognition', 'text' => 'A unique identifier accepted in over 200 jurisdictions.'],
                    ['title' => 'Regulatory Compliance', 'text' => 'Meets the identification requirements of many financial regulators.'],
                    ['title' => 'Faster KYC', 'text' => 'Simplifies customer due diligence and onboarding.'],
                    ['title' => 'Greater Transparency', 'text' => 'Clearly identifies the legal entity and its ownership structure (where applicable).'],
                    ['title' => 'Reduced Transaction Risk', 'text' => 'Helps minimize errors caused by entity misidentification.'],
                    ['title' => 'Improved Market Confidence', 'text' => 'Enhances trust among banks, investors, and business partners.'],
                    ['title' => 'Streamlined Cross-Border Transactions', 'text' => 'Facilitates international financial dealings.'],
                ],
            ],
            [
                'block_type' => 'mandatory',
                'sort_order' => 14,
                'title' => 'Is an LEI Mandatory?',
                'body' => 'LEI requirements vary by country, regulator, and transaction type. In many jurisdictions, an LEI is mandatory for certain regulated financial transactions, while in others it may be voluntary but strongly recommended. Organizations should confirm the applicable requirements with their financial institution or relevant regulator before undertaking regulated financial activities.',
            ],
        ];

        foreach ($blocks as $block) {
            $match = ['sort_order' => $block['sort_order']];

            if ($block['block_type'] === 'category' && ! empty($block['category_number'])) {
                $match = ['block_type' => 'category', 'category_number' => $block['category_number']];
            } elseif ($block['block_type'] !== 'category') {
                $match = ['block_type' => $block['block_type']];
            }

            LeiHomeLeiBlock::updateOrCreate(
                $match,
                array_merge($block, ['is_active' => true])
            );
        }
    }
}
