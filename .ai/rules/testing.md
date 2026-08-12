---
paths:
  - tests/**
---

# Testing

Framework: **Pest 4** (running on PHPUnit 12 — not plain PHPUnit test classes). Disposable in-memory
SQLite is configured in `phpunit.xml`; CI also runs the suite on MySQL 8.

- Write tests with Pest: `test('...', fn () => ...)` or `it('...')` + `expect()`. Do **not** add
  `PHPUnit\Framework\TestCase` subclasses, and do **not** convert Pest tests back to PHPUnit classes —
  this reverses the older, still-present Boost PHPUnit guideline. `$this` inside a test closure is the
  `Tests\TestCase`, so PHPUnit assertions (`$this->assertContains(...)`) remain available when an
  `expect()` equivalent is awkward (e.g. an assertion that needs a custom failure message).
- The base `Tests\TestCase` is bound to the `Feature` and `Unit` suites in `tests/Pest.php`. Opt a file
  into a fresh schema with `uses(RefreshDatabase::class);` at the top — it is intentionally NOT global,
  so pure unit tests do not pay for migrations. Shared helpers (e.g. `managerWithPermission()`) live in
  `tests/Pest.php`, not duplicated per file.
- Every vertical slice ships tests covering validation, authorization, persistence side effects, and
  the response/view. Add a regression test with each bug fix.
- Public API behavior is protected by contract/feature tests (see `tests/Unit/Auth/*Contracts*` and
  `tests/Feature/**`). Do not change a public API contract without updating its test.
- Architecture invariants live in `tests/Unit/Architecture/FoundationArchitectureTest.php`. If you add
  a mechanically checkable rule, extend that test in the same change.
- Use factories for model state; never seed real credentials. Tests must not depend on developer data
  or hit external services — fake mail, queue, HTTP, and notifications.
- Name tests by behavior (`test('manager without permission cannot export', ...)`). Prefer feature
  tests through the HTTP layer for controllers; unit-test pure logic directly.
- Laravel 13: `Str` factories and custom UUID/ULID generators reset between tests — set them in each
  test's `beforeEach()` if needed.
- Locally there is no `pdo_sqlite`: run the suite against MySQL with env overrides
  (`DB_CONNECTION=mysql DB_DATABASE=basehafez_test … php artisan test`). Never point tests at the dev
  database — `RefreshDatabase` wipes it. Do not report an unrun suite as green.
