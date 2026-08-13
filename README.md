# PN Knowledge Campus

A CMS-driven higher-education institution website: a Laravel 12 API backend
and a React 19 + TypeScript admin panel + public site, built module-by-module
against five specification documents (SRS, Database Design, API Design,
UI/UX Design, Development Roadmap).

*(Audit fix, Medium remediation — this file and `VERIFICATION.md` previously
described a "Milestone 0, only login works" foundation state. Both are
rewritten here to reflect what has actually shipped since; see
`backend/DEPLOYMENT.md` for the living, most-current operational reference.)*

## What's in the box

```
pn-knowledge-campus/
├── backend/     Laravel 12 API (PHP 8.3+, Sanctum, Spatie Permission, Spatie Media Library)
└── frontend/    React 19 + TypeScript + Vite + Tailwind — admin panel and public site in one SPA
```

## What's actually built

- **Admin CMS**: Settings, Media Library, Menus, Page Builder, Homepage
  Builder, Hero Slider, Faculties/Departments/Courses, Blog, News, Events,
  Gallery, Testimonials, Partners, FAQ, Downloads catalog, Applications
  review queue, Inquiry inbox (with staff assignment and follow-up notes),
  SEO Manager, Users/Roles/Permissions, Dashboard analytics.
- **Public website**: every corresponding public page (home, listings,
  detail pages, static Page-Builder pages, search, apply/enquire flows),
  server-rendered per-page `<title>`/meta tags for real crawlers (not just
  client-side `document.title`), a sitemap/robots generator, and gated
  downloads (a capture form + signed URL in front of a file).
- **Auth & RBAC**: Sanctum bearer tokens (now with a 30-day expiration and
  an admin "revoke all sessions" action), five baseline roles enforced via
  Spatie Permission against the SRS's own Permission Matrix.
- **Automated backups**: `spatie/laravel-backup`, scheduled daily (see
  `backend/DEPLOYMENT.md`'s "Backups & Restore" section).
- **Tests**: 439+ PHPUnit tests (backend) and a Vitest + React Testing
  Library suite (frontend) covering the highest-risk flows (auth, gated
  downloads, the public header's menu-visibility logic). Run them yourself
  rather than trusting a point-in-time log — see "How to verify this
  yourself" below.

## Setup

Each half has its own `README.md` with exact local-dev setup commands
(`backend/README.md`, `frontend/README.md`) — those still describe the
Milestone 0 foundation's file layout accurately for what shipped in that
milestone, they just haven't been extended to narrate every later one.
For everything about running this in production — environment variables,
the queue worker, the scheduler, backups, and how to verify a deploy —
**`backend/DEPLOYMENT.md` is the canonical, currently-maintained reference.**

## How to verify this yourself

Don't rely on a stale log — run the real suites:

```
cd backend && php artisan test && ./vendor/bin/pint --test
cd frontend && npm run typecheck && npm run lint && npm test && npm run build
```

All four should pass clean on a checkout with `composer install`/`npm ci`
already run and a configured `.env`.

## Why it's split this way

Every file in this codebase traces back to a specific section in one of
the five specification documents — the code comments say which one, so a
developer picking this up later isn't guessing why something is structured
the way it is.
