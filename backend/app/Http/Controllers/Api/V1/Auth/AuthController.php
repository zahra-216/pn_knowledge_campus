<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

/**
 * Implements every endpoint in the API Design document, Section 2.2 —
 * this is the only real feature surface in the Milestone 0 foundation
 * build; everything else in this milestone is scaffolding for it.
 */
class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email'))->first();

        if (! $user || ! Auth::getProvider()->validateCredentials($user, $request->only('password'))) {
            return ApiResponse::validationError(
                ['email' => ['These credentials do not match our records.']]
            );
        }

        if (! $user->is_active) {
            return ApiResponse::error('This account has been deactivated. Contact a Super Admin.', 403);
        }

        $token = $user->createToken($request->string('device_name'));

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return ApiResponse::success([
            'token' => $token->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::message('Logged out.');
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    /**
     * POST /api/v1/auth/forgot-password
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink($request->only('email'));

        // Always return the same message regardless of whether the email
        // exists, so this endpoint cannot be used to enumerate accounts.
        return ApiResponse::message('If that email exists, a reset link has been sent.');
    }

    /**
     * POST /api/v1/auth/reset-password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
                $user->tokens()->delete(); // password change revokes all existing sessions
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return ApiResponse::validationError(
                ['email' => [__($status)]]
            );
        }

        return ApiResponse::message('Password has been reset.');
    }
}
