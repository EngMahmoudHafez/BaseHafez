# AGENTS.md

Operating rules for this Laravel base. Read this file first, then the files under
`specs/` for the feature you are touching. These rules bind humans and AI agents equally.

This base is an **engineering system**, not a demo starter. Its value is that every project
started from it stays consistent, secure, and easy to evolve. Prefer deleting complexity over
adding it.

## 1. Golden rules

1. **Prefer Laravel-native APIs.** Before writing custom code, check whether the framework or an
   installed first-party package already solves it. Search version-specific docs with Laravel
   Boost (`search-docs`) instead of guessing an API.
2. **Newer is not automatically better.** Use a newer Laravel/PHP API only when it reduces
   complexity, improves correctness or readability, removes duplication, or matches the
   surrounding code. Preserve existing behavior.
3. **Controllers stay thin.** Validation lives in Form Requests, authorization in
   middleware/policies, and the workflow in a Service. See `docs/architecture.md`.
4. **Do not introduce a second UI, icon, or CSS framework.** The dashboard is Bootstrap 5 +
   Tabler icons (Vuexy). No Livewire, Alpine, Tailwind-in-Blade, Vue, or React in the dashboard.
   See `docs/dashboard-design-system.md`.
5. **Escape by default.** `{{ }}` for any database, user, flash, or editor value. `{!! !!}` only
   for application-generated HTML that already passed an explicit allow-list boundary. Pass server
   values to JavaScript with `@js`, never by interpolating raw Blade into a script literal.
6. **Every module owns its complete slice.** Routes, migrations, seeders, views, translations,
   permissions, and tests live with the module. `Base` owns shared plumbing only.
7. **Do not add a package or abstraction to wrap one Eloquent call.** A speculative repository
   method, service, or DTO for a single read is over-engineering.
8. **Never commit secrets or fixed passwords.** Seeders are idempotent and credential-free; the
   first admin comes from `BASE_ADMIN_*` only.
9. **A check that did not run is not a pass.** Report environment limitations explicitly.

## 2. Stack and versions

| Layer | Choice |
| --- | --- |
| Framework | Laravel **13.x** |
| PHP | **8.3+** |
| Auth | JWT (`tymon/jwt-auth`) for the API, session guards for web/dashboard; Laratrust for roles/permissions |
| Dashboard | Blade + Bootstrap 5 + Tabler icons (Vuexy), Vite, `x-dashboard.*` components |
| i18n | `mcamara/laravel-localization`, Arabic + English, RTL + LTR |
| Static analysis | Larastan (PHPStan) |
| Style | Laravel Pint (PHP only; Blade is hand-formatted) |
| Tests | Pest 4 (on PHPUnit 12), disposable in-memory SQLite |
| AI | Laravel Boost, `.ai/rules`, project skills under `.claude/skills`, guard skills |
| Dev observability | Telescope (local only) |

Do not change a major version, swap a core package, or add a framework without a spec and a
recorded decision in §7.

## 3. Architecture (summary)

The full contract is in `docs/architecture.md`. `app/Modules` is the source of truth and is meant
to grow. `FoundationArchitectureTest` enforces the module **contract** — a StudlyCase name, a
discoverable service provider, and canonical surface paths — not a fixed list of names. Any module
is allowed if it conforms; a module that is present but not loaded fails the test. Inspect
discovery with `php artisan base:modules` and health with `php artisan base:doctor`.

Request flow:

```text
Route → Controller → FormRequest + permission/policy → Service (workflow, transaction)
                                                     ↘ API Resource  |  owning Blade view
                                                        Repository / Model
```

- **Controller** — translates HTTP only. Implements `HasMiddleware`, maps `permission:*` to
  actions, constructor-injects one Service, and returns what the Service produces. No queries,
  no business logic. (`app/Modules/Auth/Http/Controllers/Dashboard/User/UserController.php` is the
  reference.)
- **Form Request** — validates and normalizes input; carries authorization when it is field-shaped.
- **Service** — owns a complete workflow and its transaction, and renders the owning view or
  redirect. Lives in `Http/Services/Api/V1` or `Http/Services/Dashboard`.
- **Repository** — reusable persistence only. Interface in `Repositories/`, implementation in
  `Repositories/Eloquent/`, bound by name via `RepositoryServiceProvider`. One-off reads use the
  model directly.
- **API Resource** — the public response contract, in `Http/Resources/V1`.

### Canonical module paths (enforced by `FoundationArchitectureTest`)

```text
app/Modules/<Module>/
├── Http/Controllers/Api/V1        Http/Controllers/Dashboard
├── Http/Requests/Api/V1           Http/Requests/Dashboard
├── Http/Resources/V1              Http/Services/Api/V1 | Http/Services/Dashboard
├── Models  Providers  Repositories/Eloquent  Rules
├── Resources/views
├── Routes/api/v1/web.php          Routes/dashboard/dashboard.php
└── database/migrations | factories | seeders
```

