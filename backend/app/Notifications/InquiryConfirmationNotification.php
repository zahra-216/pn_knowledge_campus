<?php

namespace App\Notifications;

use App\Models\Inquiry;
use App\Support\MailConfigurator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Milestone 23 — the visitor-facing acknowledgement, sent on-demand to the submitter's own email (no User account exists for them). See NewInquiryNotification's docblock for the mail-only/on-demand pattern this mirrors. */
class InquiryConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Inquiry $inquiry) {}

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        MailConfigurator::apply();

        return (new MailMessage)
            ->subject("We've received your message")
            ->view('emails.inquiry-confirmation', ['inquiry' => $this->inquiry]);
    }
}
