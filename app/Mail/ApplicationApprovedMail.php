<?php

namespace App\Mail;

use App\Models\LeiApplication;
use App\Models\LeiBusinessSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly LeiApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Application Approved — LEI '.$this->application->lei_number);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-approved',
            with: [
                'businessName'   => LeiBusinessSetting::current()->company_name ?? config('app.name'),
                'userName'       => $this->application->user?->name ?? 'Applicant',
                'entityName'     => $this->application->entity_name,
                'leiNumber'      => $this->application->lei_number,
                'leiOid'         => \App\Models\LeiCertificate::OID_LEI,
                'country'        => $this->application->country,
                'approvedOn'     => now()->format('M j, Y'),
                'expiryDate'     => $this->application->expiry_date?->format('M j, Y') ?? '—',
                'trackUrl'       => route('applicant.applications.show', $this->application),
            ],
        );
    }
}
