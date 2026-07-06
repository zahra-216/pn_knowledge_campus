<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Milestone 19 (Contact Module) — makes the admin-editable SMTP settings
 * (Settings::registry()'s 'smtp' group, edited via the Settings screen's
 * SmtpTab.tsx) actually take effect, closing the gap config/mail.php's
 * own docblock flagged back in Milestone 1: "a runtime mailer config
 * override reads from there instead of purely from env."
 *
 * Deliberately NOT a service-provider boot()-time call — that would
 * query the settings table on every single request/boot regardless of
 * whether mail is ever sent. Instead, call apply() once, right before
 * actually sending a Mailable (see App\Mail\*), so the DB hit only
 * happens on the request that needs it.
 *
 * If `smtp_host` hasn't been configured by an admin yet, this is a
 * no-op — the app keeps using config/mail.php's own default (the 'log'
 * mailer), so email notifications still "work" (visible in
 * storage/logs/laravel.log) on a fresh install with no SMTP set up.
 */
class MailConfigurator
{
    public static function apply(): void
    {
        $settings = Setting::whereIn('key', [
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption',
            'mail_from_address', 'mail_from_name',
        ])->pluck('value', 'key');

        $host = $settings->get('smtp_host');

        if (! $host) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => (int) ($settings->get('smtp_port') ?: 587),
            'mail.mailers.smtp.username' => $settings->get('smtp_username'),
            'mail.mailers.smtp.password' => $settings->get('smtp_password'),
            'mail.mailers.smtp.encryption' => $settings->get('smtp_encryption') ?: null,
        ]);

        if ($settings->get('mail_from_address')) {
            config([
                'mail.from.address' => $settings->get('mail_from_address'),
                'mail.from.name' => $settings->get('mail_from_name') ?: config('mail.from.name'),
            ]);
        }
    }
}
