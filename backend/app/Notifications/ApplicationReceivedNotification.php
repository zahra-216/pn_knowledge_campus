<?php

namespace App\Notifications;

use App\Models\Application;
use App\Support\MailConfigurator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Milestone 23 — replaces Milestone 20's `ApplicationSubmittedMail`. On-demand mail to the applicant's own email (no visitor login — see Application's migration docblock). */
class ApplicationReceivedNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject('Application Received — '.$this->application->application_number)
            ->view('emails.application-submitted', ['application' => $this->application]);
    }
}
