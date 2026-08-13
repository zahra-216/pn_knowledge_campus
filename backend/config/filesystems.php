<?php

return [
    'default' => env('FILESYSTEM_DISK', 'public'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
        ],
        // Media Library (Database Design, Section 4.3) stores uploaded
        // course/page/news assets here in Phase 1 (shared hosting at
        // launch, per the SRS). Switching to an s3 disk later is a config
        // change only — no application code references the disk driver.
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        // Media Library hardening — "Storage abstraction (local now, S3
        // compatible later)". Inert until MEDIA_DISK=s3 is set and
        // `composer require league/flysystem-aws-s3-v3` is run (not
        // installed here — no S3 usage is requested yet, so this stub
        // is config-readiness only, not a live dependency). Nothing in
        // application code references a disk driver directly; switching
        // is exactly the SRS Portability requirement's "environment/
        // config changes only".
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'visibility' => 'public',
            'throw' => false,
        ],

        // Audit fix (Critical remediation) — spatie/laravel-backup's
        // destination disk (config/backup.php), kept separate from
        // 'local'/'public' so backup archives don't mix with the
        // application's own runtime files. Local-disk backups on
        // shared hosting are a starting point, not the whole story —
        // see DEPLOYMENT.md's "Backups & Restore" section for copying
        // this off-server, which an on-disk backup alone doesn't
        // protect against (a lost/corrupted server takes its own
        // backups with it).
        'backups' => [
            'driver' => 'local',
            'root' => storage_path('app/backups'),
            'throw' => false,
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
