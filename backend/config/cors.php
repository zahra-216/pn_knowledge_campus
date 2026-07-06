<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'up'],

    'allowed_methods' => ['*'],

    // Populated from FRONTEND_URL rather than hardcoded, so staging/
    // production simply set a different .env value — no code change
    // needed per environment (ties to the API Design versioning /
    // deployment stability principles).
    'allowed_origins' => array_filter([env('FRONTEND_URL', 'http://localhost:5173')]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // bearer-token API, not cookie-based
];
