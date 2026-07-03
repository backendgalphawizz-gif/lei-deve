<?php

namespace App\Mail;

use App\Models\LeiApplication;
use App\Models\LeiBusinessSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly LeiApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Application Received — ' . $this->application->application_code);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-submitted',
            with: [
                'businessName'    => LeiBusinessSetting::current()->company_name ?? config('app.name'),
                'userName'        => $this->application->user?->name ?? 'Applicant',
                'applicationCode' => $this->application->application_code,
                'entityName'      => $this->application->entity_name,
                'leiNumber'       => $this->application->lei_number ?? '—',
                'submittedOn'     => now()->format('M j, Y'),
                'trackUrl'        => route('applicant.applications.show', $this->application),
            ],
        );
    }
}
