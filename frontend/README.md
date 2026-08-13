# PNK Global Campus — Frontend (Foundation)

This is the **Milestone 0 foundation** from the Development Roadmap: React 19
+ TypeScript + Vite + Tailwind, wired to the Laravel backend's Auth API,
with the Admin Layout shell, routing, theme, and reusable component
library every later screen builds on.

**Not included yet (by design):** Pages, Menus, Courses, News, Blog,
Media Library, Settings screens, Inquiries. The Sidebar already lists
those groups (per the UI/UX Design's Admin Sitemap) but they're disabled
placeholders — real screens ship module-by-module starting at Milestone 1.

This project was built and verified in this environment: `npm install`,
`npm run build`, and `npm run lint` all pass cleanly (see the root
`VERIFICATION.md` for the full log). `node_modules` and `dist` are not
included in this delivery — run `npm install` yourself to restore them.

## What's here

```
src/
  components/ui/        Button, Input, Card, Badge, Spinner, EmptyState, Toast — Component Library §6
  context/AuthContext.tsx   Login/logout/me state, the only global state in this build
  hooks/
    usePermission.ts        can()/canAny() permission checks, mirrors the SRS Permission Matrix
    useTheme.ts              Dark mode toggle + persistence
  layouts/AdminLayout/    TopBar, Sidebar, Breadcrumb, NotificationsPanel, ProfileMenu — UI/UX Design §5.2
  lib/
    api.ts                   The Axios instance — auth header injection, 401 handling, error normalization
    endpoints.ts             Every API path, in one place
    storage.ts               Token persistence, isolated behind one module
  pages/
    auth/Login.tsx
    Dashboard.tsx            Placeholder — real widgets ship in Milestone 1
  routes/
    AppRoutes.tsx            Every route in the app, declared once
    ProtectedRoute.tsx       Redirects unauthenticated visits to /login
  styles/index.css          Tailwind + dark-mode CSS variables + focus-visible + reduced-motion
  types/                    auth.ts, api.ts — matches the backend's API Resources exactly
  utils/                    cn.ts (classnames), formatDate.ts
tailwind.config.ts          Every color/type/spacing token from the UI/UX Design document, by name
```

## Setup

```bash
npm install
cp .env.example .env
npm run dev
```

Point `VITE_API_BASE_URL` in `.env` at your running backend (default
assumes `php artisan serve` on `localhost:8000`).

Log in with the seeded Super Admin from the backend README:

```
email:    superadmin@pnknowledgecampus.edu
password: ChangeMe!12345
```

## Commands

| Command | What it does |
|---|---|
| `npm run dev` | Start the Vite dev server on `:5173` |
| `npm run build` | Type-check (`tsc -b`) then production-build |
| `npm run lint` | ESLint, zero warnings allowed |
| `npm run preview` | Serve the production build locally |

## Conventions every future screen must follow

- **Never call `axios` directly** — import `api` from `src/lib/api.ts`.
  It already attaches the auth token and normalizes errors.
- **Never write a raw `/api/v1/...` string** — add the path to
  `src/lib/endpoints.ts` once, import it everywhere.
- **Every new UI element** checks `src/components/ui/` first. If
  something close already exists, extend it — don't create a
  near-duplicate.
- **Every screen behind login** is a nested `<Route>` under
  `/admin` in `AppRoutes.tsx`, so it renders inside `AdminLayout`
  automatically — never build a screen with its own top bar/sidebar.
- **Colors, fonts, spacing** come from `tailwind.config.ts`'s named
  tokens (`text-navy`, `font-display`, `text-h2`, `shadow-2`, ...) —
  never a raw hex code or arbitrary pixel value in a component.

## Next step

Development Roadmap → **Milestone 1: Core Settings & Media Library**.
