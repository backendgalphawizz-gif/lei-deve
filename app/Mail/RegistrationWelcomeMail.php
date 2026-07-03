<?php

namespace App\Mail;

use App\Models\LeiBusinessSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to ' . (LeiBusinessSetting::current()->company_name ?? config('app.name')) . ' — Your LEI is Ready');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-welcome',
            with: [
                'businessName'     => LeiBusinessSetting::current()->company_name ?? config('app.name'),
                'userName'         => $this->user->name,
                'userEmail'        => $this->user->email,
                'organizationName' => $this->user->organization_name ?: $this->user->name,
                'leiNumber'        => $this->user->lei_number ?? '—',
                'portalUrl'        => route('applicant.dashboard'),
            ],
        );
    }
}
