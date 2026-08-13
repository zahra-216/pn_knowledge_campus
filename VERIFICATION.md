# Verification

*(Audit fix, Medium remediation — this file previously described a single
point-in-time session where the backend couldn't be installed at all
("this sandbox's network allowlist does not include packagist.org") and
only the frontend build had been verified. That limitation no longer
applies; both halves install, run, and are tested in full today. Rather
than replace one stale snapshot with another that will just as surely go
stale, this file now describes *how* to verify the project, with
`backend/DEPLOYMENT.md` as the canonical, living reference for anything
deployment-specific.)*

## Backend (Laravel 12 / PHP)

```
cd backend
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan test        # full PHPUnit suite
./vendor/bin/pint --test
```

The seeder creates the five baseline roles (Super Admin, Administrator,
Content Editor, Marketing, Admissions) and a Super Admin account — see
`database/seeders/RoleSeeder.php` for the exact permission matrix and
`database/seeders/SuperAdminUserSeeder.php` for the seeded login
credentials.

## Frontend (React 19 / TypeScript / Vite)

```
cd frontend
npm ci
npm run typecheck   # tsc -b --noEmit
npm run lint         # eslint, zero warnings tolerated
npm test              # vitest
npm run build          # production build, both the admin and public routes
```

## A live round trip

1. `cd backend && php artisan serve`
2. `cd frontend && npm run dev`
3. Open the frontend's dev URL, log in with the seeded Super Admin
   credentials, confirm the Dashboard loads with real published-content
   counts and a recent-activity feed.
4. Open the public site (`/`) in the same browser — it's the same SPA,
   just the unauthenticated routes — and confirm a real Course/Blog/News
   detail page renders.
5. `curl -s http://localhost:8000/courses/some-real-slug | grep '<title>'`
   — confirms the server-rendered SEO shell (`routes/web.php` +
   `SeoShellController`) is returning real per-page meta tags in the
   initial HTTP response, not just what client-side JS sets afterward.

## What isn't covered by automated tests yet

Tracked honestly rather than silently: browser-based accessibility/visual
regression testing, load/performance testing against the SRS's Lighthouse
NFRs, and a handful of smaller model relationships added as
minimal-surface fixes without full CRUD/UI coverage (see each model's own
docblock — e.g. `Event::faqs()`, `News::tags()`). None of these block
using or extending the project; they're just not yet asserted by a test
you can run.
