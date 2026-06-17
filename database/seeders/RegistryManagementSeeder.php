<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiRegistryTemplate;
use Illuminate\Database\Seeder;

class RegistryManagementSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Registry Services')->update([
            'route_name' => 'admin.registry.index',
        ]);

        LeiRegistryTemplate::query()->update(['is_active' => false]);

        LeiRegistryTemplate::create([
            'document_name' => 'Certificate of Incorporation',
            'primary_category' => 'legal_entity_proof',
            'sub_category' => 'general_corporate',
            'mandatory_flag' => true,
            'ocr_verification' => false,
            'file_formats' => ['pdf', 'docx'],
            'max_file_size_mb' => 25,
            'versioning_mode' => 'audit_trail',
            'approval_flow' => 'standard_review_2',
            'security_tier' => 'standard',
            'last_modified_by' => 'Super_Admin_01',
            'last_modified_at' => now()->setTime(9, 42),
            'is_published' => false,
            'is_active' => true,
        ]);
    }
}
