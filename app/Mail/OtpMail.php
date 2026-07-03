<?php

namespace App\Mail;

use App\Models\LeiBusinessSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $userName,
        public readonly string $otpCode,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Verification Code — ' . (LeiBusinessSetting::current()->company_name ?? config('app.name')));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'businessName' => LeiBusinessSetting::current()->company_name ?? config('app.name'),
                'userName'     => $this->userName,
                'otpCode'      => $this->otpCode,
            ],
        );
    }
}
