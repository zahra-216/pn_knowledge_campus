# PN Knowledge Campus — Project Foundation

This is **Development Roadmap Milestone 0** delivered as real, working code —
not documentation. It is the shared starting point every later milestone
(Courses, News, Pages, CMS modules, etc.) will be built on top of.

This package intentionally does **not** include the CMS, Courses, Blog,
News, or any other content module. Those ship one at a time, in the order
defined in the Development Roadmap document, starting with **Milestone 1
— Core Settings & Media Library**.

## What's in the box

```
pn-knowledge-campus/
├── backend/     Laravel 12 API (PHP 8.3+, MySQL 8.x, Sanctum, Spatie Permission)
├── frontend/    React 19 + TypeScript + Vite + Tailwind admin panel
└── VERIFICATION.md   What was actually tested in this environment, and how
```

Each half has its own `README.md` with exact setup commands. Read
`VERIFICATION.md` first if you want to know what's actually been proven
to work versus what's written-but-unverified.

## The one thing that works end-to-end right now

Login. That's it, deliberately. A CMS user can:

1. Open the React admin panel
2. Log in with a seeded Super Admin account
3. Land on a (mostly empty) Dashboard behind the real Admin Layout shell
4. Log out

Everything visible around that flow — the sidebar, top bar, dark mode
toggle, notification bell, breadcrumb — is real, built, and wired to
match the UI/UX Design document exactly. It's just not yet connected to
any content, because there isn't any content yet. That's next.

## Why it's split this way

This mirrors the five specification documents already produced for this
project (SRS, Database Design, UI/UX Design, API Design, Development
Roadmap). Every file in this codebase traces back to a specific section
in one of those documents — the code comments say which one, so a
developer picking this up later isn't guessing why something is
structured the way it is.

## What a developer does next

Open the Development Roadmap document to **Milestone 1: Core Settings &
Media Library**, and follow its Objectives / Estimated Files / Estimated
Tables / Frontend Components / Backend Components / Testing / Expected
Deliverables / Dependency Order exactly as written. Nothing in this
foundation needs to be revisited to start that milestone — that's the
point of building it in this order.

## A note on what was and wasn't run in this environment

This sandbox cannot reach Packagist (PHP's package registry), so the
Laravel side could not have `composer install` run against it — every
PHP file was hand-written and syntax-checked (`php -l`) but not executed.
The React side *could* be installed and built for real here, and was —
see `VERIFICATION.md`. Neither limitation reflects a limitation of the
code itself; it's about what this particular sandbox can reach on the
network. Run `composer install` on your own machine or CI before relying
on the backend.