Do not create compatibility copies in old locations. Move a surface, update every reference, then
run the reference sweep (`git grep` the class name **and** namespace).

## 4. How an agent works a task

**Before writing code**

1. Read this file and the relevant `specs/` entry. Create `specs/<n>-<feature>/{spec,plan,tasks}.md`
   when behavior or architecture changes.
2. Find the existing owner of the surface. Extend it; do not duplicate it.
3. Consult the matching rule in `.ai/rules/`.
4. Use Laravel Boost `search-docs` for any framework/package API, scoped to the installed version.
5. Prefer a Laravel-native solution that matches this project's version and conventions.

**After writing code**

1. Run the focused tests for the slice (validation, authorization, persistence, response/view).
2. Run the guards on the diff: `laravel-guard`, then `clean-code-guard`; `test-guard` if tests
   changed; `docs-guard` if public behavior or docs changed.
3. `vendor/bin/pint` (formats PHP; Blade is hand-formatted).
4. `vendor/bin/phpstan analyse` (Larastan).
5. Run the relevant suite, then the full handoff gate before handoff.
6. Report residual risks honestly.

Optional review passes (read-only, they propose — they do not edit):
`laravel-modernizer` for version-native refactors, `blade-dashboard-review` for dashboard screens.

## 5. Quality gates (handoff checklist)

`composer check` runs the local gate (Pint test, Larastan, tests, audit). The full handoff gate:

```bash
composer validate --strict
composer audit
npm audit
vendor/bin/pint --test          # PHP formatting (Blade is hand-formatted)
vendor/bin/phpstan analyse      # Larastan
php artisan test --compact      # disposable SQLite
php artisan route:list
php artisan view:cache
npm run build
git diff --check
```

Never call a gate successful when it did not run. If a database driver (`pdo_sqlite`) is missing
locally, say so and rely on CI, which provisions it.

## 6. AI tooling map

| Path | Purpose |
| --- | --- |
| `AGENTS.md` | This constitution. |
| `.ai/rules/*.md` | Path-scoped rules applied to the files an agent edits. |
| `.claude/skills/laravel-guard` | Reviews a Laravel diff against this base's architecture. |
| `.claude/skills/laravel-modernizer` | Proposes version-native Laravel refactors (read-only). |
| `.claude/skills/blade-dashboard-review` | Reviews dashboard Blade against the design system. |
| `.claude/skills/{clean-code-guard,test-guard,docs-guard}` | Installed general guard skills that review the diff (clean-code, test, docs). |
| Laravel Boost | Version-aware docs (`search-docs`) and guidelines. Refresh with `php artisan boost:update` after dependency changes. |

Skills change agent behavior, so treat their updates like code: review the diff in a PR, never
auto-merge.

## 7. Persistent project decisions

Recurring decisions live here as `- YYYY-MM-DD: <rule>; <reason>.` If a rule is mechanically
checkable, add or update an architecture test in the same change. Do not put one-off task notes
here — keep those in the matching spec or audit report.

- 2026-08-10: Target Laravel 13 / PHP 8.3+; framework `^13.0`, tinker `^3.0`; third-party packages
  verified compatible without downgrade. Reason: keep the base on the current supported release.
- 2026-08-10: Keep the Vuexy (Bootstrap 5 + Tabler) dashboard; do not add Livewire/Alpine/Tailwind
  to dashboard screens. Reason: one coherent, documented design system beats mixed UI stacks.
- 2026-08-10: Pin `intervention/image` at `^3.8` (not v4). Reason: v4 is a breaking rewrite with no
  requirement pulling it in.
- 2026-08-10: Adopt Laravel Boost, Larastan, guard skills, GitHub Actions CI, Dependabot, and
  dev-only Telescope as the standing engineering toolchain. Reason: make correctness and
  version-awareness automatic instead of a monthly manual prompt.
- 2026-08-11: Removed `laravel/sanctum` (API auth is JWT), `geniusts/hijri-dates`, `laravel/sail`, and
  ~58 demo-only npm libraries. Reason: keep only what the base actually uses.
- 2026-08-11: One API response shape via `App\Modules\Base\Http\Responses\ApiResponse` (`success`/`error`);
  the old global `responseSuccess/Fail` helpers and the `Responser` trait are gone. Reason: a single
  response contract that unwraps resources/paginators uniformly.
- 2026-08-11: Per-model API resources — `XResource` (full) + `XSummaryResource` (list/embed), reused
  across surfaces. Reason: consistent, DRY response bodies.
