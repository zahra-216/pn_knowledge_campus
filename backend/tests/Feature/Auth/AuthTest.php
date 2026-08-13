<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Audit fix (High remediation) — AuthController is the only authentication
 * surface in the app (every /admin/* route depends on it) and had no
 * test coverage at all. Covers API Design Section 2.2's five endpoints.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_log_in_with_correct_credentials(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com', 'password' => Hash::make('correct-password')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'staff@example.com',
            'password' => 'correct-password',
            'device_name' => 'test-suite',
        ]);

        $response->assertOk();
        $this->assertNotNull($response->json('data.token'));
        $this->assertSame($user->id, $response->json('data.user.id'));
    }

    public function test_login_fails_with_an_incorrect_password(): void
    {
        User::factory()->create(['email' => 'staff@example.com', 'password' => Hash::make('correct-password')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'staff@example.com',
            'password' => 'wrong-password',
            'device_name' => 'test-suite',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_for_an_email_that_does_not_exist(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'whatever',
            'device_name' => 'test-suite',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_a_deactivated_user_cannot_log_in(): void
    {
        User::factory()->create([
            'email' => 'staff@example.com',
            'password' => Hash::make('correct-password'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'staff@example.com',
            'password' => 'correct-password',
            'device_name' => 'test-suite',
        ]);

        $response->assertForbidden();
    }

    public function test_email_password_and_device_name_are_required(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password', 'device_name']);
    }

    public function test_login_is_rate_limited_per_ip_and_email(): void
    {
        User::factory()->create(['email' => 'staff@example.com', 'password' => Hash::make('correct-password')]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'staff@example.com',
                'password' => 'wrong-password',
                'device_name' => 'test-suite',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'staff@example.com',
            'password' => 'correct-password',
            'device_name' => 'test-suite',
        ])->assertStatus(429);
    }

    public function test_an_authenticated_user_can_fetch_their_own_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/auth/me');

        $response->assertOk();
        $this->assertSame($user->id, $response->json('data.id'));
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    /**
     * Asserts against the database, not a follow-up HTTP call with the
     * same (now-revoked) token — Sanctum's RequestGuard memoizes the
     * resolved user for the life of the test process, so a second
     * `getJson()` in the same test method would still "succeed" even
     * though the token is genuinely gone (this doesn't affect real
     * traffic, where every request is a fresh process).
     */
    public function test_a_user_can_log_out_and_the_token_is_revoked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-suite')->plainTextToken;
        $this->assertSame(1, $user->tokens()->count());

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_forgot_password_sends_a_reset_link_when_the_email_exists(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'staff@example.com']);

        $response = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'staff@example.com']);

        $response->assertOk();
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_returns_the_same_response_for_an_unknown_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'nobody@example.com']);

        $response->assertOk();
        $response->assertJson(['message' => 'If that email exists, a reset link has been sent.']);
        Notification::assertNothingSent();
    }

    public function test_a_user_can_reset_their_password_with_a_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com', 'password' => Hash::make('old-password')]);
        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'staff@example.com',
            'password' => 'Brand-New-Password9',
            'password_confirmation' => 'Brand-New-Password9',
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('Brand-New-Password9', $user->fresh()->password));
    }

    public function test_resetting_the_password_revokes_all_existing_tokens(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com', 'password' => Hash::make('old-password')]);
        $user->createToken('device-one');
        $user->createToken('device-two');
        $token = Password::createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'staff@example.com',
            'password' => 'Brand-New-Password9',
            'password_confirmation' => 'Brand-New-Password9',
        ])->assertOk();

        $this->assertSame(0, $user->fresh()->tokens()->count());
    }

    public function test_reset_password_fails_with_an_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'not-a-real-token',
            'email' => 'staff@example.com',
            'password' => 'Brand-New-Password9',
            'password_confirmation' => 'Brand-New-Password9',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email']);
        $this->assertFalse(Hash::check('Brand-New-Password9', $user->fresh()->password));
    }

    public function test_reset_password_requires_a_confirmed_password_of_minimum_length(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);
        $token = Password::createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'staff@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }
}
