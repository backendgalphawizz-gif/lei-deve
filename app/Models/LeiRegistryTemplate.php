<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiRegistryTemplate extends Model
{
    protected $fillable = [
        'document_name', 'primary_category', 'sub_category',
        'mandatory_flag', 'ocr_verification', 'file_formats',
        'max_file_size_mb', 'versioning_mode', 'approval_flow',
        'security_tier', 'last_modified_by', 'last_modified_at',
        'is_published', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'mandatory_flag' => 'boolean',
            'ocr_verification' => 'boolean',
            'file_formats' => 'array',
            'is_published' => 'boolean',
            'is_active' => 'boolean',
            'last_modified_at' => 'datetime',
        ];
    }

    /** @return array<string, string> */
    public static function primaryCategories(): array
    {
        return [
            'legal_entity_proof' => 'Legal Entity Proof',
            'identity_verification' => 'Identity Verification',
            'financial_statement' => 'Financial Statement',
        ];
    }

    /** @return array<string, string> */
    public static function subCategories(): array
    {
        return [
            'general_corporate' => 'General Corporate',
            'subsidiary_filing' => 'Subsidiary Filing',
            'regulatory_filing' => 'Regulatory Filing',
        ];
    }

    /** @return array<string, string> */
    public static function approvalFlows(): array
    {
        return [
            'standard_review_2' => 'Standard Review (2 Level)',
            'expedited_review' => 'Expedited Review (1 Level)',
            'executive_review' => 'Executive Review (3 Level)',
        ];
    }
}
