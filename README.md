# Laravel Modular Base

A production-minded Laravel 13 foundation for starting new projects without carrying product-specific legacy code. The maintained modules are `Auth`, `Base`, `Notifications`, and `Structure`.

It ships as an **engineering system**, not just a starter: a version-aware AI toolchain (Laravel Boost, project rules, review skills), static analysis (Larastan), PHP formatting (Pint; Blade is hand-formatted), CI, and dependency automation. See [AGENTS.md](AGENTS.md).

## Requirements

- PHP 8.3 or newer with the database driver you intend to use.
- Composer 2.
- Node.js 22 or newer and npm.
- A database: MySQL (the `.env.example` default) or PostgreSQL, or PHP's `pdo_sqlite` extension for a zero-dependency SQLite setup.

## First installation

```bash
git clone https://github.com/EngMahmoudHafez/BaseHafez.git my-project
cd my-project
composer setup
```

`composer setup` is the single, idempotent installer. It installs PHP and npm dependencies, creates `.env`, generates the application key and JWT secret, prepares the database (MySQL by default — edit the `DB_*` block first, or set `DB_CONNECTION=sqlite` for a zero-dependency setup and the installer creates the file for you), links storage, runs migrations and seeders, and builds the frontend. Re-running it is safe. The Laravel-side steps are one command — `php artisan base:install` — which CI (`--ci`) and `composer create-project` also run, so the documented flow and the automation never drift.

**Using MySQL or PostgreSQL?** Create and edit `.env` first, then run setup — `base:install` leaves an existing `.env` untouched:

```bash
cp .env.example .env      # then set the DB_* block
composer setup
```

To create the first dashboard manager, set `BASE_ADMIN_EMAIL` and a `BASE_ADMIN_PASSWORD` of at least 12 characters in `.env`, then run `php artisan base:install --seed`. Never reuse production credentials in a test environment.

The foundation seeder is repeatable and does not delete existing data. It creates roles, permissions, countries, and generic site content. The first dashboard manager is created only when `BASE_ADMIN_EMAIL` is valid and `BASE_ADMIN_PASSWORD` contains at least 12 characters; otherwise that step is deliberately skipped. Remove those bootstrap credentials from the deployment environment after the account exists.

Email password recovery uses the configured Laravel mailer. Phone OTP generation is included, but production delivery is intentionally not tied to a vendor: add the project SMS/WhatsApp provider in its own specification before launch. The fixed `1111` code and response hint exist only in `local` and `testing`; every other environment receives a random code that is never returned by the API.

For browser clients, set `CORS_ALLOWED_ORIGINS` to a comma-separated allowlist and enable `CORS_SUPPORTS_CREDENTIALS` only when cookie-based cross-origin requests are actually required. The wildcard default does not support credentials.

Start the complete local stack with:

```bash
composer dev
```

## Quality checks

Run the local gate with one command:

```bash
composer check   # Pint (PHP) --test, Larastan, tests, composer audit
```

The individual gates, matching CI (`.github/workflows/ci.yml`):

```bash
composer validate --strict
vendor/bin/pint --test        # formats PHP (Blade is hand-formatted)
vendor/bin/phpstan analyse    # Larastan static analysis
php artisan test --compact
composer audit && npm audit
npm run build
```

The test suite defaults to an in-memory SQLite database. Install `pdo_sqlite`, or provide a dedicated disposable test database through environment variables.

Blade templates are hand-formatted: Pint formats PHP only (the prettier-blade fixer was removed for silently corrupting directives). See [.ai/rules/blade.md](.ai/rules/blade.md).

## Production readiness

Before deploying, run the hardening check. It fails on unsafe settings (debug enabled, wildcard CORS, leftover or weak bootstrap admin credentials, a `sync` queue, log/array mail, or a non-HTTPS/localhost `APP_URL`) so a misconfigured environment cannot ship:

```bash
php artisan base:doctor --production
```

Run `php artisan base:modules` to see every discovered module and confirm none is "present but not loaded".

### Background processing

Notifications (FCM push) run through the queue and the module ships a daily cleanup on the scheduler, so a real deployment needs both a worker and the scheduler running. Do **not** leave `QUEUE_CONNECTION=sync` in production (`base:doctor --production` fails on it):

```bash
php artisan queue:work --tries=3        # or a Supervisor program
php artisan schedule:work               # or a cron running: * * * * * php artisan schedule:run
```

Set `BROADCAST_CONNECTION` and the FCM/service-account credentials only when realtime web push and device push are actually wired for the project.

## Staying in sync with the base

This repository is a base you keep pulling from — not a one-time clone. A project tracks it as a git `upstream` remote and merges tagged releases, so security fixes reach every project instead of being re-applied by hand. See [docs/UPGRADING.md](docs/UPGRADING.md) and [CHANGELOG.md](CHANGELOG.md).

```bash
git remote add upstream https://github.com/EngMahmoudHafez/BaseHafez.git
git fetch upstream --tags
git merge vX.Y.Z
```

## Engineering toolchain

| Tool | Purpose |
| --- | --- |
| **Laravel Boost** | Version-aware Laravel docs (`search-docs`) and `record-rule` via MCP (`.mcp.json`). |
| **`.ai/rules/`** | Path-scoped project rules the AI must follow (committed). |
| **Review skills** (`.claude/skills/`) | `laravel-guard`, `laravel-modernizer`, `blade-dashboard-review`, plus `clean-code-guard`, `test-guard`, `docs-guard`. |
| **Larastan** | Static analysis (`phpstan.neon`, `phpstan-baseline.neon`). |
| **Pint** | Formatting for PHP (Blade is hand-formatted). |
| **Telescope** | Local-only dev observability (excluded from auto-discovery; registered only in `local`). |
| **CI + Dependabot** | `.github/workflows/ci.yml` runs the gates; weekly dependency updates. |

To generate Boost's AI-guideline files for your editor, run `php artisan boost:install` (interactive). Keep Boost resources fresh after dependency changes with `php artisan boost:update`.

## Creating a module

```bash
php artisan make:module Catalog
php artisan make:module-model Catalog Product --all
```

Generated files follow the repository's canonical paths. They are scaffolding: define real validation, authorization, routes, views, and tests before shipping.

Read [AGENTS.md](AGENTS.md) before any change. Then use the [architecture](docs/architecture.md), [dashboard design system](docs/dashboard-design-system.md), and [workflow](docs/workflow.md) docs as the maintained operating guide. The [foundation audit](docs/base-audit-report.md) is a dated historical snapshot (superseded — see its banner), kept for context only.
