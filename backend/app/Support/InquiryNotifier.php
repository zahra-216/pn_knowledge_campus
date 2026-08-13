<?php

namespace App\Support;

use App\Models\Inquiry;
use App\Models\Setting;
use App\Notifications\InquiryConfirmationNotification;
use App\Notifications\NewInquiryNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Extracted from InquiryController (audit fix, High remediation) so the
 * gated-download capture flow (DownloadController::requestDownload())
 * can send the exact same staff/visitor notifications a Contact-form
 * inquiry gets, without duplicating the dispatch logic in two
 * controllers. Both notifications are queued (ShouldQueue) —
 * dispatching just inserts a `jobs` row, so a bad SMTP config can't
 * fail the calling request; any delivery failure surfaces later as a
 * `failed_jobs` row instead.
 */
class InquiryNotifier
{
    public static function send(Inquiry $inquiry): void
    {
        try {
            $staffEmail = Setting::where('key', 'admissions_email')->value('value')
                ?: Setting::where('key', 'contact_email')->value('value');

            if ($staffEmail) {
                Notification::route('mail', $staffEmail)->notify(new NewInquiryNotification($inquiry));
            }

            Notification::route('mail', $inquiry->email)->notify(new InquiryConfirmationNotification($inquiry));
        } catch (\Throwable $e) {
            Log::warning('Inquiry notification dispatch failed.', ['inquiry_id' => $inquiry->id, 'error' => $e->getMessage()]);
        }
    }
}
