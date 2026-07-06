<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Milestone 25 (Performance Optimization / Security Review) —
 * SecurityHeaders middleware is registered globally in bootstrap/app.php
 * via $middleware->append(), so any endpoint proves it's wired up
 * correctly; the public settings endpoint is used simply because it
 * needs no auth setup.
 */
class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_response_carries_the_baseline_security_headers(): void
    {
        $response = $this->getJson('/api/v1/settings/public');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }

    public function test_strict_transport_security_is_only_sent_over_https(): void
    {
        $plain = $this->getJson('/api/v1/settings/public');
        $plain->assertHeaderMissing('Strict-Transport-Security');

        // Symfony's Request::create() derives HTTPS from the request
        // URL's own scheme (and overwrites any server variable that
        // says otherwise), so the only reliable way to simulate an
        // HTTPS request in a feature test is to pass an already-absolute
        // https:// URL — Laravel's UrlGenerator::to() (used by every
        // ->getJson() call) returns an already-valid URL untouched
        // rather than rewriting it against config('app.url').
        $secure = $this->getJson(url()->to('/api/v1/settings/public', [], true));
        $secure->assertHeader('Strict-Transport-Security');
    }
}
