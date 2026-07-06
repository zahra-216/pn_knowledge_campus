<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Milestone 25 (Performance Optimization / Security Review). This API
 * is JSON-only and never renders HTML of its own, but it's still fair
 * game to have its responses embedded, sniffed, or framed by a
 * malicious third-party page, and every one of these headers is cheap
 * insurance against that regardless of content type:
 *   - X-Content-Type-Options: stops a browser from ignoring the
 *     `application/json` Content-Type and re-sniffing a response as
 *     something executable.
 *   - X-Frame-Options: this API is never meant to be framed.
 *   - Referrer-Policy: avoids leaking full request URLs (which can
 *     contain query params like a search term or, for the frontend
 *     pages this API backs, tokens in edge cases) to third-party
 *     origins via the Referer header.
 *   - Permissions-Policy: this API never needs camera/microphone/
 *     geolocation; explicitly disclaiming them costs nothing.
 *   - Strict-Transport-Security is added only when the request already
 *     arrived over HTTPS — sending HSTS over plain HTTP is meaningless
 *     (the browser wouldn't apply it) and would be actively wrong to
 *     send in local development, where APP_URL is http://localhost.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