- 2026-08-11: All shared traits live in `App\Modules\Base\Concerns` — `HasImages` (declare a column and
  get storage/URL/replace, optional intervention), `HandlesResourceQuery` (declarative dashboard
  search/filter), `HasDeviceTokens`, `FileTrait`. Reason: one home, minimal model boilerplate.
- 2026-08-11: Every dashboard screen is built from the `x-dashboard.*` component set (table, filter-bar,
  actions, form-page, field, details). Reason: one consistent, low-boilerplate way to build screens.
- 2026-08-11: `Structure` is a declarative section registry (`app/Modules/Structure/Support/sections.php`)
  driving one generic editor, validation, and serializer — a new content section is a single definition.
  Reason: remove the per-section controller/request/view triplication.
- 2026-08-11: Push/broadcast go through `PushNotifier::send()` (FCM HTTP v1 + Laravel broadcasting),
  env-gated with no committed credentials. Reason: a ready-to-use notification path for new projects.
- 2026-08-12: `FoundationArchitectureTest` enforces the module *contract* (StudlyCase name,
  discoverable provider, canonical paths), not a whitelist of module names. Reason: this is a base to
  build on — new modules must be allowed if they conform, while a present-but-unloaded module still
  fails loudly.
- 2026-08-12: One installer, `php artisan base:install` (`--ci`, `--seed`); README, `composer setup`,
  and CI all call it. Reason: a single idempotent source of truth so documentation and automation
  cannot drift.
- 2026-08-12: `php artisan base:doctor --production` gates unsafe production settings (debug, wildcard
  CORS, leftover or weak admin credentials, sync queue, log/array mail, a non-HTTPS/localhost `APP_URL`)
  and warns on an insecure session cookie. Reason: make production hardening a checkable step, not
  tribal knowledge.
- 2026-08-12: Removed Tailwind from the foundation; the dashboard stays Bootstrap 5 + Vuexy. Reason:
  Tailwind contradicted the single-design-system rule (§1.4) and was unused.
- 2026-08-12: CI runs the suite on MySQL 8 alongside SQLite, pins Node via `.nvmrc`, upgrades the
  GitHub Actions to v7 (`checkout@v7`, `setup-node@v7`), and makes dependency audits blocking-with-retry.
  Reason: test the production database, end action-deprecation warnings, and keep `main` green without
  hiding real advisories.
- 2026-08-12: Release strategy — the base is a template consumed via a git `upstream` remote,
  SemVer-tagged, with `CHANGELOG.md` and `docs/UPGRADING.md`. Reason: one auditable source of security
  fixes instead of many detached forks.
- 2026-08-12: Test framework is **Pest 4** (running on PHPUnit 12; `phpunit/phpunit` bumped `^11.5.3`→
  `^12.3` to satisfy Pest 4), not plain PHPUnit test classes. Reason: project-owner decision. The suite
  was converted with `pest-plugin-drift` (used once, then removed); shared helpers live in
  `tests/Pest.php`; tests use `test()/it()` + `expect()` and opt into the database with
  `uses(RefreshDatabase::class)` per file. This **reverses** the earlier "PHPUnit 11 (not Pest)" rule —
  the Boost-generated PHPUnit guideline is superseded by `.ai/rules/testing.md`; do not convert Pest
  tests back to PHPUnit classes.
- 2026-08-12: De-productized the Auth module — removed the education-specific `account_type`
  (student/parent) plus its enum, `birth_date`, `gender`, and `marketing_opt_in` from the User model,
  migration, requests, resources, services, dashboard screens, and translations. Kept generic auth,
  the phone/OTP flow, `country`, and `whatsapp`. Reason: the base ships generic authentication, not one
  product's user schema. Structure and its CMS were kept by decision.
- 2026-08-12: Removed the education-legacy `teacher` role, the manager `title`/`bio`/`specialization` profile fields, and `isTeacher()`; renamed the education-flavored auth flow test. Reason: the base ships generic manager accounts, not one product's role/profile schema.

## 8. Module lifecycle

Add or remove a module as a complete vertical slice — see `docs/workflow.md` ("Adding a module",
"Removing a feature"). A removed module leaves no trace in runtime code, routes, schedules, menu
entries, permissions, seeders, translations, configuration, or tests.

## 9. Reuse vs. package boundary

Keep code in the application unless it is genuinely **reusable, application-independent, and has a
stable contract** — only then consider extracting it to a shared Composer package. Do not create a
package (or a service/DTO/repository layer) merely to wrap framework behavior.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.3. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record durable rules with `record-rule` so the next agent or teammate inherits them instead of working them out again. Pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Always use `record-rule`, never your native memory or notes tool — native memory is personal and session-scoped; only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
