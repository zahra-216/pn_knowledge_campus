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

**A freshly migrated database has no roles, no permissions, and no
admin account** — `migrate` alone gets you an empty schema, not a
usable CMS. Seed at least the two baseline seeders before anyone tries
to log in:

```bash
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=SuperAdminUserSeeder --force
```

Change the seeded Super Admin password (`superadmin@pnknowledgecampus.edu`
/ `ChangeMe!12345` — see `database/seeders/SuperAdminUserSeeder.php`)
immediately after first login, before this instance is reachable
publicly.

That's enough to log in and run the CMS with real, empty content.
`DatabaseSeeder` (`php artisan db:seed --force`, or `migrate --seed` in
one step) goes further and seeds the rest of the baseline — Settings
defaults, the header/footer menu structure, course levels/modes/
categories, and *also* sample Faculty/Course/Blog/News/Event
records — appropriate for a demo or staging launch, not necessarily
for a production instance you want to start empty and populate with
real institutional content through the Admin CMS. `DemoContentSeeder`
(`php artisan db:seed --class=DemoContentSeeder`) goes further still,
attaching real photos via the Media Library — for demonstration/
evaluation environments only.

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

**Deploy this backend and the frontend's `dist/` on the same host/
filesystem** (audit fix, High remediation — a topology change from
this doc's earlier "separate static host/CDN" note). `routes/web.php`'s
catch-all reads `frontend/dist/index.html` directly off disk to inject
real, server-rendered `<title>`/meta tags per request before handing
off to the compiled SPA bundle — the SRS's binding "server-rendered
meta tags per page" requirement can't be met by a purely client-side
SPA, since crawlers/unfurlers that don't execute JS only ever see
whatever HTML the very first response contains. `config/frontend.php`'s
`dist_path` defaults to the sibling `frontend/dist` directory (matching
where `sitemap:generate` already writes its own output for the same
reason) — set `FRONTEND_DIST_PATH` if the build ever lives somewhere
else on the same host.

Static assets (`dist/assets/*`) can still be served directly by Nginx/a
CDN in front of PHP — only the HTML *document* itself now goes through
this backend; nothing about `vite.config.ts`'s per-route code-splitting
or asset hashing changes. Enable gzip/brotli compression at whichever
layer serves these static files (Nginx's `gzip on`, or your CDN's
built-in compression) — Vite doesn't pre-compress assets itself, and
this is normally a one-line web-server/CDN config rather than a build
step.

If the frontend truly cannot be colocated with this backend in your
deployment, `SeoShellController` returns a `503` with a clear message
rather than a broken page — the SPA will need to be served from
wherever it lives instead, without server-rendered meta tags (a
regression from the SRS requirement, tracked as a known limitation of
that topology, not silently masked).

## 6. Web server & TLS

A minimal Nginx server block — PHP-FPM handles `.php` requests (which
includes every route, since `routes/web.php`'s catch-all and the API
both go through `public/index.php`), static assets are served directly:

```nginx
server {
    listen 80;
    server_name yourdomain.example;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.example;
    root /var/www/pn-knowledge-campus/backend/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/yourdomain.example/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.example/privkey.pem;

    client_max_body_size 12M; # matches MEDIA_MAX_UPLOAD_SIZE's default headroom

    gzip on;
    gzip_types text/css application/javascript application/json image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}
```

Obtain the certificate with Certbot (auto-renews via its own installed
systemd timer/cron):

```bash
sudo certbot --nginx -d yourdomain.example
```

If TLS terminates somewhere other than this Nginx (a load balancer or
CDN in front of it), set `TRUSTED_PROXIES` (Section 1) so
`$request->ip()`/`isSecure()` resolve correctly instead of always
seeing the proxy's own address/scheme — with `APP_ENV=production`,
every URL this app generates is forced to `https://` (`AppServiceProvider`),
which depends on Laravel correctly knowing the original request really
was HTTPS.

## 7. Verify

- `GET /up` — Laravel's built-in health check.
- `GET /api/v1/settings/public` — should return `Cache-Control: public,
  max-age=60` (confirms the `cache.headers` middleware and
  `PublicCache` layer, see `routes/api.php`) and the security headers
  from `SecurityHeaders` middleware (`X-Frame-Options`, etc.).
- Submit a real inquiry or application, then confirm the queue worker
  actually drains `jobs` (`php artisan queue:monitor` or just watch the
  table) instead of it growing unboundedly.
- `curl -s https://yourdomain/courses/some-real-slug | grep -i '<title>'`
  (or any real public page) — confirm the returned `<title>`/`<meta>`
  tags are the entity's own real values, not a generic placeholder or
  a `503`. `view-source:` in a real browser works too; DevTools'
  rendered-DOM view does not (it shows post-JS state, not what a
  crawler actually received).

## 8. Backups & Restore

(Audit fix — no backup strategy existed before this; the SRS gates
go-live on it: "Automated daily database backups and a documented
restore procedure prior to go-live," Roadmap Stage 10.)

`spatie/laravel-backup` is installed and scheduled (`routes/console.php`):
`backup:clean` (01:30), `backup:run` (02:00), `backup:monitor` (03:00) —
all daily, via the same cron entry as the scheduler in Step 4, no
separate cron line needed. Each run dumps the database (`mysqldump`,
via `spatie/db-dumper` — **must be installed on the production host's
PATH**, this is a system package, not a Composer one) plus
`storage/app/public` and `storage/app/private` (Media Library uploads
and Application documents — see `config/backup.php`'s docblock for why
the codebase itself isn't included: it's in git now, a separate
recovery path). The zip lands on the `backups` disk
(`config/filesystems.php` → `storage/app/backups` by default).

**Off-server copy — do this before go-live, not after an incident.** A
backup that lives on the same disk as the database it's backing up
does not protect against that disk/server failing. At minimum, mount
`storage/app/backups` to an off-server destination (rsync to another
host, or add an `s3`-driver disk — already stubbed in
`config/filesystems.php` — to `config/backup.php`'s `destination.disks`
array once real S3/Spaces/R2 credentials exist).

**Notifications** — a failed backup or a failed `backup:monitor` health
check emails `BACKUP_NOTIFICATION_EMAIL` (`.env.example`; falls back to
`MAIL_FROM_ADDRESS` if unset). Don't rely on "no news is good news" —
until `MAIL_MAILER` is a real driver (not `log`), those notifications go
nowhere.

**Encryption** — set `BACKUP_ARCHIVE_PASSWORD` (`.env.example`) before
go-live. Unset, the zip is unencrypted, and it contains the full
database — applicant PII included — so an off-server copy is only as
safe as whatever's holding it. Store the password in your secrets
manager, not in `.env` on the server it's backing up.

**Restore procedure:**

```bash
# 1. Retrieve the desired dated zip from the backups disk (or its
#    off-server copy) and unzip it locally.
unzip 2026-07-06-02-00-00.zip -d restore/

# 2. Restore the database dump (filename matches DB_CONNECTION, e.g. mysql.sql).
mysql -u <user> -p <database> < restore/db-dumps/mysql.sql

# 3. Restore uploaded files back into place (stop the app or put it in
#    maintenance mode first — `php artisan down` — so nothing writes to
#    these paths mid-restore).
rsync -a restore/storage/app/public/ storage/app/public/
rsync -a restore/storage/app/private/ storage/app/private/

# 4. Bring the app back and re-verify Step 6 above.
php artisan up
```

Test this procedure against a staging copy before go-live — a restore
procedure that's only ever been read, never run, is not a verified one.
