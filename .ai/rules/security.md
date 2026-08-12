---
paths:
  - app/**
---

# Security

- **Output encoding:** escaped Blade `{{ }}` is the default boundary for database, request, flash, and
  editor content. `{!! !!}` only for application-generated, already-sanitized HTML. Pass values to JS
  with `@js`.
- **Mass assignment:** models accepting request input declare an explicit `$fillable`. Never pass an
  unfiltered `$request->all()` to `create`/`update`.
- **Authorization on every surface:** dashboard actions gate on Laratrust `permission:*` middleware;
  policies/gates guard record-level access; Blade uses `@can`. No unguarded destructive routes.
- **Query safety:** constrain user-controlled queries by column and bind parameters; never interpolate
  input into raw SQL. Scope records to the authenticated user where ownership applies.
- **Secrets & auth:** no committed credentials. OTPs and reset tokens are hashed, expiring,
  attempt-limited, and single-use; forgot-password returns the same response for known/unknown emails.
- **CSV/exports:** neutralize formula injection (prefix `= + - @` cells) — see `tests/Unit/Base/CsvCellTest.php`.
- **Config from env:** CORS allow-list and credentials come from environment, not hard-coded. Keep
  Laravel 13's `cache.serializable_classes` restrictive; only allow-list classes you deliberately cache.
- **CSRF:** keep `@csrf` in every state-changing form; do not disable request-forgery protection.
