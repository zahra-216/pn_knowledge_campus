<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Milestone 25 (Security Review) — the 'login' rate limiter registered
 * in AppServiceProvider (5/minute per IP+email) is what actually
 * protects /auth/login from credential-guessing; this proves the limit
 * is wired up end-to-end (route → middleware → limiter), not just that
 * the limiter callback itself is correct in isolation.
 */
class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login');
    }

    public function test_repeated_failed_logins_for_the_same_email_are_throttled(): void
    {
        User::factory()->create(['email' => 'student@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'student@example.com',
                'password' => 'wrong-password',
                'device_name' => 'test-device',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'student@example.com',
            'password' => 'wrong-password',
            'device_name' => 'test-device',
        ])->assertStatus(429);
    }

    public function test_throttle_is_scoped_per_email_not_shared_across_all_login_attempts(): void
    {
        User::factory()->create(['email' => 'exhausted@example.com']);
        User::factory()->create(['email' => 'fresh@example.com', 'password' => bcrypt('correct-password')]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'exhausted@example.com',
                'password' => 'wrong-password',
                'device_name' => 'test-device',
            ]);
        }

        // A different email from the same IP is unaffected.
        $this->postJson('/api/v1/auth/login', [
            'email' => 'fresh@example.com',
            'password' => 'correct-password',
            'device_name' => 'test-device',
        ])->assertOk();
    }
}
