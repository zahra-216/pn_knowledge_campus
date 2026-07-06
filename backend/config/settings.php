<?php

/**
 * The known-key registry for the Settings module (SRS FR-28, Section 7.4;
 * Database Design Section 4.2). The `settings` table is a heterogeneous
 * key/value store precisely so new settings never require a migration —
 * this file is where a new key is declared instead. It drives three
 * things: SettingSeeder's initial rows, SettingController's validation of
 * which keys may be written, and the value-casting used when returning
 * settings to the client.
 *
 * Every key not listed here is rejected by UpdateSettingsRequest — the
 * bulk-update endpoint is intentionally not "write any key you like".
 *
 * `is_public` decides whether a key is ever returned from the public,
 * unauthenticated GET /api/v1/settings/public endpoint. The entire smtp
 * group is private — the public site has no legitimate use for outgoing
 * mail configuration, not just the password field the Database Design
 * document calls out as its one example.
 */
return [
    'campus' => [
        'campus_name' => ['type' => 'string', 'is_public' => true],
        'campus_short_name' => ['type' => 'string', 'is_public' => true],
        'campus_tagline' => ['type' => 'string', 'is_public' => true],
        'registration_number' => ['type' => 'string', 'is_public' => true],
        'accreditation_number' => ['type' => 'string', 'is_public' => true],
    ],

    'contact' => [
        'contact_email' => ['type' => 'string', 'is_public' => true],
        'contact_phone' => ['type' => 'string', 'is_public' => true],
        'contact_address' => ['type' => 'string', 'is_public' => true],
        'admissions_email' => ['type' => 'string', 'is_public' => true],
        'admissions_phone' => ['type' => 'string', 'is_public' => true],
    ],

    // Google Maps embed for the public Contact page. Both keys are
    // deliberately public — a Maps embed URL/JS API key is meant to be
    // read by the browser; the actual access control for a Google Maps
    // API key is the HTTP-referrer restriction configured in Google
    // Cloud Console, not secrecy of the key string itself.
    'maps' => [
        'google_maps_embed_url' => ['type' => 'string', 'is_public' => true],
        'google_maps_api_key' => ['type' => 'string', 'is_public' => true],
    ],

    'smtp' => [
        'smtp_host' => ['type' => 'string', 'is_public' => false],
        'smtp_port' => ['type' => 'string', 'is_public' => false],
        'smtp_username' => ['type' => 'string', 'is_public' => false],
        'smtp_password' => ['type' => 'string', 'is_public' => false],
        'smtp_encryption' => ['type' => 'string', 'is_public' => false],
        'mail_from_address' => ['type' => 'string', 'is_public' => false],
        'mail_from_name' => ['type' => 'string', 'is_public' => false],
    ],

    'branding' => [
        'logo_media_id' => ['type' => 'int', 'is_public' => true],
        'favicon_media_id' => ['type' => 'int', 'is_public' => true],
    ],

    'footer' => [
        'footer_text' => ['type' => 'string', 'is_public' => true],
        'footer_copyright' => ['type' => 'string', 'is_public' => true],
    ],

    'analytics' => [
        'ga_tracking_id' => ['type' => 'string', 'is_public' => true],
        'gtm_container_id' => ['type' => 'string', 'is_public' => true],
    ],

    // Global fallback meta tags (SRS Section 7.4) — used when a page/
    // entity has no explicit SEO data of its own once M7's SEO Manager
    // and the fallback-resolution logic ship. `site_url` (Milestone 22)
    // is the public website's own canonical origin — distinct from
    // config('app.url'), which is this backend API's origin in the
    // decoupled frontend/backend deploy model this project uses.
    // GenerateSitemap reads it to build absolute URLs.
    'seo_defaults' => [
        'site_url' => ['type' => 'string', 'is_public' => true],
        'default_meta_title' => ['type' => 'string', 'is_public' => true],
        'default_meta_description' => ['type' => 'string', 'is_public' => true],
        'default_keywords' => ['type' => 'string', 'is_public' => true],
        'default_og_image_media_id' => ['type' => 'int', 'is_public' => true],
    ],

    // Homepage Builder — flat marketing copy for the sections that have
    // no dedicated content table in the Database Design (Welcome, Why
    // Choose Us, Statistics, CTA, Footer Widgets), unlike Hero Slider/
    // Testimonials/Partners which are real repeatable records with their
    // own tables. 'why_choose_us_items'/'statistics_items'/
    // 'footer_widgets' use the 'json' type — still one text column, no
    // migration, per this table's own reason for existing.
    'homepage' => [
        'welcome_heading' => ['type' => 'string', 'is_public' => true],
        'welcome_body' => ['type' => 'string', 'is_public' => true],
        'welcome_media_id' => ['type' => 'int', 'is_public' => true],
        'why_choose_us_items' => ['type' => 'json', 'is_public' => true],
        'statistics_items' => ['type' => 'json', 'is_public' => true],
        'cta_heading' => ['type' => 'string', 'is_public' => true],
        'cta_body' => ['type' => 'string', 'is_public' => true],
        'cta_button_label' => ['type' => 'string', 'is_public' => true],
        'cta_button_url' => ['type' => 'string', 'is_public' => true],
        'footer_widgets' => ['type' => 'json', 'is_public' => true],
    ],
];
