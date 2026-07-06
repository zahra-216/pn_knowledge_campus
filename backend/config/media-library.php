<?php

use App\Models\Media;

/**
 * Only the keys this project customizes are declared here — every other
 * spatie/laravel-medialibrary default is left untouched via the
 * package's own mergeConfigFrom() (Laravel fills in any key this array
 * doesn't define), matching the "modify only what's necessary" approach
 * already used for config/permission.php in Milestone 0.
 */
return [
    // Custom Media model (Database Design, Section 4.3 — folder_id,
    // alt_text, uploaded_by additions).
    'media_model' => Media::class,

    // Local/shared-hosting disk at launch (SRS Section 9, Portability) —
    // matches config/filesystems.php's default disk.
    'disk_name' => env('MEDIA_DISK', 'public'),

    // API Design, Section 5.2 — default max upload size (10MB) unless
    // overridden per environment.
    'max_file_size' => (int) env('MEDIA_MAX_UPLOAD_SIZE', 1024 * 1024 * 10),
];
