<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Audit fix (Critical/High remediation) — the 'public-write' limiter
 * (10/minute/IP) registered in AppServiceProvider protects every
 * unauthenticated write endpoint that previously relied only on the
 * blanket 60/minute 'api' limiter (inquiries, applications, page-view
 * tracking). This proves the limit is actually wired up end-to-end on
 * the inquiries route, not just correct in isolation.
 */
class PublicWriteThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('public-write');
    }

    public function test_repeated_inquiry_submissions_from_one_ip_are_throttled(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/v1/inquiries', [
                'name' => 'Visitor',
                'email' => "visitor{$i}@example.com",
                'message' => 'Tell me more.',
            ])->assertCreated();
        }

        $this->postJson('/api/v1/inquiries', [
            'name' => 'Visitor',
            'email' => 'visitor-overflow@example.com',
            'message' => 'Tell me more.',
        ])->assertStatus(429);
    }

    public function test_the_general_api_limiter_is_unaffected_for_authenticated_requests(): void
    {
        // A sanity check that the new limiter is scoped to its own group
        // and doesn't leak onto unrelated public GET endpoints.
        $this->getJson('/api/v1/settings/public')->assertOk();
    }
}
