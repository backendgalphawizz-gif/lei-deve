<?php

namespace App\Mail;

use App\Models\LeiApplication;
use App\Models\LeiBusinessSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly LeiApplication $application,
        public readonly string $status,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->status) {
            'clarification' => 'Clarification Required — ' . $this->application->application_code,
            'rejected'      => 'Application Rejected — ' . $this->application->application_code,
            default         => 'Application Update — ' . $this->application->application_code,
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-status',
            with: [
                'businessName'    => LeiBusinessSetting::current()->company_name ?? config('app.name'),
                'userName'        => $this->application->user?->name ?? 'Applicant',
                'applicationCode' => $this->application->application_code,
                'entityName'      => $this->application->entity_name,
                'status'          => $this->status,
                'trackUrl'        => route('applicant.applications.show', $this->application),
            ],
        );
    }
}
