---
name: laravel-modernizer
description: Propose version-native Laravel refactors for existing code (read-only). Use to find where code reinvents something the installed Laravel/package versions already provide. Reports ranked suggestions; does not modify code until you approve specific items.
---

# Laravel Modernizer

Finds where existing code is more verbose or custom than the Laravel-native equivalent for the
**installed** versions, and proposes replacements. It answers "is there a newer/better native API?" —
which is different from a correctness review.

> **Adapt before first use.** Replace every `<…>` placeholder with your project's shared abstractions
> and guardrail docs. See the bundle README.

## When to use

- "Modernize `<path>`" — audit a path for native replacements.
- Before a larger refactor, to surface high-value, low-risk simplifications.

**When not to use:** to justify a trendier idiom for its own sake, or to add a dependency — this skill
only reduces custom code in favor of what the installed versions already provide.

## Prerequisites

- Read access to the target path and to your guardrail doc (`<e.g. AGENTS.md>`).
- A way to confirm each native API exists in the installed versions (Laravel Boost `search-docs`, or
  the versioned docs matching `composer.json`).

## Method

1. Detect installed versions (framework + packages) — do not assume.
2. Confirm each candidate native API exists in those versions. Never suggest an absent API.
3. Read the code; identify obsolete/verbose/duplicated patterns with a native equivalent.
4. **Do not modify anything yet** — produce a ranked report.

## What to look for

- Manual validation a Form Request or a first-party rule already covers.
- Hand-rolled response shaping instead of API Resources; manual pagination instead of `paginate()`.
- A `$casts` property instead of the `casts()` method; date math instead of Carbon helpers.
- Custom loops instead of `Arr` / `Collection` / higher-order messages; bespoke `Str`/`Arr`/`Number` helpers.
- Manual authorization `if` chains instead of policies, gates, `@can`, or middleware.
- Manual container wiring where contextual binding / method injection suffices.
- Reinventing one of your project's own shared conventions (`<e.g. a response wrapper, an image trait,
  a query-filter helper, a content registry>`) instead of using it — inside a mature base, these *are*
  the native target.

Guardrail: prefer native only when it reduces complexity, improves correctness or readability, or
removes duplication. **Newer is not automatically better** — do not propose a change that only swaps a
working idiom for a trendier one, and never propose adding a framework the project deliberately avoids.

## Output format

    NN [HIGH|MEDIUM|LOW] VALUE — <path:line>
    Current:       <short snippet or description>
    Native (X.x):  <the native replacement + doc reference>
    Reason:        <complexity | correctness | readability | duplication>
    Risk:          <low | med | high>   Tests affected: <list or none>

Rank High→Low. Then wait: apply only the items the user names (e.g. "apply 1, 3, 5"), and run the gates
after applying.

### Sample entry

    01 [HIGH] VALUE — app/Services/UserProfileService.php:88
    Current:       manual foreach builds a response array, then response()->json(...)
    Native (11.x): a UserResource returned through the project's response layer
    Reason:        removes duplication — a response contract already exists
    Risk:          low     Tests affected: tests/Feature/ProfileTest.php
