<?php

use App\Http\Controllers\Web\SeoShellController;
use Illuminate\Support\Facades\Route;

/**
 * Audit fix (High remediation) — this app had no `web` routing group at
 * all (see GenerateSitemap's docblock: "no Laravel web routing group").
 * The catch-all below exists solely to serve the compiled public SPA
 * with real server-rendered meta tags per request (SeoShellController) —
 * it is not a second application surface, and `/admin/*` gets no special
 * treatment here (same shell, generic title) since that section is
 * authenticated tooling, not indexed content.
 *
 * The negative lookahead excludes every path this app already serves
 * through a different mechanism: `api/*` (routes/api.php), `up`
 * (the health check), `storage/*` (Media Library's public symlink,
 * only relevant when running via `php artisan serve` locally — a real
 * deploy's web server serves that path directly and never reaches PHP),
 * and `sanctum/*` (the csrf-cookie route Sanctum's own service provider
 * registers once a `web` middleware group exists — unused by this
 * bearer-token API, but harmless to leave reachable).
 */
Route::get('/{path?}', [SeoShellController::class, 'show'])
    ->where('path', '^(?!api(?:/.*)?$)(?!up$)(?!storage(?:/.*)?$)(?!sanctum(?:/.*)?$).*$')
    ->name('web.shell');
