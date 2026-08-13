# Changelog

All notable changes to this base are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Projects built from this base track it as a git `upstream` remote and merge tagged releases —
see [docs/UPGRADING.md](docs/UPGRADING.md).

## [Unreleased]

### Added

- `php artisan base:setup` — a single, idempotent installer (env file, application and JWT keys,
  **validated MySQL connection**, storage link, migrate/seed). CI (`--ci`) and `composer setup` both
  call it, so the documented install flow and the pipeline can no longer drift. MySQL is the official
  database — `--ci` requires a reachable MySQL and does not fall back to SQLite or auto-create the DB.
- `php artisan base:doctor [--production]` — environment and module health check. `--production`
  fails the process on unsafe settings (debug enabled, wildcard CORS, leftover or weak bootstrap admin
  credentials, `sync` queue, log/array mail, a non-HTTPS/localhost `APP_URL`) and warns on an insecure
  session cookie, so it can gate a deploy.
- `php artisan base:modules` — lists discovered modules with their provider, seeders, and route
  files, and flags any module that is present but not loaded.
- A **`Settings` module** — generic key/value configuration (`SettingsService::get/set/has/forget`,
  cached under `settings.all` and invalidated on write) with a dashboard editor gated by
  `settings-read`/`settings-update`. The base ships no business values — projects seed their own.
- `.nvmrc` and a `package.json` `engines` field pin Node 22.

### Changed

- **MySQL is the official database.** Tests, CI, and `base:setup` are MySQL-first: the SQLite test
  matrix and the experimental PHP 8.5 CI entry were removed, CI is simplified to **Quality + Tests
  (PHP 8.3/8.4 · MySQL)**, and `phpunit.xml` forces a dedicated `*test*` database (a `TestCase` guard
  refuses to run against any non-test database). `base:install` was renamed to **`base:setup`** and
  the test runner is now `vendor/bin/pest`.
- **Notifications are polymorphic** — `notifications.user_id` became `notifiable_type`/`notifiable_id`
  (`MorphTo`), so any Eloquent model (User, Manager, or a project's Vendor/Driver/Customer) can receive
  them. The notification engine no longer imports `Auth\Models\User`; the dashboard broadcast-to-users
  admin feature still targets users. Enforced by `FoundationArchitectureTest`.
- **File handling consolidated** into one `InteractsWithFiles` concern (merging `HasImages` +
  `FileTrait`, with a single `optimizeImage`). `HasDeviceTokens` moved from `Base` to `Notifications`,
  removing a `Base → Notifications` dependency inversion.
- **Static analysis raised to PHPStan level 8 with no baseline** (previously level 5 with a 23-entry
  baseline); the base `Repository` is now generic (`@template TModel`).
- `FoundationArchitectureTest` now enforces the module **contract** (StudlyCase name, a
  discoverable service provider, canonical surface paths) instead of a fixed whitelist of module
  names, plus new **boundary guards** (Base isolation, notification-engine independence, no resurrected
  file traits, Settings invariants). New modules are allowed as long as they conform, and a module that
  is present but not loaded fails loudly.
- CI upgraded to `actions/checkout@v7` and `actions/setup-node@v7`; Node is read from `.nvmrc`.
- Dependency audits are blocking and network-resilient: `composer audit` and
  `npm audit --omit=dev --audit-level=high` no longer pass silently on failure (the previous
  `continue-on-error` on the npm audit is gone), while transient registry/SSL errors are retried
  instead of turning `main` red.

### Removed

- Tailwind CSS from the foundation (`@tailwindcss/vite`, `tailwindcss`,
  `prettier-plugin-tailwindcss`, and the `@import 'tailwindcss'` layer in `resources/css/app.css`).
  The dashboard is Bootstrap 5 + Vuexy per AGENTS.md; Tailwind contradicted that policy. The
  custom `.dashboard-*` component styles are preserved.
- Education-specific fields from the Auth module — `account_type` (student/parent) and its enum,
  `birth_date`, `gender`, and `marketing_opt_in` — from the User model, migration, requests,
  resources, services, dashboard screens, and translations. Generic authentication, the phone/OTP
  flow, `country`, and `whatsapp` are unchanged.
- The education-legacy `teacher` role, the manager `title`/`bio`/`specialization` profile fields, and
  `isTeacher()`; the education-flavored auth flow test was renamed. The base ships generic manager
  accounts, not one product's role/profile schema.

<!-- Add the first tagged release below, e.g.:

## [0.1.0] - 2026-08-12
Initial versioned baseline of the reusable Laravel modular base.
-->
