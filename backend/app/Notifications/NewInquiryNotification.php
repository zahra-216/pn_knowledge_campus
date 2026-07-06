<?php

namespace App\Notifications;

use App\Models\Inquiry;
use App\Support\MailConfigurator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Milestone 23 (Notification System) — replaces the ad-hoc
 * `Mail::to(...)->send(new InquiryReceivedMail(...))` call from
 * Milestone 19. Sent on-demand (`Notification::route('mail', $email)`)
 * to whichever address the "Contact Information" settings designate
 * (admissions_email/contact_email) — that's a configurable mailbox, not
 * necessarily any particular staff User account, so this deliberately
 * has no 'database' channel (there's no admin inbox screen for
 * Inquiries yet to link an in-app notification to — see
 * InquiryController's docblock on why that's still deferred). Compare
 * to NewApplicationNotification, which IS sent to real Users and does
 * get a 'database' entry, since Applications has a real admin screen.
 */
class NewInquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Inquiry $inquiry) {}

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        // Queued notifications run in their own process (possibly a
        // separate `queue:work` invocation) — the admin-editable SMTP
        // settings must be (re-)applied here, not just once wherever
        // the notification was dispatched from.
        MailConfigurator::apply();

        return (new MailMessage)
            ->subject('New Inquiry: '.$this->inquiry->name)
            ->view('emails.inquiry-received', ['inquiry' => $this->inquiry]);
    }
}
