<?php

/**
 * Allow-list of valid `block_type` values for the Page Builder (Database
 * Design, Section 4.5). Each key is validated against here by
 * PageBlockRequest, which also owns the per-type `data` shape rules —
 * this file only decides which block types exist, not their fields.
 *
 * Adding an 11th block type later means: add a key here, add a
 * `dataRules()` case in PageBlockRequest, and add a form in the React
 * Page Builder. Nothing else in the pipeline needs to change.
 */
return [
    'types' => [
        'hero',
        'text',
        'rich_text',
        'image',
        'gallery',
        'video',
        'cta',
        'faq',
        'statistics',
        'testimonials',
        'partners',
    ],
];
