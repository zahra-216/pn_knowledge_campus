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

    'expiration' => null, // tokens do not expire by default (API Design, Section 2.4) — revoked explicitly instead

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],
];
