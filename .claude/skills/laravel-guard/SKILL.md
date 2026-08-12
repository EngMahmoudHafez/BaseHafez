---
name: laravel-guard
description: Review a Laravel diff against this base's architecture before commit or merge. Use after implementing or changing PHP/Blade code in app/Modules — checks controllers, Eloquent, requests, security, Blade, performance, and tests against the project's conventions. Read-only: it reports findings, it does not rewrite code.
---

# Laravel Guard

Second-pass reviewer for this modular Laravel 13 base. Run it on a diff after the code is written
and the focused tests pass, before `clean-code-guard` and the formatting/static gates.

## When to use

- After implementing a feature slice or changing anything under `app/Modules/**`.
- Before `git commit` / opening a PR, as the Laravel-specific review pass.

**When not to use:** a diff that touches no `app/Modules/**` PHP or Blade (pure docs, config, or asset
changes) — run the formatting and static-analysis gates instead.

## Prerequisites

- The change is written and its focused tests pass; you have the `git diff` to review.
- Laravel Boost `search-docs` is available to version-check any API you propose.
- `AGENTS.md` and the `.ai/rules/*` files matching the diff's paths are readable.

## Operating rule (do this first)

Before recommending any Laravel-specific change, **confirm the API against the installed version**
using Laravel Boost `search-docs` (framework 13.x + the installed package versions). Never propose an
API that does not exist in this project's versions. Read `AGENTS.md` and the matching `.ai/rules/*`
for the files in the diff.

## Review checklist

Scope the review to the diff. For each finding give: file:line, the problem, the project-aligned fix,
risk (low/med/high), and tests affected.

**Architecture** (`docs/architecture.md`, `.ai/rules/architecture.md`)
- Fat controller? Business logic or queries that belong in a Service/Repository.
- Surface off its canonical path (`Http/Controllers/{Api/V1,Dashboard}`, `Http/Services/...`, etc.).
- New module beyond Auth/Base/Notifications/Structure, or `Base` owning a business screen.
- Unnecessary service/DTO/repository wrapping a single Eloquent call. Custom code Laravel provides.
- Duplicated logic instead of extending the existing owner.

**Base shared conventions** (`docs/architecture.md` → "Shared conventions"; all shared traits live in `App\Modules\Base\Concerns`)
- API response not built through `App\Modules\Base\Http\Responses\ApiResponse` (`success`/`error`) — an ad-hoc JSON envelope, or a reintroduced global response helper / `Responser` trait (both were removed; one source only).
- Endpoint returns a raw model/array instead of `XResource` (full record) / `XSummaryResource` (list + embedding); a screen showing a user/manager not reusing `UserSummaryResource` / `ManagerSummaryResource`.
- Model hand-rolls image storage or an image accessor instead of `use HasImages` (gives `putImage()`, `image_url`, `deleteImage()`; override `imageColumn()` only when the column differs from `avatar`).
- Dashboard list service hand-writes `when()` filter chains instead of declaring `searchable`/`filterable` and calling `HandlesResourceQuery::applyDashboardFilters()`.
- New editable site content added as a per-section controller/request/view instead of a single entry in `Structure/Support/sections.php`.
- Push/broadcast wired by hand instead of `Notifications\Services\PushNotifier::send()`; device tokens stored outside `HasDeviceTokens`.
- API authentication via a reintroduced Sanctum (removed) — this base authenticates the API with JWT (`tymon/jwt-auth`).

**Eloquent & queries**
- N+1 / missing eager loading; query inside a loop.
- Missing pagination on a list; unbounded collection loaded into memory.
- Wrong/duplicated relationship, missing `casts()`, missing scope reuse.
- Multi-write workflow not wrapped in a transaction.

**Requests & authorization**
- Validation in the controller instead of a Form Request; `authorize()` returning `false`.
- Missing `permission:*` middleware mapping; role checked inline instead of policy/`@can`.
- Duplicated validation rules that belong in a shared `Rules/` class.
- Update `unique` not ignoring the current id.

**Security** (`.ai/rules/security.md`)
- Mass assignment via `$request->all()` / empty `$guarded`.
- Unescaped `{!! !!}` on user/db content; server value built into a `<script>` without `@js`.
- User-controlled raw SQL; record not scoped to its owner.
- Committed secret/fixed password; OTP/token not hashed/expiring/single-use.
- CSV export without formula-injection neutralization; `@csrf` missing on a state-changing form.

**Blade / dashboard** (`docs/dashboard-design-system.md`)
- Duplicated markup that should be an `x-dashboard.*` / form / table component.
- Query or business logic in a view; missing `<x-dashboard.page-header>`.
- A second UI/icon/CSS framework introduced (Livewire/Alpine/Tailwind-in-Blade/Vue/React).
- Missing empty/loading/error state; unescaped output; missing `@can`.

**Performance** (`.ai/rules/performance.md`)
- Heavy work (mail, export, external call) run inline instead of queued.
- Counting rows in PHP instead of the database; cacheable read recomputed each request.

**Testing** (`.ai/rules/testing.md`)
- Slice missing coverage for validation, authorization, persistence, or response/view.
- Bug fix without a regression test; changed public API contract without an updated contract test.
- New mechanically checkable invariant without a matching architecture-test assertion.

## Output

Group findings by severity (High → Low). For each: `path:line` · problem · fix · risk · tests.
End with `Ready to commit` only if there are no High/Medium findings and the gates
(`vendor/bin/pint --test`, `vendor/bin/phpstan analyse`, focused tests) pass.

### Sample finding

> **High** · `app/Modules/Auth/Http/Controllers/Api/V1/UserController.php:42` · returns
> `$user->toArray()` directly, bypassing the response contract. · Wrap it in
> `ApiResponse::success(data: new UserResource($user))` (add `UserResource` if missing). · risk: low ·
> tests: `tests/Feature/Auth/UserApiTest.php` asserts the `{status, message, data}` envelope.

Close with a one-line verdict — `Ready to commit`, or `Blocked: N High / M Medium`.
