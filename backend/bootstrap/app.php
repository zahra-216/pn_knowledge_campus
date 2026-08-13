<?php

use App\Http\Middleware\SecurityHeaders;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/*
|--------------------------------------------------------------------------
| Application Bootstrap (Laravel 12 style)
|--------------------------------------------------------------------------
|
| Laravel 12 no longer ships app/Http/Kernel.php or a RouteServiceProvider —
| routing, middleware groups, and exception rendering are all configured
| here. This file is the single place to look for "how does a request get
| routed and how do errors come back to the client" for this API.
|
*/

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // routes/web.php (audit fix, High remediation — the SEO meta
        // shell) is loaded via `then`, not the `web:` parameter, so it
        // never picks up Laravel's default 'web' middleware group
        // (sessions, CSRF, cookie encryption) — this route is a
        // read-only, cacheable content response with no session state
        // of its own, and a stray `Set-Cookie` header would work against
        // the very thing it exists for (a crawler-friendly, CDN-cacheable
        // public page).
        then: function (): void {
            require __DIR__.'/../routes/web.php';
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sanctum's stateful-SPA middleware is only needed if the SPA and
        // API ever share a cookie-based session domain. This API is
        // token-based (bearer tokens issued at /api/v1/auth/login), so the
        // default `api` middleware group (throttle + bindings) is enough.
        $middleware->api(prepend: [
            HandleCors::class,
        ]);

        $middleware->append(SecurityHeaders::class);

        $middleware->throttleApi();

        // Milestone 25 (Production Readiness) — trusts reverse-proxy
        // headers (X-Forwarded-For/Proto/Host) only from the hosts
        // listed in TRUSTED_PROXIES, so $request->ip()/isSecure() are
        // correct behind a load balancer or CDN instead of always
        // resolving to the proxy's own address/scheme. Unset (the
        // default), no proxy is trusted — correct for local development
        // and for a deploy with no reverse proxy in front of PHP at all.
        // Set to "*" if the proxy's IP isn't fixed (common on managed
        // platforms), or a comma-separated IP/CIDR list otherwise.
        $trustedProxies = env('TRUSTED_PROXIES');
        $middleware->trustProxies(
            at: $trustedProxies === '*' ? '*' : ($trustedProxies ? explode(',', $trustedProxies) : null)
        );

        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Audit fix (High remediation) — Laravel's default Authenticate
        // middleware calls route('login') to build AuthenticationException's
        // redirect target whenever the request doesn't explicitly send
        // Accept: application/json, and does so *before* our custom
        // AuthenticationException renderer below ever runs. This app has no
        // named 'login' route at all (it's a pure token API behind a
        // separate SPA), so that call itself threw RouteNotFoundException —
        // a real client hitting an admin endpoint without that header got a
        // raw 500 with a leaked stack trace instead of a clean 401,
        // reproduced live during the Phase 1 audit. Always redirecting
        // nowhere means the middleware only ever throws AuthenticationException
        // itself, which our renderer already turns into a proper 401.
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Every API error response follows the standard envelope defined
        // in the API Design document (Section 6). This is the one place
        // that guarantee is enforced, regardless of which layer threw.
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());

        $exceptions->render(function (ValidationException $e, Request $request) {
            return ApiResponse::validationError($e->errors());
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return ApiResponse::error('Unauthenticated.', 401);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            return ApiResponse::error('This action is unauthorized.', 403);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            $model = class_basename($e->getModel());

            return ApiResponse::error("{$model} not found.", 404);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            // Preserve a message from an explicit abort(404, '...') call
            // (e.g. SeoMetaController's "not a recognized entity type");
            // Symfony's own "no route matched" case has no message, so
            // the generic fallback still applies there.
            return ApiResponse::error($e->getMessage() ?: 'The requested endpoint does not exist.', 404);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            return ApiResponse::error($e->getMessage() ?: 'Request failed.', $e->getStatusCode());
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! app()->environment('production')) {
                return null; // let Laravel's default debug renderer take over locally
            }

            report($e);

            return ApiResponse::error('Something went wrong on our end. Please try again shortly.', 500);
        });
    })->create();
