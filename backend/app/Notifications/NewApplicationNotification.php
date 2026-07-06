<?php

namespace App\Notifications;

use App\Models\Application;
use App\Support\MailConfigurator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Milestone 23 — replaces Milestone 20's ad-hoc
 * `Mail::to($staffEmail)->send(new ApplicationSubmittedStaffMail(...))`.
 * Unlike NewInquiryNotification, this IS sent to real `User` models
 * (every user holding `applications.view` — see
 * ApplicationController::sendSubmissionNotifications()), not a single
 * configured mailbox address: Applications has a real admin review
 * screen (/admin/applications/{id}) for a 'database' in-app entry to
 * link to, and "who should see a new-application alert in their
 * notification bell" is naturally "whoever can review applications",
 * not a single shared inbox.
 */
class NewApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application) {}

    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        MailConfigurator::apply();

        return (new MailMessage)
            ->subject('New Application Submitted: '.$this->application->application_number)
            ->view('emails.application-submitted-staff', ['application' => $this->application]);
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'title' => 'New Application Submitted',
            'message' => "{$this->application->first_name} {$this->application->last_name} submitted an application ({$this->application->application_number}).",
            'url' => "/admin/applications/{$this->application->id}",
        ];
    }
}
