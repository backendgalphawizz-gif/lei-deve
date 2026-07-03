<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeiCertificate extends Model
{
    public const OID_LEI = '1.3.6.1.4.1.52266.1';

    public const OID_ROLE = '1.3.6.1.4.1.52266.2';

    protected $fillable = [
        'lei_application_id',
        'status',
        'serial_number',
        'signature_algorithm',
        'issuer_dn',
        'subject_dn',
        'lei_oid',
        'role_oid',
        'certificate_role',
        'valid_from',
        'valid_until',
        'unsigned_pdf_path',
        'signed_pdf_path',
        'x509_pem_path',
        'signature_hash',
        'signed_by',
        'signed_at',
        'ca_notes',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'signed_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(LeiApplication::class, 'lei_application_id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function isSigned(): bool
    {
        return $this->status === 'signed' && $this->signed_pdf_path;
    }

    public function isPendingCa(): bool
    {
        return in_array($this->status, ['unsigned', 'pending_ca'], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending_unsigned' => 'Pending Generation',
            'unsigned' => 'Unsigned — Awaiting CA',
            'pending_ca' => 'Awaiting CA Signature',
            'signed' => 'Digitally Signed',
            'rejected' => 'Rejected',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'signed' => 'green',
            'unsigned', 'pending_ca', 'pending_unsigned' => 'orange',
            'rejected' => 'red',
            default => 'gray',
        };
    }
}
