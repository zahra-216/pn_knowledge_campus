# PNK Global Campus — Backend (Foundation)

This is the **Milestone 0 foundation** from the Development Roadmap: Laravel 12
project structure, authentication, RBAC, database connection, and the API
architecture conventions that every later module builds on.

**Not included yet (by design):** Pages, Menus, Courses, News, Blog, Media
Library UI, Settings, Inquiries, SEO Manager. Those ship module-by-module
starting at Milestone 1, per the Development Roadmap document.

## What's here

```
app/
  Http/
    Controllers/Api/V1/Auth/AuthController.php   Login, logout, me, forgot/reset password
    Requests/Auth/                               Form Request validation for the above
    Resources/UserResource.php                    Shapes the user payload
  Models/User.php                                 Sanctum + Spatie HasRoles
  Providers/AppServiceProvider.php                 Morph map registration
  Support/
    ApiResponse.php                                Standard success/error envelope (API Design §4, §6)
    Concerns/HasAuditColumns.php                    created_by/updated_by convention, used by every future model
bootstrap/
  app.php                                          Laravel 12 routing + middleware + exception handling (replaces Kernel.php)
config/                                            app, auth, sanctum, permission, cors, database, cache, queue, mail, filesystems, logging
database/
  migrations/                                      users, cache, jobs, personal_access_tokens, permission tables
  seeders/                                          5 baseline roles + one Super Admin user
routes/
  api.php                                           /api/v1/auth/* wired; /api/v1/admin and public groups left as empty placeholders
```

## Setup

This container has no internet access to Packagist, so `vendor/` is **not**
included and `composer install` was not run here. On your own machine or CI:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your real database credentials (see `DB_*` keys — MySQL 8.x
per the Database Design document), then:

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

This seeds one login you can use immediately:

```
email:    superadmin@pnknowledgecampus.edu
password: ChangeMe!12345
```

**Change this password immediately** via `POST /api/v1/auth/reset-password`
once you have a working frontend, or via `php artisan tinker` in the
meantime. It exists only so the frontend has something to authenticate
against on day one.

## Verifying it works

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"superadmin@pnknowledgecampus.edu","password":"ChangeMe!12345","device_name":"curl-test"}'
```

You should get back a `token` and a `user` object with `"role": "Super Admin"`.

## Conventions every future module must follow

These aren't optional style preferences — the Database Design, API Design,
and Development Roadmap documents assume them:

- **Every new table** gets a Migration, Model, Form Request, API Resource,
  and Policy — no exceptions, per the SRS code standards.
- **Every writable model** uses `App\Support\Concerns\HasAuditColumns`.
- **Every controller response** goes through `App\Support\ApiResponse` —
  never `return response()->json(...)` directly, so the envelope shape
  never drifts between modules.
- **Every new permission** follows the `{module}.{action}` naming used in
  `RoleSeeder` (e.g. `courses.view`, `courses.publish`).
- **Routes** are added inside `routes/api.php`'s existing `admin` or public
  group for that module — do not create a second top-level route file.

## Next step

Development Roadmap → **Milestone 1: Core Settings & Media Library**.
