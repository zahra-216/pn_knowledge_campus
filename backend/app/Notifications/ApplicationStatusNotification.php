<?php

namespace App\Notifications;

use App\Models\Application;
use App\Support\MailConfigurator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Milestone 23 — replaces Milestone 20's `ApplicationStatusMail`, sent when staff approve/reject (Application::$status is already updated by the time this is built). On-demand mail to the applicant's own email. */
class ApplicationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application) {}

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        MailConfigurator::apply();

        $label = $this->application->status === 'approved' ? 'Approved' : 'Update';

        return (new MailMessage)
            ->subject("Application {$label} — {$this->application->application_number}")
            ->view('emails.application-status', ['application' => $this->application]);
    }
}
