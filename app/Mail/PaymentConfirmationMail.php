<?php

namespace App\Mail;

use App\Models\LeiBusinessSetting;
use App\Models\LeiSubscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly LeiSubscription $subscription,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Payment Confirmed — ' . $this->subscription->reference);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-confirmation',
            with: [
                'businessName' => LeiBusinessSetting::current()->company_name ?? config('app.name'),
                'userName'     => $this->user->name,
                'reference'    => $this->subscription->reference,
                'planName'     => $this->subscription->plan_name,
                'amount'       => $this->subscription->formattedAmount(),
                'paidOn'       => now()->format('M j, Y'),
                'validUntil'   => $this->subscription->valid_until?->format('M j, Y') ?? '—',
                'portalUrl'    => route('applicant.payments.index'),
            ],
        );
    }
}
