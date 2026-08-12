---
name: laravel-guard
description: Review a Laravel diff against your project's architecture and conventions before commit or merge. Use after implementing or changing PHP/Blade code — checks controllers, Eloquent, requests, security, Blade, performance, and tests. Read-only: it reports findings, it does not rewrite code.
---

# Laravel Guard

A second-pass, version-aware reviewer for a Laravel codebase. Run it on a diff after the code is
written and its focused tests pass, and before the formatting / static-analysis gates.

> **Adapt before first use.** Replace every `<…>` placeholder with your project's real values (paths,
> convention docs, shared abstractions, gate commands). The checklist is universal Laravel; the
> placeholders tie it to your conventions. See the bundle README.

## When to use

- After implementing a feature slice or changing anything under `<app source path, e.g. app/ or app/Modules/**>`.
- Before `git commit` / opening a PR, as the Laravel-specific review pass.

**When not to use:** a diff with no PHP or Blade changes (pure docs, config, or assets) — run the
formatting and static-analysis gates instead.

## Prerequisites

- The change is written and its focused tests pass; you have the `git diff` to review.
- A way to confirm an API exists in the *installed* versions (Laravel Boost `search-docs`, or the
  versioned docs matching `composer.json`).
- Your convention docs — `<e.g. AGENTS.md, .ai/rules/*, CONTRIBUTING.md>` — are readable.

## Operating rule (do this first)

Confirm any Laravel-specific API against the **installed** version before recommending it. Never
propose an API that does not exist in the project's versions. Read the convention docs for the files in
the diff.

## Review checklist

Scope to the diff. For each finding give: `file:line`, the problem, the project-aligned fix, risk
(low/med/high), and tests affected.

**Architecture**
- Fat controller — business logic or queries that belong in a Service/Action/Repository.
- File off its canonical path for your structure (`<controllers / services / requests / … layout>`).
- Duplicated logic instead of extending the existing owner; custom code the framework provides.
- Speculative abstraction (service/DTO/repository) wrapping a single Eloquent call.

**Project conventions** — adapt these to your base
- Response shape not going through your shared response layer (`<e.g. an ApiResponse wrapper / a Resource>`);
  ad-hoc JSON envelopes.
- Endpoint returning a raw model/array instead of an API Resource; shared "summary" data not reused.
- A cross-cutting concern (file/image handling, list filtering, tokens) reinvented instead of the
  shared trait/helper (`<name them>`), or a shared trait placed outside its canonical namespace.

**Eloquent & queries**
- N+1 / missing eager loading; query inside a loop.
- Missing pagination on a list; unbounded collection loaded into memory.
- Wrong/duplicated relationship; missing `casts()`; multi-write workflow not wrapped in a transaction.

**Requests & authorization**
- Validation in the controller instead of a Form Request; `authorize()` stubbed to `false`.
- Missing authorization (policy / gate / `@can` / middleware); role checked inline instead of a policy.
- Duplicated rules that belong in a shared rule object; update `unique` not ignoring the current id.

**Security**
- Mass assignment via `$request->all()` / empty `$guarded`.
- Unescaped `{!! !!}` on user/DB content; a server value injected into `<script>` without `@js`.
- User-controlled raw SQL; a record not scoped to its owner.
- Committed secret / fixed password; token or OTP not hashed, expiring, and single-use.
- `@csrf` missing on a state-changing form; CSV export without formula-injection neutralization.

**Blade / UI**
- Duplicated markup that should be a shared component; query or business logic in a view.
- A second UI/CSS/JS framework introduced against the project's chosen stack.
- Missing empty/loading/error state; unescaped output; missing authorization directive.

**Performance**
- Heavy work (mail, export, external call) run inline instead of queued.
- Counting rows in PHP instead of the database; a cacheable read recomputed every request.

**Testing**
- Missing coverage for validation, authorization, persistence, or response/view.
- Bug fix without a regression test; a changed public contract without an updated contract test.

## Output

Group findings by severity (High → Low). For each: `path:line` · problem · fix · risk · tests.

### Sample finding

> **High** · `app/Http/Controllers/UserController.php:42` · returns `$user->toArray()` directly,
> bypassing the response contract. · Wrap it in your response layer + an API Resource. · risk: low ·
> tests: assert the response envelope.

Close with a one-line verdict — `Ready to commit`, or `Blocked: N High / M Medium` — gated on your
formatting, static-analysis, and focused tests passing (`<your gate commands>`).
