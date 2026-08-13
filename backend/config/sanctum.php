<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;

return [
    /*
    | Only relevant if the SPA ever authenticates via cookies instead of
    | bearer tokens. This API uses bearer tokens exclusively (API Design,
    | Section 2), so this list matters only for CSRF cookie issuance if
    | that mode is ever turned on — left populated from env for that
    | future flexibility, not currently required by any route.
    */
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,localhost:5173,127.0.0.1,127.0.0.1:8000,::1')),

    'guard' => ['web'],

    // Audit fix (Medium remediation) — a leaked/stolen bearer token
    // previously stayed valid forever; the only revocation path was the
    // user's own device-scoped logout or an admin deleting the account
    // outright. Minutes, per Sanctum's own config — 43200 = 30 days.
    // See UserController::revokeSessions() for the immediate,
    // incident-response counterpart to this ceiling.
    'expiration' => env('SANCTUM_TOKEN_EXPIRATION_MINUTES', 43200),

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],
];
