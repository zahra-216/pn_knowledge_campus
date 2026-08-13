<?php

use App\Models\Course;
use App\Models\Page;

/**
 * Audit fix (High remediation) — allow-list of valid `{attachableType}`
 * values for DownloadController's attach/detach endpoints, same
 * "short URL-safe key to model class" pattern as config/seo.php's
 * seoable_types and config/menus.php's linkable_types. Starts with the
 * two entity types the Database Design document's own example calls
 * out ("linked from multiple Course/Page sections") — extend this list
 * the same way those two configs grew, as more entities need it.
 */
return [
    'attachable_types' => [
        'course' => Course::class,
        'page' => Page::class,
    ],
];
