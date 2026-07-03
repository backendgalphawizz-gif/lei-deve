<?php

namespace App\Mail;

use App\Models\LeiApplication;
use App\Models\LeiBusinessSetting;
use App\Models\LeiCertificate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CertificateSignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly LeiApplication $application,
        public readonly LeiCertificate $certificate,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your LEI Certificate is Ready — '.$this->application->lei_number);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.certificate-signed',
            with: [
                'businessName' => LeiBusinessSetting::current()->company_name ?? config('app.name'),
                'userName' => $this->application->user?->name ?? 'Applicant',
                'entityName' => $this->application->entity_name,
                'leiNumber' => $this->application->lei_number,
                'serialNumber' => $this->certificate->serial_number,
                'signedAt' => $this->certificate->signed_at?->format('M j, Y H:i') ?? now()->format('M j, Y H:i'),
                'validUntil' => $this->certificate->valid_until?->format('M j, Y') ?? '—',
                'certificateUrl' => route('applicant.applications.certificate', $this->application),
            ],
        );
    }
}
