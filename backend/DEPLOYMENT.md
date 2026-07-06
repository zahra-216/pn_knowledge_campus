# Production Deployment

Milestone 25 (Performance Optimization / Production Readiness). This is
the operational checklist for deploying the backend and frontend
outside of local development — it doesn't replace `.env.example`
(still the source of truth for individual variables), it's the order
of operations and the "why" behind each step.

## 1. Environment

Copy `.env.example` to `.env` and set at minimum:

- `APP_ENV=production`, `APP_DEBUG=false` — with debug on, unhandled
  exceptions leak stack traces (file paths, query bindings) to API
  clients. `bootstrap/app.php`'s exception handler already renders the
  generic "Something went wrong" message in production and never in
  local — this only takes effect if `APP_ENV` is actually set.
- `APP_URL` — the real public origin. `URL::forceScheme('https')` (see
  `AppServiceProvider::boot()`) only activates when `APP_ENV=production`.
- `TRUSTED_PROXIES` — set if a load balancer/Nginx/CDN sits in front of
  PHP (see `.env.example`'s comment and `bootstrap/app.php`). Leave
  unset if PHP receives requests directly.
- `LOG_STACK=daily`, `LOG_LEVEL=warning` — the `single` channel used in
  local dev grows one unbounded file forever; `daily` rotates. `debug`
  level is noisy in production; `warning` still catches everything
  `report()` sends.
- `CACHE_STORE=database` (default) is fine for a single app server. If
  you run more than one app server behind a load balancer, every
  server must share one cache backend (e.g. the same database, or
  Redis) — PublicCache's invalidation (see `app/Support/PublicCache.php`)
  only reaches whichever process handles the write.
- `QUEUE_CONNECTION=database` (default) — same shared-backend note
  applies if running multiple app servers.

## 2. Install & build (backend)

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force   # only if APP_KEY isn't already set
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan event:cache
```

Re-run the three `:cache` commands after every deploy that changes
`.env`, routes, config, or event listeners — Laravel reads the cached
versions once they exist and won't pick up file changes otherwise.

## 3. Queue worker

Every Notification since Milestone 23 (application/inquiry emails,
in-app notifications) is `ShouldQueue` — it sits in the `jobs` table
until a worker processes it. Install
[`deploy/supervisor-queue-worker.conf`](deploy/supervisor-queue-worker.conf)
under Supervisor (or an equivalent process manager) so it's supervised
and auto-restarting, not a manually-started `queue:work` that dies
with the terminal session. Without this, queued notifications
accumulate in `jobs` and are never delivered.

## 4. Scheduler

`routes/console.php` registers the scheduled-publish jobs (Pages,
Blog, News) and `sitemap:generate` (daily). Add one cron entry:

```
* * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
```

## 5. Frontend

```bash
cd ../frontend
npm ci
npm run build
```

Serve `dist/` from a static host / CDN / Nginx. `vite.config.ts`
already splits every route into its own lazily-loaded chunk (Milestone
25) and disables sourcemaps for the production build — no extra build
flags needed. Enable gzip/brotli compression at whichever layer serves
these static files (Nginx's `gzip on`, or your CDN's built-in
compression) — Vite doesn't pre-compress assets itself, and this is
normally a one-line web-server/CDN config rather than a build step.

## 6. Verify

- `GET /up` — Laravel's built-in health check.
- `GET /api/v1/settings/public` — should return `Cache-Control: public,
  max-age=60` (confirms the `cache.headers` middleware and
  `PublicCache` layer, see `routes/api.php`) and the security headers
  from `SecurityHeaders` middleware (`X-Frame-Options`, etc.).
- Submit a real inquiry or application, then confirm the queue worker
  actually drains `jobs` (`php artisan queue:monitor` or just watch the
  table) instead of it growing unboundedly.
