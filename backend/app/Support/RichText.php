<?php

namespace App\Support;

use Mews\Purifier\Facades\Purifier;

/**
 * Audit fix (High remediation) — every editor-authored rich-text field
 * (BlogPost.body, News.body, PageBlock rich_text blocks) was saved as
 * raw HTML from the in-house RichTextEditor with no sanitization
 * anywhere, then rendered via dangerouslySetInnerHTML on public pages:
 * a Content Editor/Marketing account (broadly granted create/edit on
 * these) could inject a <script> that runs in every visitor's browser.
 * Sanitized once, here, on save — not on render — using the 'richtext'
 * HTMLPurifier profile (config/purifier.php), which only allows the
 * tags the editor actually produces and RICH_TEXT_CLASSNAME actually
 * styles.
 */
class RichText
{
    public static function sanitize(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        return Purifier::clean($html, 'richtext');
    }
}
